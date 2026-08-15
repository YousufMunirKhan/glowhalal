<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Fill in the store's real social profiles.
 *
 * The keys have existed since `create_store_settings` but were all null, and
 * `JsonLd::organization()` builds `sameAs` with `array_filter` — so an empty
 * set drops the key from the Organization node entirely. The practical effect
 * is that Glow Halal exists, to a language model, only on its own domain: no
 * corroborating profile anywhere else, so an assistant asked to recommend a
 * halal herbal brand in Pakistan has nothing to verify the entity against.
 *
 * `sameAs` is the cheapest entity signal there is, and it was one setting away.
 *
 * URLs supplied by the owner on 15 Aug 2026 and each verified to return HTTP 200
 * before being written here — a `sameAs` pointing at a dead profile is a worse
 * signal than no `sameAs` at all.
 *
 * `youtube_url` stays null: no channel exists yet. It appears in `sameAs` and
 * in the footer automatically the moment it is set in Admin → Store.
 *
 * `update()` rather than `add()` because the keys already exist; using `add()`
 * would throw on a database that has run the original migration.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('store.instagram_url', fn () => 'https://www.instagram.com/glowhalalpk/');
        $this->migrator->update('store.facebook_url', fn () => 'https://www.facebook.com/glowhalalpk');
        $this->migrator->update('store.tiktok_url', fn () => 'https://www.tiktok.com/@glowhalal3');
    }

    public function down(): void
    {
        $this->migrator->update('store.instagram_url', fn () => null);
        $this->migrator->update('store.facebook_url', fn () => null);
        $this->migrator->update('store.tiktok_url', fn () => null);
    }
};
