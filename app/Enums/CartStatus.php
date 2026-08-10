<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CartStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Converted = 'converted';
    case Abandoned = 'abandoned';
    case Merged = 'merged';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'info',
            self::Converted => 'success',
            self::Abandoned => 'warning',
            self::Merged => 'gray',
        };
    }
}
