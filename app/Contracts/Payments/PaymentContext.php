<?php

namespace App\Contracts\Payments;

use App\Models\User;
use App\Support\Money;

/**
 * Architecture §6.3. Everything a driver needs to decide whether it may be
 * offered for this basket, and what it would surcharge — without the driver
 * ever seeing a Cart or an Order.
 */
final readonly class PaymentContext
{
    public function __construct(
        public Money $subtotal,
        public Money $grandTotal,
        public ?string $city = null,
        public ?string $province = null,
        public ?User $user = null,
        public int $itemsCount = 0,
    ) {}
}
