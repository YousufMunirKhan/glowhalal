<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Ingredient;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * /sitemap.xml — the single storefront sitemap (SEO §7.5).
 *
 * Built with spatie/laravel-sitemap. Every indexable, published URL is listed
 * exactly once. Blog posts appear in BOTH locales, each entry carrying
 * xhtml:link alternates that MIRROR the on-page hreflang cluster — so Google
 * sees the en and ur-Latn versions as a reciprocal alternate set, never as
 * duplicates competing for the same query.
 *
 * Deliberately EXCLUDED: /cart, /checkout, /account, /login, /search — these are
 * noindex/transactional and must never enter the index (they are also
 * Disallow'd in robots.txt).
 */
class SitemapController extends Controller
{
    /**
     * CMS page slugs that must never be listed even if a row somehow exists:
     * the noindex/transactional set, plus the reserved hub slugs that are served
     * by their own controllers (and listed as static hubs below) — a CMS Page
     * row with one of these slugs is unreachable behind the real route, so it
     * must not create a duplicate sitemap entry.
     */
    private const EXCLUDED_SLUGS = [
        'cart', 'checkout', 'account', 'login', 'search',
        'about', 'halal-ingredients', 'what-we-never-use', 'blog',
    ];

    public function __invoke(Request $request): Response
    {
        $sitemap = Sitemap::create();

        // ── Static entries ────────────────────────────────────────────────
        $sitemap->add(
            Url::create(url('/'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(1.0)
        );
        $sitemap->add(
            Url::create(url('/shop'))
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );

        // ── Editorial + evidence hubs ─────────────────────────────────────
        // The index/landing pages that were missing entirely: a crawler could
        // reach individual posts and ingredient pages but never the hubs that
        // link them. Each is served by its own controller (not a CMS Page).
        foreach ([
            ['/about', 0.7, Url::CHANGE_FREQUENCY_MONTHLY],
            ['/halal-ingredients', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['/what-we-never-use', 0.7, Url::CHANGE_FREQUENCY_MONTHLY],
            ['/blog', 0.7, Url::CHANGE_FREQUENCY_WEEKLY],
            ['/ur-roman/blog', 0.6, Url::CHANGE_FREQUENCY_WEEKLY],
            ['/contact', 0.5, Url::CHANGE_FREQUENCY_MONTHLY],
        ] as [$path, $priority, $freq]) {
            $sitemap->add(
                Url::create(url($path))
                    ->setChangeFrequency($freq)
                    ->setPriority($priority)
            );
        }

        // Category hubs — shop categories with published products, and blog
        // categories with published posts (they are indexable, internally
        // linked, and were missing from the sitemap).
        \App\Models\Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($q) => $q->published())
            ->each(fn ($c) => $sitemap->add(
                Url::create(url('/shop/'.$c->slug))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6)
            ));

        \App\Models\BlogCategory::query()
            ->where('is_active', true)
            ->whereHas('posts', fn ($q) => $q->published())
            ->each(fn ($c) => $sitemap->add(
                Url::create(url('/blog/category/'.$c->slug))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.5)
            ));

        // ── Products ──────────────────────────────────────────────────────
        Product::query()
            ->published()
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->reject(fn (Product $p) => ! $p->isIndexable())
            ->each(fn (Product $p) => $sitemap->add(
                Url::create(url('/products/'.$p->slug))
                    ->setLastModificationDate($p->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            ));

        // ── Blog posts, BOTH locales, with mirrored hreflang alternates ───
        $this->addBlogPosts($sitemap);

        // ── Ingredient index pages ────────────────────────────────────────
        Ingredient::query()
            ->published()
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->reject(fn (Ingredient $i) => ! $i->isIndexable())
            ->each(fn (Ingredient $i) => $sitemap->add(
                Url::create(url('/halal-ingredients/'.$i->slug))
                    ->setLastModificationDate($i->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.6)
            ));

        // ── CMS pages (privacy, terms, faq, …) ────────────────────────────
        Page::query()
            ->published()
            ->with('seoMeta')
            ->get(['id', 'slug', 'updated_at'])
            ->reject(fn (Page $page) => in_array($page->slug, self::EXCLUDED_SLUGS, true) || ! $page->isIndexable())
            ->each(fn (Page $page) => $sitemap->add(
                Url::create(url('/'.$page->slug))
                    ->setLastModificationDate($page->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.5)
            ));

        return $sitemap->toResponse($request);
    }

    /**
     * One <url> per published post per locale. When a post belongs to a
     * multi-member translation group, its entry lists xhtml:link alternates for
     * every published member plus x-default (English), exactly mirroring
     * BlogController::hreflangCluster(). A lonely post gets no alternates.
     */
    private function addBlogPosts(Sitemap $sitemap): void
    {
        /** @var Collection<int, BlogPost> $posts */
        $posts = BlogPost::query()
            ->published()
            ->with('seoMeta')
            ->get(['id', 'slug', 'locale', 'translation_group_id', 'updated_at'])
            ->reject(fn (BlogPost $p) => ! $p->isIndexable())
            ->values();

        $groups = $posts
            ->filter(fn (BlogPost $p) => filled($p->translation_group_id))
            ->groupBy('translation_group_id');

        foreach ($posts as $post) {
            $url = Url::create($this->blogUrl($post))
                ->setLastModificationDate($post->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7);

            $members = filled($post->translation_group_id)
                ? $groups->get($post->translation_group_id, collect([$post]))
                : collect([$post]);

            if ($members->count() > 1) {
                foreach ($members as $member) {
                    $url->addAlternate($this->blogUrl($member), $this->hreflang($member->locale));
                }

                $english = $members->firstWhere('locale', 'en');
                $url->addAlternate($this->blogUrl($english ?? $post), 'x-default');
            }

            $sitemap->add($url);
        }
    }

    private function blogUrl(BlogPost $post): string
    {
        $prefix = $post->locale === 'ur-Latn' ? '/ur-roman' : '';

        return url($prefix.'/blog/'.$post->slug);
    }

    private function hreflang(string $locale): string
    {
        return $locale === 'ur-Latn' ? 'ur-Latn' : 'en';
    }
}
