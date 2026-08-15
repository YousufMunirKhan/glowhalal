<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stop three of our own pages competing for "lookman e hayat oil price in
 * pakistan".
 *
 * Live on 15 Aug 2026, all three targeted the same query:
 *
 *   1. /blog/lookman-e-hayat-oil-price-in-pakistan   — exact-match title + H1
 *   2. /blog/lookman-e-hayat-oil-uses-benefits-price — "Price" in slug, title
 *      AND an H2 reading "Lookman-e-Hayat oil price in Pakistan"
 *   3. /products/herbal-skin-oil-50ml — title "Buy Lookman e Hayat Oil 50ml –
 *      Rs 1,200 COD Pakistan"
 *
 * Google picks one and suppresses the others, and which one it picks is not
 * ours to choose — so all three rank worse than a single page would.
 *
 * The dedicated price post (1) is the designated owner: it matches the query
 * exactly and has nothing else to do. This migration de-optimises (2) and (3)
 * onto their own distinct intents:
 *
 *   (2) becomes a uses/benefits/how-to page. Its slug is deliberately LEFT
 *       ALONE — a 301 would cost more equity than the word "price" in a URL is
 *       worth, and the slug is a weak signal next to title and H2.
 *   (3) becomes a transactional "buy online" page. Dropping "Rs 1,200" from the
 *       title also removes a hardcoded price that goes stale the day the owner
 *       changes it; the real price is in the Offer schema and on the page.
 *
 * TITLE STORAGE, because it is not obvious and getting it wrong makes this
 * migration a silent no-op (which is exactly how the 13 Aug WhatsApp fix
 * failed):
 *   - SEO overrides live in `seo_metas.meta_title` (NOT `.title`), keyed
 *     polymorphically, with a parallel `og_title`.
 *   - `BlogController:229` reads `seoMeta?->meta_title ?: $post->title`, so a
 *     post can carry its title in either place — both are handled here.
 *   - The visible ` | Glow Halal` suffix is appended by
 *     `products/show.blade.php:61`, so it is NOT part of the stored value and
 *     must not appear in the strings matched below.
 *   - Blog titles are truncated to 65 chars at render
 *     (`BlogController:258`); the replacements are within that.
 *
 * Everything is matched on exact current strings and skipped when they have
 * already changed, so this is idempotent and will not clobber an owner edit
 * made in the admin between now and deploy.
 */
return new class extends Migration
{
    private const USES_POST_SLUG = 'lookman-e-hayat-oil-uses-benefits-price';

    private const PRODUCT_SLUG = 'herbal-skin-oil-50ml';

    private const POST_TITLE_OLD = 'Lookman-e-Hayat Oil: Uses, Benefits, Price & How to Use';

    private const POST_TITLE_NEW = 'Lookman-e-Hayat Oil: Uses, Benefits & How to Apply';

    private const POST_H2_OLD = 'Lookman-e-Hayat oil price in Pakistan';

    private const POST_H2_NEW = 'How much does it cost?';

    private const PRODUCT_TITLE_OLD = 'Buy Lookman e Hayat Oil 50ml – Rs 1,200 COD Pakistan';

    private const PRODUCT_TITLE_NEW = 'Lookman e Hayat Oil 50ml – Buy Online, COD Pakistan';

    public function up(): void
    {
        $this->retitlePost(self::POST_TITLE_OLD, self::POST_TITLE_NEW);
        $this->rewriteH2(self::POST_H2_OLD, self::POST_H2_NEW);
        $this->retitleProduct(self::PRODUCT_TITLE_OLD, self::PRODUCT_TITLE_NEW);
    }

    public function down(): void
    {
        $this->retitlePost(self::POST_TITLE_NEW, self::POST_TITLE_OLD);
        $this->rewriteH2(self::POST_H2_NEW, self::POST_H2_OLD);
        $this->retitleProduct(self::PRODUCT_TITLE_NEW, self::PRODUCT_TITLE_OLD);
    }

    /** The post's own title (its H1) and any SEO/OG override of it. */
    private function retitlePost(string $from, string $to): void
    {
        if (! Schema::hasTable('blog_posts')) {
            return;
        }

        $post = DB::table('blog_posts')->where('slug', self::USES_POST_SLUG)->first();

        if (! $post) {
            return;
        }

        if (($post->title ?? null) === $from) {
            DB::table('blog_posts')->where('id', $post->id)->update([
                'title' => $to,
                'updated_at' => now(),
            ]);
        }

        $this->updateSeoTitles(\App\Models\BlogPost::class, $post->id, $from, $to);
    }

    /**
     * The H2 duplicating the owner post's target phrase. Matched as a HEADING
     * only — a mention of the phrase inside a sentence is left alone. Handles
     * markdown (## …) and HTML (<h2>…</h2>) because either may be stored
     * depending on how the post was authored.
     */
    private function rewriteH2(string $from, string $to): void
    {
        if (! Schema::hasTable('blog_posts')) {
            return;
        }

        $post = DB::table('blog_posts')->where('slug', self::USES_POST_SLUG)->first();

        if (! $post || ! isset($post->content)) {
            return;
        }

        $content = (string) $post->content;

        $updated = preg_replace(
            [
                '/^(#{2,3}[ \t]*)'.preg_quote($from, '/').'[ \t]*$/mu',
                '/(<h2\b[^>]*>)\s*'.preg_quote($from, '/').'\s*(<\/h2>)/iu',
            ],
            [
                '$1'.$to,
                '$1'.$to.'$2',
            ],
            $content,
        ) ?? $content;

        if ($updated !== $content) {
            DB::table('blog_posts')->where('id', $post->id)->update([
                'content' => $updated,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Only the SEO override is touched. `products.name` is the on-page H1, the
     * cart line and the schema `Product.name` — it is already correct and must
     * not become a sentence.
     */
    private function retitleProduct(string $from, string $to): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        $product = DB::table('products')->where('slug', self::PRODUCT_SLUG)->first();

        if ($product) {
            $this->updateSeoTitles(\App\Models\Product::class, $product->id, $from, $to);
        }
    }

    /** Rewrites `meta_title` and `og_title` when each is exactly $from. */
    private function updateSeoTitles(string $type, int|string $id, string $from, string $to): void
    {
        if (! Schema::hasTable('seo_metas')) {
            return;
        }

        foreach (['meta_title', 'og_title'] as $column) {
            DB::table('seo_metas')
                ->where('seoable_type', $type)
                ->where('seoable_id', $id)
                ->where($column, $from)
                ->update([$column => $to, 'updated_at' => now()]);
        }
    }
};
