<?php

namespace App\Services\Orders;

use App\Models\CartItem;
use App\Support\Money;

/**
 * Architecture §5.7. Problems are collected, not thrown on the first failure —
 * a customer with three unavailable items should be told about all three at
 * once, not made to retry three times.
 */
final readonly class CheckoutProblem
{
    private function __construct(
        public string $type,
        public int $cartItemId,
        public string $message,
        public ?int $availableQuantity = null,
    ) {}

    public static function unavailable(CartItem $item): self
    {
        return new self('unavailable', $item->id,
            "{$item->name_snapshot} is no longer available and has been removed from your bag.", 0);
    }

    public static function insufficientStock(CartItem $item, int $available): self
    {
        return $available === 0
            ? new self('out_of_stock', $item->id,
                "{$item->name_snapshot} has just sold out and has been removed from your bag.", 0)
            : new self('insufficient_stock', $item->id,
                "{$item->name_snapshot} — only {$available} left. Your quantity has been reduced.", $available);
    }

    public static function priceChanged(CartItem $item, Money $newPrice): self
    {
        return new self('price_changed', $item->id,
            "{$item->name_snapshot} is now {$newPrice->format()}. Please review your bag before ordering.");
    }
}
