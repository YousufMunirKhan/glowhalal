<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialAssetType: string implements HasLabel
{
    case Video = 'video';
    case Photo = 'photo';
    case Text = 'text';

    public function getLabel(): string
    {
        return match ($this) {
            self::Video => 'Video',
            self::Photo => 'Photo',
            self::Text => 'Text / quote',
        };
    }
}
