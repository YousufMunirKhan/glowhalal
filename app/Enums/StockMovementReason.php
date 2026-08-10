<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementReason: string implements HasColor, HasLabel
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Damage = 'damage';
    case Recount = 'recount';
    case ReservationRelease = 'reservation_release';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase / restock',
            self::Sale => 'Sale',
            self::Return => 'Customer return',
            self::Adjustment => 'Manual adjustment',
            self::Damage => 'Damage / write-off',
            self::Recount => 'Stock recount',
            self::ReservationRelease => 'Reservation released',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Purchase, self::Return, self::ReservationRelease => 'success',
            self::Sale => 'info',
            self::Adjustment, self::Recount => 'warning',
            self::Damage => 'danger',
        };
    }
}
