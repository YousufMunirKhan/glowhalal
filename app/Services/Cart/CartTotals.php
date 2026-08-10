<?php

namespace App\Services\Cart;

use App\Services\Shipping\ShippingQuote;
use App\Support\Money;

/** Architecture §5.6. The derived view of a cart, computed in one place. */
final readonly class CartTotals
{
    public function __construct(
        public Money $subtotal,
        public Money $discount,
        public Money $shipping,
        public Money $codFee,
        public Money $tax,
        public Money $grandTotal,
        public int $itemsCount = 0,
        public ?ShippingQuote $shippingQuote = null,
    ) {}

    public static function empty(): self
    {
        return new self(
            Money::zero(), Money::zero(), Money::zero(),
            Money::zero(), Money::zero(), Money::zero(),
        );
    }
}
