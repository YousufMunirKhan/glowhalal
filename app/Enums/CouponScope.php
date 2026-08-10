<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponScope: string implements HasLabel
{
    case All = 'all';
    case Products = 'products';
    case Categories = 'categories';

    public function getLabel(): string
    {
        return match ($this) {
            self::All => 'Entire order',
            self::Products => 'Selected products',
            self::Categories => 'Selected categories',
        };
    }
}
