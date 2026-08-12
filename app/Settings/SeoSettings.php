<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Site-wide SEO defaults. Per-record overrides live in `seo_metas` (§7.3);
 * these are the fallbacks used when a record has none.
 */
class SeoSettings extends Settings
{
    public ?string $site_name;

    public ?string $default_meta_title;

    /** Appended to every page title, e.g. " | Glow Halal". */
    public ?string $title_suffix;

    public ?string $default_meta_description;

    public ?string $default_og_image_path;

    /**
     * Master switch. False writes `noindex` site-wide — correct while the site
     * is being built, and the single most damaging setting to leave on by
     * mistake once it launches.
     */
    public bool $is_indexable;

    public ?string $google_analytics_id;

    public ?string $google_tag_manager_id;

    public ?string $meta_pixel_id;

    /**
     * Google Ads conversion action, e.g. "AW-123456789/AbCdEfGhIj". Optional and
     * blank-safe — the purchase page fires a Google Ads conversion only when this
     * is set (and cookie consent is granted). Blank means nothing fires.
     */
    public ?string $google_ads_conversion;

    public ?string $google_site_verification;

    /** Bing Webmaster Tools <meta name="msvalidate.01">. Also verifies Yahoo. */
    public ?string $bing_site_verification;

    /** Meta Business Suite <meta name="facebook-domain-verification">. */
    public ?string $facebook_domain_verification;

    // ---- Google sign-in (OAuth) --------------------------------------------
    public ?string $google_oauth_client_id;

    public ?string $google_oauth_client_secret;

    public ?string $google_oauth_redirect;

    public ?string $organisation_name;

    public ?string $organisation_logo_path;

    public static function group(): string
    {
        return 'seo';
    }

    public function robotsDirective(): string
    {
        return $this->is_indexable ? 'index,follow' : 'noindex,nofollow';
    }

    public function title(?string $pageTitle = null): string
    {
        $base = $pageTitle ?: $this->default_meta_title ?: $this->site_name ?: '';

        return filled($this->title_suffix) && ! str_contains($base, (string) $this->title_suffix)
            ? $base.$this->title_suffix
            : $base;
    }

    public function hasAnalytics(): bool
    {
        return filled($this->google_analytics_id) || filled($this->google_tag_manager_id);
    }
}
