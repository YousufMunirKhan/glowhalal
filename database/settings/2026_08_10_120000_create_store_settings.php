<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Every value defaults to null / empty / false on purpose.
 *
 * The storefront previously carried invented placeholders — a fabricated
 * founder, a fabricated Lahore address, invented phone numbers. None of that is
 * real, so none of it is seeded here. A blank field the owner fills in is
 * correct; a plausible-looking default that survives to production is not.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // Contact
        $this->migrator->add('store.contact_email', null);
        $this->migrator->add('store.contact_phone', null);
        $this->migrator->add('store.whatsapp_number', null);
        $this->migrator->add('store.address_line', null);
        $this->migrator->add('store.city', null);
        $this->migrator->add('store.postal_code', null);
        $this->migrator->add('store.opening_hours', null);

        // Brand / about
        $this->migrator->add('store.founder_name', null);
        $this->migrator->add('store.founder_title', null);
        $this->migrator->add('store.founder_bio', null);
        $this->migrator->add('store.founder_photo_path', null);
        $this->migrator->add('store.brand_story', null);

        // Storefront copy — the announcement bar stays off until it has copy.
        $this->migrator->add('store.announcement_enabled', false);
        $this->migrator->add('store.announcement_text', null);
        $this->migrator->add('store.announcement_url', null);
        $this->migrator->add('store.hero_heading', null);
        $this->migrator->add('store.hero_subheading', null);
        $this->migrator->add('store.hero_cta_label', null);
        $this->migrator->add('store.hero_cta_url', null);
        $this->migrator->add('store.free_delivery_threshold_amount', null);

        // Delivery — no courier is named until the owner names one.
        $this->migrator->add('store.couriers', []);
        $this->migrator->add('store.delivery_estimates', []);
        $this->migrator->add('store.cod_enabled', true);
        $this->migrator->add('store.cod_fee_amount', null);
        $this->migrator->add('store.delivery_note', null);
        $this->migrator->add('store.returns_policy_summary', null);

        // Social
        $this->migrator->add('store.instagram_url', null);
        $this->migrator->add('store.facebook_url', null);
        $this->migrator->add('store.whatsapp_url', null);
        $this->migrator->add('store.tiktok_url', null);
        $this->migrator->add('store.youtube_url', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('store.contact_email');
        $this->migrator->deleteIfExists('store.contact_phone');
        $this->migrator->deleteIfExists('store.whatsapp_number');
        $this->migrator->deleteIfExists('store.address_line');
        $this->migrator->deleteIfExists('store.city');
        $this->migrator->deleteIfExists('store.postal_code');
        $this->migrator->deleteIfExists('store.opening_hours');
        $this->migrator->deleteIfExists('store.founder_name');
        $this->migrator->deleteIfExists('store.founder_title');
        $this->migrator->deleteIfExists('store.founder_bio');
        $this->migrator->deleteIfExists('store.founder_photo_path');
        $this->migrator->deleteIfExists('store.brand_story');
        $this->migrator->deleteIfExists('store.announcement_enabled');
        $this->migrator->deleteIfExists('store.announcement_text');
        $this->migrator->deleteIfExists('store.announcement_url');
        $this->migrator->deleteIfExists('store.hero_heading');
        $this->migrator->deleteIfExists('store.hero_subheading');
        $this->migrator->deleteIfExists('store.hero_cta_label');
        $this->migrator->deleteIfExists('store.hero_cta_url');
        $this->migrator->deleteIfExists('store.free_delivery_threshold_amount');
        $this->migrator->deleteIfExists('store.couriers');
        $this->migrator->deleteIfExists('store.delivery_estimates');
        $this->migrator->deleteIfExists('store.cod_enabled');
        $this->migrator->deleteIfExists('store.cod_fee_amount');
        $this->migrator->deleteIfExists('store.delivery_note');
        $this->migrator->deleteIfExists('store.returns_policy_summary');
        $this->migrator->deleteIfExists('store.instagram_url');
        $this->migrator->deleteIfExists('store.facebook_url');
        $this->migrator->deleteIfExists('store.whatsapp_url');
        $this->migrator->deleteIfExists('store.tiktok_url');
        $this->migrator->deleteIfExists('store.youtube_url');
    }
};
