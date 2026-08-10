<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReservationStatus: string implements HasColor, HasLabel
{
    case Held = 'held';
    case Committed = 'committed';
    case Released = 'released';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Held => 'warning',
            self::Committed => 'success',
            self::Released, self::Expired => 'gray',
        };
    }
}
