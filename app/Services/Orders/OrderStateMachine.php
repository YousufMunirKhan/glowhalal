<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Enums\StockMovementReason;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

/**
 * The only sanctioned way an order changes status.
 *
 * `OrderStatus::canTransitionTo()` is the single definition of what is legal
 * (§3.3). The Filament actions use it to decide what to *offer*; this service
 * uses it again to decide what to *accept*, so a forged request is still rejected.
 */
final class OrderStateMachine
{
    public function transition(Order $order, OrderStatus $target, array $data = []): Order
    {
        $from = $order->status;

        if (! $from->canTransitionTo($target)) {
            throw new IllegalOrderTransitionException(
                "Cannot move order {$order->order_number} from {$from->value} to {$target->value}."
            );
        }

        return DB::transaction(function () use ($order, $from, $target, $data) {
            match ($target) {
                OrderStatus::Confirmed => $this->onConfirm($order),
                OrderStatus::Shipped => $this->onShip($order, $data),
                OrderStatus::Delivered => $this->onDeliver($order),
                OrderStatus::Cancelled => $this->onCancel($order, $from, $data),
                OrderStatus::Refunded => $this->onRefund($order, $data),
                default => null,
            };

            $order->status = $target;

            if (($data['note'] ?? null) !== null) {
                $order->admin_note = $data['note'];
            }

            // OrderObserver stamps the *_at column and writes the history row.
            $order->save();

            if ($data['notify_customer'] ?? false) {
                $order->statusHistories()->latest('id')->limit(1)->update(['customer_notified' => true]);
            }

            return $order->refresh();
        });
    }

    /**
     * Commit the held reservations for every line.
     *
     * The storefront reserves at order placement (§5.7) — `quantity_reserved`
     * goes up, which drops the generated `quantity_available` and stops the
     * next shopper overselling the last unit, while `quantity_on_hand` stays
     * put. Confirmation is the moment the goods actually leave: on-hand comes
     * down, the reservation is released against it, and the Sale movement is
     * written. That keeps the ledger a true record of physical stock rather
     * than of intent.
     */
    private function onConfirm(Order $order): void
    {
        foreach ($order->reservations()->where('status', ReservationStatus::Held)->get() as $reservation) {
            $inventory = InventoryItem::whereKey($reservation->inventory_item_id)->lockForUpdate()->first();

            if ($inventory) {
                $inventory->forceFill([
                    'quantity_on_hand' => $inventory->quantity_on_hand - $reservation->quantity,
                    'quantity_reserved' => max(0, $inventory->quantity_reserved - $reservation->quantity),
                ])->save();

                $inventory->movements()->create([
                    'quantity_delta' => -$reservation->quantity,
                    'quantity_after' => $inventory->quantity_on_hand,
                    'reason' => StockMovementReason::Sale,
                    'reference_type' => $order->getMorphClass(),
                    'reference_id' => $order->getKey(),
                    'user_id' => auth()->id(),
                    'note' => "Committed on confirmation of {$order->order_number}",
                ]);
            }

            $reservation->forceFill(['status' => ReservationStatus::Committed])->save();
        }
    }

    private function onShip(Order $order, array $data): void
    {
        $order->tracking_number = $data['tracking_number'] ?? $order->tracking_number;
        $order->courier = $data['courier'] ?? $order->courier;
    }

    private function onDeliver(Order $order): void
    {
        // For COD, delivery is the moment payment is collected.
        if ($order->payment_method === 'cod' && $order->payment_status !== PaymentStatus::Paid) {
            $order->payment_status = PaymentStatus::Paid;
            $order->paid_amount = $order->grand_total_amount;
        }
    }

    private function onCancel(Order $order, OrderStatus $from, array $data): void
    {
        $order->cancel_reason = $data['cancel_reason'] ?? null;
        $restock = (bool) ($data['restock'] ?? true);

        foreach ($order->reservations()
            ->whereIn('status', [ReservationStatus::Held, ReservationStatus::Committed])
            ->get() as $reservation) {

            $inventory = InventoryItem::whereKey($reservation->inventory_item_id)->lockForUpdate()->first();

            if ($inventory) {
                if ($reservation->status === ReservationStatus::Held) {
                    // Never left the shelf — just stop holding it.
                    $inventory->forceFill([
                        'quantity_reserved' => max(0, $inventory->quantity_reserved - $reservation->quantity),
                    ])->save();
                } elseif ($restock) {
                    $inventory->forceFill([
                        'quantity_on_hand' => $inventory->quantity_on_hand + $reservation->quantity,
                    ])->save();

                    $inventory->movements()->create([
                        'quantity_delta' => $reservation->quantity,
                        'quantity_after' => $inventory->quantity_on_hand,
                        'reason' => StockMovementReason::Return,
                        'reference_type' => $order->getMorphClass(),
                        'reference_id' => $order->getKey(),
                        'user_id' => auth()->id(),
                        'note' => 'Restocked on cancellation',
                    ]);
                }
            }

            $reservation->forceFill(['status' => ReservationStatus::Released])->save();
        }

        // Orders placed before reservations existed have no rows to walk back;
        // fall back to the line-item restock for those.
        if ($restock && $from->hasConsumedStock() && $order->reservations()->count() === 0) {
            $this->restock($order);
        }
    }

    private function onRefund(Order $order, array $data): void
    {
        $amount = isset($data['amount'])
            ? Money::fromRupees($data['amount'])
            : $order->grand_total_amount;

        $order->refunded_amount = $amount;
        $order->payment_status = $amount->minorUnits >= ($order->grand_total_amount?->minorUnits ?? 0)
            ? PaymentStatus::Refunded
            : PaymentStatus::PartiallyRefunded;
    }

    private function restock(Order $order): void
    {
        $order->items()->with('variant')->get()->each(function (OrderItem $item) use ($order) {
            $inventory = InventoryItem::where('product_variant_id', $item->product_variant_id)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                return;
            }

            $inventory->quantity_on_hand += $item->quantity;
            $inventory->save();

            $inventory->movements()->create([
                'quantity_delta' => $item->quantity,
                'quantity_after' => $inventory->quantity_on_hand,
                'reason' => StockMovementReason::Return,
                'reference_type' => $order->getMorphClass(),
                'reference_id' => $order->getKey(),
                'user_id' => auth()->id(),
                'note' => 'Restocked on cancellation',
            ]);
        });
    }
}
