<?php

namespace App\Observers;

use App\Models\Cart;
use App\Models\CartItem;

class CartItemObserver
{
    public function saved(CartItem $item): void
    {
        $this->recalculate($item->cart_id);
    }

    public function deleted(CartItem $item): void
    {
        $this->recalculate($item->cart_id);
    }

    private function recalculate(?int $cartId): void
    {
        if (! $cartId || ! ($cart = Cart::find($cartId))) {
            return;
        }

        $aggregate = CartItem::where('cart_id', $cartId)
            ->selectRaw('COALESCE(SUM(line_total_amount), 0) AS subtotal, COALESCE(SUM(quantity), 0) AS items')
            ->first();

        $subtotal = (int) ($aggregate?->subtotal ?? 0);

        $cart->forceFill([
            'subtotal_amount' => $subtotal,
            'items_count' => (int) ($aggregate?->items ?? 0),
            'grand_total_amount' => max(0, $subtotal
                - (int) ($cart->getRawOriginal('discount_amount') ?? 0)
                + (int) ($cart->getRawOriginal('shipping_amount') ?? 0)
                + (int) ($cart->getRawOriginal('tax_amount') ?? 0)),
            'last_activity_at' => now(),
        ])->saveQuietly();
    }
}
