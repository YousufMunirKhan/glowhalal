<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Blog auto-publish + AI cover image settings, owner-editable in
 * Admin → Settings → Blog & AI Images.
 *
 * Auto-publish itself needs no flag: a post with status "Published" and a
 * future "Published at" goes live automatically at that moment (the
 * published() scope enforces it). These settings govern the cover images
 * that are generated at that publish moment.
 */
class BlogSettings extends Settings
{
    /** Master switch for AI cover generation at publish time. */
    public bool $ai_images_enabled;

    /** 'gemini_first' (fall back to Pollinations) or 'pollinations_only'. */
    public string $image_provider;

    /**
     * Max Gemini generations per day; beyond it the free Pollinations
     * fallback is used. Keeps within the owner's expected free allowance
     * (and caps spend once Gemini billing is enabled).
     */
    public int $gemini_daily_limit;

    public static function group(): string
    {
        return 'blog';
    }
}
