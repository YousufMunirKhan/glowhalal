<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasLabel
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
    case FreeShipping = 'free_shipping';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage off',
            self::FixedAmount => 'Fixed amount off',
            self::FreeShipping => 'Free shipping',
        };
    }
}
