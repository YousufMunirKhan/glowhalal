<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialMediaType: string implements HasLabel
{
    case Image = 'image';
    case Video = 'video';

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
        };
    }
}
