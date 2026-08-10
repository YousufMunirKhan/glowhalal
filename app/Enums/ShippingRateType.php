<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ShippingRateType: string implements HasLabel
{
    case Flat = 'flat';
    case FreeOver = 'free_over';
    case WeightBased = 'weight_based';

    public function getLabel(): string
    {
        return match ($this) {
            self::Flat => 'Flat rate',
            self::FreeOver => 'Free over subtotal',
            self::WeightBased => 'Weight based',
        };
    }
}
