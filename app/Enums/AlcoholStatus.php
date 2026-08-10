<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AlcoholStatus: string implements HasColor, HasLabel
{
    case None = 'none';
    case FattyAlcoholOnly = 'fatty_alcohol_only';
    case Denatured = 'denatured';
    case Ethanol = 'ethanol';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return match ($this) {
            self::None => 'No alcohol',
            self::FattyAlcoholOnly => 'Fatty alcohol only (non-intoxicating)',
            self::Denatured => 'Denatured alcohol',
            self::Ethanol => 'Ethanol',
            self::Unknown => 'Unknown',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::None => 'success',
            self::FattyAlcoholOnly => 'info',
            self::Denatured, self::Ethanol => 'danger',
            self::Unknown => 'gray',
        };
    }

    /** Fatty alcohols are emollients, not intoxicants — they never fail an alcohol-free claim. */
    public function isIntoxicating(): bool
    {
        return in_array($this, [self::Denatured, self::Ethanol], strict: true);
    }
}
