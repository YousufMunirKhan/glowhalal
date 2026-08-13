<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Google Ads account tag (the "Google tag", AW-XXXXXXXXX).
 *
 * Distinct from `seo.google_ads_conversion`, which holds a single conversion
 * ACTION ("AW-XXXXXXXXX/AbCdEfGhIj"). The account tag is what Google Ads asks
 * you to put on every page: it builds remarketing audiences and feeds campaign
 * optimisation. A conversion label cannot stand in for it, and an account ID
 * cannot record a conversion — the two fields do different jobs.
 *
 * Seeded with the live account so the tag is active the moment this migration
 * runs; the owner can change or clear it in Admin → SEO & Integrations. Blank
 * means nothing is emitted, same as every other ID on that page.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.google_ads_id', 'AW-18386456759');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.google_ads_id');
    }
};
