<?php

namespace App\Services\Shipping;

use App\Models\ShippingRate;
use App\Support\Money;

final readonly class ShippingQuote
{
    public function __construct(
        public Money $amount,
        public Money $codSurcharge,
        public string $label,
        public ?ShippingRate $rate = null,
        public ?int $minDays = null,
        public ?int $maxDays = null,
        public bool $isEstimate = false,
    ) {}

    public function deliveryWindow(): ?string
    {
        if ($this->minDays === null && $this->maxDays === null) {
            return null;
        }

        if ($this->minDays !== null && $this->maxDays !== null && $this->minDays !== $this->maxDays) {
            return "{$this->minDays}–{$this->maxDays} working days";
        }

        $days = $this->maxDays ?? $this->minDays;

        return "{$days} working days";
    }

    public function isFree(): bool
    {
        return $this->amount->isZero();
    }
}
