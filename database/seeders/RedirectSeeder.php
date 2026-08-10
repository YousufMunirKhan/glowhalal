<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * Legacy WordPress → Laravel migration redirects.
 *
 * Only the SAFE ones whose targets already exist are seeded here. The three
 * legacy blog-post 301s are intentionally NOT seeded yet — their target
 * /blog/{slug} posts must be published FIRST (a 301 to a missing page loses the
 * equity it was meant to preserve). Add them once the posts exist:
 *
 *   /embrace-natural-care-the-benefits-of-neem-soap                -> /blog/neem-soap-benefits-skin
 *   /the-hidden-dangers-of-market-soaps-...-pimples-in-pakistan     -> /blog/pimples-in-pakistan-heat-humidity
 *   /the-hidden-dangers-of-store-bought-soaps-for-your-skin         -> /blog/whats-really-in-your-bar-soap
 *
 * Also deferred: wp-sitemap.xml -> /sitemap.xml (no sitemap route yet).
 *
 * See docs/seo-migration-and-content-plan.md §A1 for the full map.
 */
class RedirectSeeder extends Seeder
{
    public function run(): void
    {
        // from_path must be normalised: lowercase, leading slash, NO trailing slash.
        // Targets are demo-data-independent (/shop and /shop/oils always exist in
        // production), so redirects never point at a category the deploy lacks.
        $redirects = [
            // Live WooCommerce products (placeholder creams + a set) — no real 1:1
            // product exists, so send to the shop / the real oils category.
            ['/product/nourishing-halal-face-cream',        '/shop/oils'],
            ['/product/glow-halal-nourishing-face-cream',   '/shop/oils'],
            ['/product/glow-halal-nourishing-face-cream-2', '/shop/oils'],
            ['/product/glow-halal-nourishing-cream',        '/shop/oils'],
            ['/product/natural-glow-skincare-set',          '/shop'],

            // WordPress taxonomy pages.
            ['/category/skincare',      '/shop'],
            ['/category/health-beauty', '/shop'],

            // WooCommerce account page — no account area on the new site.
            ['/my-account', '/'],
        ];

        foreach ($redirects as [$from, $to]) {
            Redirect::updateOrCreate(
                ['from_path' => $from],
                ['to_path' => $to, 'status_code' => 301, 'source' => 'import', 'is_active' => true],
            );
        }
    }
}
