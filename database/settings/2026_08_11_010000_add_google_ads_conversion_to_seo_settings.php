<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Google Ads conversion label. Optional and blank-safe: when the owner pastes a
 * value like "AW-123456789/AbCdEfGhIj" in Admin → SEO & Integrations, the
 * purchase page fires a Google Ads conversion (consent-gated). Left null,
 * nothing fires — the field is inert until an Ads account is connected.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.google_ads_conversion', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.google_ads_conversion');
    }
};
