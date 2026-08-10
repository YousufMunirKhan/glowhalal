<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\ReservationStatus;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\StockReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Releases stock held by orders that were placed but never confirmed.
 *
 * PlaceOrderAction reserves stock at placement — `quantity_reserved` goes up,
 * which drops the generated `quantity_available` so the next shopper cannot
 * oversell — and stamps the reservation `expires_at = now()->addDays(7)`. On a
 * COD store a shopper who never answers the confirmation SMS leaves that
 * reservation Held forever, so `quantity_available` drifts down and real stock
 * looks sold out. Nothing consumed the expiry until now.
 *
 * This command finds every Held reservation whose window has passed and whose
 * order is still Pending, hands the units back to availability, and marks the
 * reservation Expired. It is idempotent and safe to run repeatedly.
 */
class ReleaseExpiredReservations extends Command
{
    protected $signature = 'inventory:release-expired';

    protected $description = 'Release stock held by expired, still-Pending order reservations.';

    public function handle(): int
    {
        // `stale()` = status Held AND expires_at set AND expires_at <= now.
        // Confirmed/Shipped orders already committed their reservations (status
        // Committed), so only genuinely abandoned Pending orders match here.
        $query = StockReservation::query()
            ->stale()
            ->whereHasMorph(
                'reservable',
                [Order::class],
                fn ($q) => $q->where('status', OrderStatus::Pending),
            );

        $released = 0;
        $skipped = 0;

        // chunkById is safe while we mutate rows out of the filtered set: it
        // pages by ascending id rather than by offset.
        $query->chunkById(200, function ($reservations) use (&$released, &$skipped) {
            foreach ($reservations as $reservation) {
                $this->releaseOne($reservation->id) ? $released++ : $skipped++;
            }
        });

        $this->info("Released {$released} expired reservation(s)".($skipped ? "; skipped {$skipped} (already moved)." : '.'));

        return self::SUCCESS;
    }

    /**
     * Release a single reservation under a row lock. Returns true when this run
     * expired it, false when a concurrent confirm/cancel had already moved it.
     */
    private function releaseOne(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::whereKey($reservationId)->lockForUpdate()->first();

            // Re-check under the lock: an admin confirm/cancel may have advanced
            // this reservation since we listed it. Only a still-Held row is ours.
            if (! $reservation || $reservation->status !== ReservationStatus::Held) {
                return false;
            }

            $inventory = InventoryItem::whereKey($reservation->inventory_item_id)->lockForUpdate()->first();

            if ($inventory) {
                // Give the units back to availability. We touch ONLY
                // quantity_reserved: quantity_available is a generated column
                // that recomputes itself (the model refuses to write it), and
                // quantity_on_hand never moved — the goods never left the shelf
                // for a still-Pending order. max(0, ...) guards against a double
                // release ever driving the count negative.
                $inventory->forceFill([
                    'quantity_reserved' => max(0, $inventory->quantity_reserved - $reservation->quantity),
                ])->save();
            }

            $reservation->forceFill(['status' => ReservationStatus::Expired])->save();

            return true;
        });
    }
}
