<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Meta (Facebook) Pixel ID — owner supplied 2490223724823454 on 16 Aug 2026.
 *
 * The Pixel plumbing has been in partials/tracking.blade.php since launch and
 * turns on the moment this value is non-blank: base code + PageView, plus the
 * ViewContent / AddToCart / InitiateCheckout / Purchase events already wired to
 * the same data-* attributes GA4 reads. Nothing to build — only the ID was
 * missing.
 *
 * Unlike the Google tag, the Pixel stays HARD-GATED on cookie consent: Meta has
 * no Consent Mode equivalent, so it loads only after the visitor accepts. That
 * is deliberate, not an oversight.
 *
 * Seeded here (not hardcoded in a view) so it stays owner-editable in
 * Admin → SEO & Integrations. Guarded: an existing value is never overwritten,
 * so re-running or an owner change in admin both survive.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('seo', function ($blueprint): void {
            $blueprint->update('meta_pixel_id', fn ($existing) => filled($existing) ? $existing : '2490223724823454');
        });
    }

    public function down(): void
    {
        $this->migrator->inGroup('seo', function ($blueprint): void {
            $blueprint->update('meta_pixel_id', fn ($existing) => $existing === '2490223724823454' ? null : $existing);
        });
    }
};
