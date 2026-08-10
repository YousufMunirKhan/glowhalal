<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Moves the previously hard-coded integration keys (Google sign-in, Google
 * Analytics) into the settings store so the owner can edit them in
 * Admin → Settings → SEO & Integrations, and adds Bing site verification
 * (which also verifies the site for Yahoo, since Yahoo search runs on Bing).
 *
 * The Google keys are seeded with the values already in use so the admin
 * fields show them pre-filled rather than blank. config/services.php keeps the
 * same values as a last-resort fallback when the DB is unavailable.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        // --- Search engine verification -------------------------------------
        // Google Search Console <meta name="google-site-verification"> already
        // exists (seo.google_site_verification). Add the Bing/Yahoo equivalent
        // <meta name="msvalidate.01">.
        $this->migrator->add('seo.bing_site_verification', null);

        // --- Google sign-in (OAuth) -----------------------------------------
        $this->migrator->add('seo.google_oauth_client_id', '613174641872-q0sv3bbvkb6tgedgfkvfo58937rlh3hn.apps.googleusercontent.com');
        // Secret comes from the environment — never committed to the repo. On an
        // existing install the real value is already in the settings table; a
        // fresh install seeds it from .env (blank if unset, then set in admin).
        $this->migrator->add('seo.google_oauth_client_secret', env('GOOGLE_CLIENT_SECRET'));
        $this->migrator->add('seo.google_oauth_redirect', 'https://glowhalal.com/auth/google/callback');

        // --- Google Analytics -----------------------------------------------
        // The measurement ID was hard-coded in config; fill the settings field
        // with it if the owner has not already set one, so it shows in admin.
        $this->migrator->update(
            'seo.google_analytics_id',
            fn ($value) => filled($value) ? $value : 'G-K88S432NS2',
        );
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.bing_site_verification');
        $this->migrator->deleteIfExists('seo.google_oauth_client_id');
        $this->migrator->deleteIfExists('seo.google_oauth_client_secret');
        $this->migrator->deleteIfExists('seo.google_oauth_redirect');
    }
};
