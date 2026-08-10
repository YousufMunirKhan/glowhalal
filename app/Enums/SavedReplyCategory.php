<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SavedReplyCategory: string implements HasColor, HasLabel
{
    case Order = 'order';
    case Delivery = 'delivery';
    case Price = 'price';
    case Safety = 'safety';
    case General = 'general';

    public function getLabel(): string
    {
        return match ($this) {
            self::Order => 'Order',
            self::Delivery => 'Delivery',
            self::Price => 'Price',
            self::Safety => 'Safety',
            self::General => 'General',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Order => 'info',
            self::Delivery => 'warning',
            self::Price => 'success',
            self::Safety => 'danger',
            self::General => 'gray',
        };
    }
}
