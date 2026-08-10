<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('social.default_hashtags', []);
        $this->migrator->add('social.brand_signature', null);
        $this->migrator->add('social.default_cta_url', null);

        // Pakistan Standard Time — the store operates from Pakistan and the owner
        // plans posts in local time.
        $this->migrator->add('social.timezone', 'Asia/Karachi');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('social.default_hashtags');
        $this->migrator->deleteIfExists('social.brand_signature');
        $this->migrator->deleteIfExists('social.default_cta_url');
        $this->migrator->deleteIfExists('social.timezone');
    }
};
