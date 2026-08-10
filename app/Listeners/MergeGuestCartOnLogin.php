<?php

namespace App\Listeners;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Services\Cart\CartCalculator;
use App\Services\Cart\CartManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

/**
 * Architecture §5.4.
 *
 * The interesting case is a guest with a cart who logs in and already has a
 * saved cart. Neither may be discarded silently.
 *
 * On a quantity collision we take max(), not the sum. Summing surprises people:
 * adding one lipstick as a guest, having previously saved one, should not
 * silently produce two. max() never over-charges and never inflates a basket.
 *
 * The guest cart is marked `merged` and retained rather than deleted — merge
 * bugs are otherwise unrecoverable.
 */
final class MergeGuestCartOnLogin
{
    public function __construct(
        private readonly CartManager $carts,
        private readonly CartCalculator $calculator,
    ) {}

    public function handle(Login $event): void
    {
        $token = request()->cookie(CartManager::COOKIE);

        if (! $token) {
            return;
        }

        $guestCart = Cart::active()
            ->where('token', $token)
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::active()
            ->where('user_id', $event->user->getAuthIdentifier())
            ->whereKeyNot($guestCart->getKey())
            ->latest('id')
            ->first();

        if (! $userCart) {
            $guestCart->update(['user_id' => $event->user->getAuthIdentifier()]);

            return;
        }

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $item) {
                $existing = $userCart->items()
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($existing) {
                    $quantity = max($existing->quantity, $item->quantity);

                    $existing->forceFill([
                        'quantity' => $quantity,
                        'line_total_amount' => $existing->unit_price_amount->times($quantity),
                    ])->save();

                    continue;
                }

                $userCart->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price_amount' => $item->unit_price_amount,
                    'line_total_amount' => $item->line_total_amount,
                    'name_snapshot' => $item->name_snapshot,
                    'options_snapshot' => $item->options_snapshot,
                    'image_path_snapshot' => $item->image_path_snapshot,
                ]);
            }

            $guestCart->update([
                'status' => CartStatus::Merged,
                'merged_into_cart_id' => $userCart->id,
            ]);

            $this->calculator->recalculate($userCart->fresh('items'));
        });

        $this->carts->remember($userCart);
    }
}
