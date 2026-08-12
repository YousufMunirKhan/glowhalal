<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Adds the Meta (Facebook) domain verification code
 * <meta name="facebook-domain-verification">, which proves domain ownership in
 * Meta Business Suite (needed for Commerce / catalog feeds and ad domain
 * claims). Seeded with the code Meta issued for glowhalal.com — like the
 * Google/Bing codes it is public by design (it ships in the page HTML), so
 * committing it is safe. Editable in Admin → SEO & Integrations.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.facebook_domain_verification', 'hvldjwom72c3cuf1pap6118q11j07f');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.facebook_domain_verification');
    }
};
