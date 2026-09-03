<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * Legacy WordPress → Laravel migration redirects.
 *
 * Every published URL of the old WordPress site (extracted from the
 * backup_old_wp_* DB dump: pages, posts, products, taxonomies, feeds) must
 * resolve in ONE 301 hop to a live page — a 404 bleeds whatever equity the old
 * domain earned, permanently.
 *
 * The three legacy blog posts point at /blog FOR NOW: their planned 1:1
 * replacement posts (per docs/seo-migration-and-content-plan.md §A1) were
 * never written. A 301 to the live blog index today beats a 404 while waiting.
 * When the dedicated posts get published, RE-POINT these rows (here + rerun,
 * or directly in Admin → Redirects):
 *
 *   /embrace-natural-care-the-benefits-of-neem-soap                -> /blog/neem-soap-benefits-skin
 *   /the-hidden-dangers-of-market-soaps-...-pimples-in-pakistan     -> /blog/pimples-in-pakistan-heat-humidity
 *   /the-hidden-dangers-of-store-bought-soaps-for-your-skin         -> /blog/whats-really-in-your-bar-soap
 */
class RedirectSeeder extends Seeder
{
    public function run(): void
    {
        // from_path must be normalised: lowercase, leading slash, NO trailing slash.
        // Targets are demo-data-independent (/shop and /shop/oils always exist in
        // production), so redirects never point at a category the deploy lacks.
        $redirects = [
            // Live WooCommerce products. The four face-cream URLs pulled 116 GSC
            // impressions in the 28 days to 3 Sep 2026 and produced zero clicks: they
            // 301d to /shop/oils, which sells no cream, and Google treats a redirect
            // to a topically unrelated page as a soft 404 — the signal is not passed
            // and the dead URL keeps floating. /blog/best-herbal-face-cream-pakistan is
            // the honest match (there is no face-cream SKU), and "herbal cream for face"
            // is one of only four queries that has ever converted a click.
            ['/product/nourishing-halal-face-cream',        '/blog/best-herbal-face-cream-pakistan'],
            ['/product/glow-halal-nourishing-face-cream',   '/blog/best-herbal-face-cream-pakistan'],
            ['/product/glow-halal-nourishing-face-cream-2', '/blog/best-herbal-face-cream-pakistan'],
            ['/product/glow-halal-nourishing-cream',        '/blog/best-herbal-face-cream-pakistan'],
            ['/product/natural-glow-skincare-set',          '/shop'],

            // WordPress taxonomy pages.
            ['/category/skincare',      '/shop'],
            ['/category/health-beauty', '/shop'],

            // WooCommerce account page — no account area on the new site.
            ['/my-account', '/'],

            // Legacy blog posts — interim target, see class docblock.
            ['/embrace-natural-care-the-benefits-of-neem-soap', '/blog'],
            ['/the-hidden-dangers-of-market-soaps-understanding-the-causes-of-pimples-in-pakistan', '/blog/pimples-in-pakistan-heat-humidity'],
            ['/the-hidden-dangers-of-store-bought-soaps-for-your-skin', '/blog'],

            // WordPress tag archives (both had published posts).
            ['/tag/natural-soaps',      '/blog'],
            ['/tag/store-bought-soaps', '/blog'],

            // WordPress RSS feeds — the blog index is the nearest live thing.
            // Per-page feed variants (/product/x/feed/ etc.) don't need rows:
            // LegacyRedirectMiddleware strips the cruft suffix and re-uses the
            // base URL's row. /comments/feed is listed because its "base"
            // (/comments) never existed as a page, so the retry can't help it.
            ['/feed', '/blog'],
            ['/comments/feed', '/blog'],

            // Old sitemap names Google keeps probing after a migration:
            // wp-sitemap.xml is WordPress core, sitemap_index.xml is Yoast.
            ['/wp-sitemap.xml',    '/sitemap.xml'],
            ['/sitemap_index.xml', '/sitemap.xml'],
        ];

        foreach ($redirects as [$from, $to]) {
            Redirect::updateOrCreate(
                ['from_path' => $from],
                ['to_path' => $to, 'status_code' => 301, 'source' => 'import', 'is_active' => true],
            );
        }
    }
}
