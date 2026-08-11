<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('blog.ai_images_enabled', true);
        $this->migrator->add('blog.image_provider', 'gemini_first');
        $this->migrator->add('blog.gemini_daily_limit', 2);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('blog.ai_images_enabled');
        $this->migrator->deleteIfExists('blog.image_provider');
        $this->migrator->deleteIfExists('blog.gemini_daily_limit');
    }
};
