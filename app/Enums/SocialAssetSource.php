<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialAssetSource: string implements HasLabel
{
    case Whatsapp = 'whatsapp';
    case Instagram = 'instagram';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Whatsapp => 'WhatsApp',
            self::Instagram => 'Instagram',
            self::Other => 'Other',
        };
    }
}
