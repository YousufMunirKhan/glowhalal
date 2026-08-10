<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.site_name', 'Glow Halal');
        $this->migrator->add('seo.default_meta_title', null);
        $this->migrator->add('seo.title_suffix', ' | Glow Halal');
        $this->migrator->add('seo.default_meta_description', null);
        $this->migrator->add('seo.default_og_image_path', null);

        // Starts false deliberately. The site is not finished, and shipping a
        // half-built storefront into the index is far more expensive to undo
        // than flipping this on at launch.
        $this->migrator->add('seo.is_indexable', false);

        $this->migrator->add('seo.google_analytics_id', null);
        $this->migrator->add('seo.google_tag_manager_id', null);
        $this->migrator->add('seo.meta_pixel_id', null);
        $this->migrator->add('seo.google_site_verification', null);
        $this->migrator->add('seo.organisation_name', null);
        $this->migrator->add('seo.organisation_logo_path', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.site_name');
        $this->migrator->deleteIfExists('seo.default_meta_title');
        $this->migrator->deleteIfExists('seo.title_suffix');
        $this->migrator->deleteIfExists('seo.default_meta_description');
        $this->migrator->deleteIfExists('seo.default_og_image_path');
        $this->migrator->deleteIfExists('seo.is_indexable');
        $this->migrator->deleteIfExists('seo.google_analytics_id');
        $this->migrator->deleteIfExists('seo.google_tag_manager_id');
        $this->migrator->deleteIfExists('seo.meta_pixel_id');
        $this->migrator->deleteIfExists('seo.google_site_verification');
        $this->migrator->deleteIfExists('seo.organisation_name');
        $this->migrator->deleteIfExists('seo.organisation_logo_path');
    }
};
