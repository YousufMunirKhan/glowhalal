<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IngredientOrigin: string implements HasColor, HasLabel
{
    case Plant = 'plant';
    case Mineral = 'mineral';
    case Synthetic = 'synthetic';
    case Marine = 'marine';
    case Microbial = 'microbial';
    case Animal = 'animal';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Plant, self::Microbial => 'success',
            self::Mineral, self::Synthetic => 'info',
            self::Marine => 'primary',
            self::Animal => 'warning',
            self::Unknown => 'gray',
        };
    }
}
