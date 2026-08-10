<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Fills the store contact details (email, phone, WhatsApp, address) with the
 * values that were previously hard-coded across the storefront, so from now on
 * they live in ONE place — Admin → Settings → Store settings — and every page,
 * the footer, the WhatsApp buttons and the SEO schema read from there.
 *
 * Only fills a field that is still blank, so it never overwrites a value the
 * owner has already set in admin.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = [
            'store.contact_email' => 'hello@glowhalal.com',
            'store.contact_phone' => '+92 300 1234567',
            'store.whatsapp_number' => '+92 300 1234567',
            'store.address_line' => 'Saddar',
            'store.city' => 'Karachi',
            'store.postal_code' => '7550',
        ];

        foreach ($defaults as $key => $value) {
            $this->migrator->update($key, fn ($current) => filled($current) ? $current : $value);
        }
    }

    public function down(): void
    {
        // No-op: these are content defaults, not schema.
    }
};
