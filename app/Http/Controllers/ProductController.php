<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Seo\ProductOffer;
use App\Support\Seo\SchemaGraph;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * The product detail page, in both content locales. Everything indexable —
 * name, price, the full INCI list, the exclusion list, the description — is
 * server-rendered Blade. The only Livewire on the page is the buy box, and its
 * price is rendered by the server on first paint from the same ProductOffer
 * that feeds the JSON-LD, so the visible price and `Product.offers.price`
 * cannot disagree.
 *
 * Bilingual contract (mirrors BlogController): English lives at
 * /products/{slug}; Roman Urdu at /ur-roman/products/{slug_ur} — an ALTERNATE,
 * never a duplicate. Each version is self-canonical and the two reference each
 * other through a reciprocal hreflang cluster. The UR page exists only when
 * real UR content is filled (Product::hasRomanUrdu()); it never falls back to
 * English text on a /ur-roman URL.
 */
class ProductController extends Controller
{
    public function show(Product $product): View|RedirectResponse
    {
        // MySQL's ci collation resolves any case variant of the slug, which
        // would serve duplicate 200 content on infinite URL spellings — force
        // the canonical casing with a single 301 instead.
        if (request()->segment(2) !== $product->slug) {
            return redirect()->route('products.show', $product->slug, 301);
        }

        return $this->render($product, 'en');
    }

    public function showUr(string $slug): View|RedirectResponse
    {
        // Manual lookup — the implicit {product} binding resolves the ENGLISH
        // slug, and a UR URL must never resolve (or redirect to) English
        // content. No UR content ⇒ no page, exactly like an unpublished post.
        $product = Product::query()
            ->published()
            ->where('slug_ur', $slug)
            ->firstOrFail();

        abort_unless($product->hasRomanUrdu(), 404);

        // Same case-canonical rule as English; the slug sits one segment deeper
        // under the /ur-roman prefix.
        if (request()->segment(3) !== $product->slug_ur) {
            return redirect()->route('products.show.ur', $product->slug_ur, 301);
        }

        return $this->render($product, 'ur-Latn');
    }

    private function render(Product $product, string $locale): View
    {
        $isUr = $locale === 'ur-Latn';

        $product->load([
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
            'variants.inventory',
            'variants.attributeValues.attribute',
            'primaryCategory',
            'categories',
            'images',
            'ingredients',
            'verifiedFreeFromAttributes',
            'halalProfile',
            'seoMeta',
        ]);

        $offer = ProductOffer::forProduct($product);
        $category = $product->primaryCategory;

        $canonical = $isUr
            ? route('products.show.ur', $product->slug_ur)
            : route('products.show', $product->slug);

        // Reciprocal hreflang cluster — only when both language versions exist,
        // and identical from whichever side it is rendered (x-default → English,
        // matching the blog convention).
        $hreflang = $product->hasRomanUrdu() ? [
            ['hreflang' => 'en', 'href' => route('products.show', $product->slug)],
            ['hreflang' => 'ur-Latn', 'href' => route('products.show.ur', $product->slug_ur)],
            ['hreflang' => 'x-default', 'href' => route('products.show', $product->slug)],
        ] : [];

        // UR meta resolves from the UR columns (seo_metas stays English-only);
        // computed BEFORE the attribute swap so the English fallbacks in
        // resolvedMeta*() are not polluted by swapped values.
        if ($isUr) {
            $metaTitle = $product->meta_title_ur ?: $product->name_ur;
            $metaDescription = \Illuminate\Support\Str::limit(
                trim(strip_tags((string) ($product->meta_description_ur ?: $product->short_description_ur))),
                158,
                '',
            ) ?: null;

            // One swap localizes the whole render path: view, WhatsApp
            // prefill, analytics data-* and JSON-LD all read the same
            // attributes. In-memory only; nothing on a GET saves the model.
            $product->presentInRomanUrdu();
        } else {
            $metaTitle = $product->resolvedMetaTitle() ?? $product->name;
            $metaDescription = \Illuminate\Support\Str::limit(
                trim(strip_tags((string) ($product->resolvedMetaDescription() ?? ''))),
                158,
                '',
            ) ?: null;
        }

        $trail = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Shop', 'url' => route('shop.index')],
        ];

        if ($category) {
            foreach ($category->ancestorTrail() as $ancestor) {
                $trail[] = ['name' => $ancestor->name, 'url' => route('shop.category', $ancestor->slug)];
            }

            $trail[] = ['name' => $category->name, 'url' => route('shop.category', $category->slug)];
        }

        $trail[] = ['name' => $product->name, 'url' => $canonical];

        $graph = SchemaGraph::make()
            ->organization()
            ->webPage($canonical, $product->name, $metaDescription, $isUr ? 'ur-Latn' : 'en-PK')
            ->breadcrumbs($trail)
            ->product($product, $offer, $category, $canonical)
            // Same Q&As the page renders visibly below — never an invisible set.
            ->faqPage($canonical, $product->faqs ?? []);

        // Money page → guide links close the internal-linking loop: posts link
        // the PDPs, and this passes authority (and a crawl path) back. Each
        // locale links its own language's guides so the reader never bounces
        // between languages mid-journey.
        $guides = $product->blogPosts()
            ->published()
            ->forLocale($locale)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get(['blog_posts.id', 'blog_posts.title', 'blog_posts.slug']);

        if ($guides->isEmpty()) {
            $guides = \App\Models\BlogPost::query()
                ->published()
                ->forLocale($locale)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get(['id', 'title', 'slug']);
        }

        // Out of stock keeps its URL, stays index,follow, and reports
        // availability honestly (seo.md §2.6). Never 404 or noindex a
        // temporarily sold-out product — the ranking is re-earned from scratch.
        return view('products.show', [
            'product' => $product,
            'offer' => $offer,
            'category' => $category,
            'guides' => $guides,
            'trail' => $trail,
            'canonical' => $canonical,
            'hreflang' => $hreflang,
            'robots' => $product->isIndexable() ? 'index,follow' : 'noindex,follow',
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'schema' => $graph->toJson(),
        ]);
    }
}
