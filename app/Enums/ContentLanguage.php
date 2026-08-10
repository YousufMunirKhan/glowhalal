<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContentLanguage: string implements HasLabel
{
    case RomanUrdu = 'roman_urdu';
    case Urdu = 'urdu';
    case English = 'english';
    case Mixed = 'mixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::RomanUrdu => 'Roman Urdu',
            self::Urdu => 'Urdu',
            self::English => 'English',
            self::Mixed => 'Mixed',
        };
    }
}
