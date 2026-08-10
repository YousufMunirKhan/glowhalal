<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Defaults for the Social planner. Read anywhere with app(SocialSettings::class).
 * Nothing here invents a value — the owner fills these in from the settings page.
 */
class SocialSettings extends Settings
{
    /**
     * Default hashtags dropped into a fresh caption. Bare `array` on purpose —
     * see the note on StoreSettings::$couriers about Spatie's SettingsCast.
     */
    public array $default_hashtags;

    /** Brand sign-off appended to captions, e.g. "— Glow Halal 🌿". */
    public ?string $brand_signature;

    /** Default call-to-action wa.me link (WhatsApp order link). */
    public ?string $default_cta_url;

    /** IANA timezone the calendar and scheduling use. */
    public ?string $timezone;

    public static function group(): string
    {
        return 'social';
    }

    public function timezoneOrDefault(): string
    {
        return $this->timezone ?: 'Asia/Karachi';
    }
}
