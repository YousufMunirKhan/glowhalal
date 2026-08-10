<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a post is manually published. Phase 0 has NO API integration — the
 * "app URL" below is only the public web home of each platform, used by the
 * "Open app" helper so the owner can jump over and paste manually.
 */
enum SocialPlatform: string implements HasColor, HasLabel
{
    case Instagram = 'instagram';
    case Facebook = 'facebook';
    case TikTok = 'tiktok';
    case X = 'x';
    case WhatsappStatus = 'whatsapp_status';

    public function getLabel(): string
    {
        return match ($this) {
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
            self::TikTok => 'TikTok',
            self::X => 'X (Twitter)',
            self::WhatsappStatus => 'WhatsApp Status',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Instagram => 'danger',
            self::Facebook => 'info',
            self::TikTok => 'gray',
            self::X => 'gray',
            self::WhatsappStatus => 'success',
        };
    }

    /**
     * Public web home of the platform — the "Open app" helper opens this in a
     * new tab so the owner can post by hand. No deep-linking, no API.
     */
    public function appUrl(): string
    {
        return match ($this) {
            self::Instagram => 'https://www.instagram.com/',
            self::Facebook => 'https://www.facebook.com/',
            self::TikTok => 'https://www.tiktok.com/upload',
            self::X => 'https://x.com/compose/post',
            self::WhatsappStatus => 'https://web.whatsapp.com/',
        };
    }
}
