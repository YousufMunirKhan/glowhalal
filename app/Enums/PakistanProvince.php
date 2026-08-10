<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Backed values are constrained at the database level by `addresses_province_check`.
 * Do not change a case value without altering that CHECK constraint first.
 */
enum PakistanProvince: string implements HasLabel
{
    case Punjab = 'punjab';
    case Sindh = 'sindh';
    case Kpk = 'kpk';
    case Balochistan = 'balochistan';
    case GilgitBaltistan = 'gilgit_baltistan';
    case Ajk = 'ajk';
    case Islamabad = 'islamabad';

    public function getLabel(): string
    {
        return match ($this) {
            self::Punjab => 'Punjab',
            self::Sindh => 'Sindh',
            self::Kpk => 'Khyber Pakhtunkhwa',
            self::Balochistan => 'Balochistan',
            self::GilgitBaltistan => 'Gilgit-Baltistan',
            self::Ajk => 'Azad Jammu & Kashmir',
            self::Islamabad => 'Islamabad Capital Territory',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
