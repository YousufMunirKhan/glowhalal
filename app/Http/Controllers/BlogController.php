<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * `/blog` — the Journal.
 *
 * Everything comes from BlogPost::published(), which is status=published AND
 * published_at in the past, so a post flipped to published with a future
 * timestamp cannot leak into the listing or the sitemap.
 *
 * The database currently holds no posts. The index therefore renders an empty
 * state rather than placeholder articles: fabricated editorial on a brand whose
 * whole position is "we publish what is true" would be self-defeating, and the
 * owner's real posts will populate this the moment they are written in the
 * admin. Nothing here needs changing when they are.
 *
 * Pagination is `?page=n` with real <a href> links (SEO §2.3 / §8.11). No
 * infinite scroll, no island append — every post needs a crawlable route to it.
 */
class BlogController extends Controller
{
    use BuildsStorefrontLayout;

    private const PER_PAGE = 9;

    public function index(): View
    {
        $locale = $this->contentLocale();

        $posts = BlogPost::query()
            ->published()
            ->forLocale($locale)
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE);

        // The featured treatment is page-1 only; on page 2 the same post would
        // be the second-newest and the layout would lie about it.
        $featured = $posts->currentPage() === 1 ? $posts->getCollection()->first() : null;
        $rest = $featured ? $posts->getCollection()->skip(1) : $posts->getCollection();

        $categories = BlogCategory::query()
            ->active()
            ->ordered()
            ->withCount(['posts' => fn (Builder $q) => $q->published()])
            ->get()
            ->filter(fn (BlogCategory $c) => $c->posts_count > 0);

        $page = $posts->currentPage();
        $canonical = $this->paginatedCanonical($page);

        // Locale-distinct meta: the two hubs must never read as duplicates.
        $isUr = $locale === 'ur-Latn';

        $title = $isUr
            ? ($page > 1
                ? 'Roman Urdu Journal - Page '.$page.' | Glow Halal'
                : 'Roman Urdu Journal - Herbal Tel Ke Guides | Glow Halal')
            : ($page > 1
                ? 'The Glow Halal Journal - Page '.$page
                : 'Journal - Ingredient Guides & Halal Formulation Notes');

        $description = $isUr
            ? ('Glow Halal ka Roman Urdu Journal: herbal tel ke istemal, qeemat aur khareedari ke asaan guides — Cash on Delivery poore Pakistan mein.'
                .($page > 1 ? ' Page '.$page.' of '.$posts->lastPage().'.' : ''))
            : ('What we are learning about halal cosmetic formulation, published as we go: '
                .'ingredient breakdowns, how to read an INCI list, and what we changed and why.'
                .($page > 1 ? ' Page '.$page.' of '.$posts->lastPage().'.' : ''));

        // Reciprocal hub-level hreflang (page 1 only): the two Journals are
        // language alternates of each other, exactly like the post pairs.
        $hubHreflang = $page === 1 ? [
            ['hreflang' => 'en', 'href' => url('/blog')],
            ['hreflang' => 'ur-Latn', 'href' => url('/ur-roman/blog')],
            ['hreflang' => 'x-default', 'href' => url('/blog')],
        ] : [];

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Journal'],
        ];

        return view('blog.index', [
            'contentLocale' => $locale,
            ...$this->layoutData('journal'),
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'posts' => $posts,
            'featured' => $featured,
            'rest' => $rest,
            'categories' => $categories,
            'crumbs' => $crumbs,
            'heading' => $isUr ? 'Roman Urdu Journal' : 'Journal',
            'intro' => $isUr
                ? 'Herbal tel ke asaan Roman Urdu guides — istemal, qeemat aur mehfooz khareedari.'
                : 'What we are learning about halal formulation, published as we go.',
            'hreflang' => $hubHreflang,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'Blog', [
                    'inLanguage' => $isUr ? 'ur-Latn' : 'en-PK',
                ]),
                JsonLd::breadcrumbs($canonical, $crumbs),
                $this->postItemList($canonical, $posts),
            ]),
        ]);
    }

    public function category(string $slug): View
    {
        $locale = $this->contentLocale();

        /** @var BlogCategory $category */
        $category = BlogCategory::query()->active()->where('slug', $slug)->firstOrFail();

        $posts = BlogPost::query()
            ->published()
            ->forLocale($locale)
            ->where('blog_category_id', $category->id)
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE);

        abort_if($posts->total() === 0, 404);

        $page = $posts->currentPage();
        $canonical = $this->paginatedCanonical($page);
        $title = $category->name.' Articles - Glow Halal Journal';
        $description = $category->resolvedMetaDescription()
            ?: 'Every Glow Halal Journal article filed under '.$category->name.'.';

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Journal', 'url' => url($this->localePrefix($locale).'/blog')],
            ['name' => $category->name],
        ];

        return view('blog.index', [
            'contentLocale' => $locale,
            ...$this->layoutData('journal'),
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => str($description)->limit(158)->toString(),
            'canonical' => $canonical,
            'posts' => $posts,
            'featured' => null,
            'rest' => $posts->getCollection(),
            'categories' => collect(),
            'crumbs' => $crumbs,
            'heading' => $category->name,
            'intro' => $category->description ?? null,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'CollectionPage'),
                JsonLd::breadcrumbs($canonical, $crumbs),
                $this->postItemList($canonical, $posts),
            ]),
        ]);
    }

    public function show(string $slug): View
    {
        $locale = $this->contentLocale();

        // 404s if the post does not exist FOR THIS LOCALE — an English slug under
        // /ur-roman (or vice versa) must not resolve to the other language's row.
        /** @var BlogPost $post */
        $post = BlogPost::query()
            ->published()
            ->forLocale($locale)
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags', 'products'])
            ->firstOrFail();

        // Same-locale related posts only, so a Roman-Urdu post never links out to
        // an English article as "read next".
        $related = BlogPost::query()
            ->published()
            ->forLocale($locale)
            ->whereKeyNot($post->getKey())
            ->when($post->blog_category_id, fn (Builder $q) => $q->where('blog_category_id', $post->blog_category_id))
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();

        // Fall back to the newest same-locale posts so the footer of an only-child
        // post in a category is not empty.
        if ($related->count() < 2) {
            $related = $related->concat(
                BlogPost::query()
                    ->published()
                    ->forLocale($locale)
                    ->whereKeyNot($post->getKey())
                    ->whereNotIn('id', $related->pluck('id'))
                    ->with('category')
                    ->orderByDesc('published_at')
                    ->limit(2 - $related->count())
                    ->get()
            );
        }

        // Reciprocal hreflang cluster (self + published siblings + x-default).
        // Empty when the post has no published translation sibling.
        $hreflang = $this->hreflangCluster($post);

        // SELF-referencing canonical on every language version. en and ur-Latn
        // are alternates, never duplicates, so NEITHER canonicalises to the other.
        $canonical = $this->canonical();
        $title = $post->seoMeta?->meta_title ?: $post->title;
        $description = $post->resolvedMetaDescription();

        $blogBase = url($this->localePrefix($locale).'/blog');

        $crumbs = array_values(array_filter([
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Journal', 'url' => $blogBase],
            $post->category ? ['name' => $post->category->name, 'url' => $blogBase.'/category/'.$post->category->slug] : null,
            ['name' => $post->title],
        ]));

        // Distinct inLanguage per locale keeps the two versions from reading as
        // duplicates in structured data; author/publisher stay identical across
        // the cluster (publisher is the shared #organization node).
        $inLanguage = $this->schemaLanguage($locale);

        return view('blog.show', [
            'contentLocale' => $locale,
            'hreflang' => $hreflang,
            ...$this->layoutData('journal'),
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => $description ? str($description)->limit(158)->toString() : null,
            'canonical' => $canonical,
            'post' => $post,
            'related' => $related,
            'readingTime' => $this->readingTime($post),
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'WebPage', [
                    'inLanguage' => $inLanguage,
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                ]),
                JsonLd::breadcrumbs($canonical, $crumbs),
                JsonLd::blogPosting($canonical, [
                    'inLanguage' => $inLanguage,
                    'headline' => str($post->title)->limit(110, '')->toString(),
                    'description' => $description,
                    'articleSection' => $post->category?->name,
                    'keywords' => $post->tags->pluck('name')->all(),
                    'datePublished' => $post->published_at?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    // No author node unless a real user record is attached. A
                    // "Glow Halal Team" byline is explicitly a negative signal
                    // for this kind of content (SEO §5.4).
                    'author' => $post->author
                        ? ['@type' => 'Person', 'name' => $post->author->name]
                        : null,
                ]),
            ]),
        ]);
    }

    /** The current content locale: 'ur-Latn' under /ur-roman, 'en' everywhere else. */
    private function contentLocale(): string
    {
        return app()->getLocale() === 'ur-Latn' ? 'ur-Latn' : 'en';
    }

    /** URL prefix for a locale: '' for English at the root, '/ur-roman' for Roman Urdu. */
    private function localePrefix(?string $locale = null): string
    {
        return ($locale ?? $this->contentLocale()) === 'ur-Latn' ? '/ur-roman' : '';
    }

    /** Absolute public URL of a post in ITS OWN locale (not the request locale). */
    private function postPublicUrl(BlogPost $post): string
    {
        return url($this->localePrefix($post->locale).'/blog/'.$post->slug);
    }

    /** BCP-47 language tag for JSON-LD inLanguage — distinct per content locale. */
    private function schemaLanguage(string $locale): string
    {
        return $locale === 'ur-Latn' ? 'ur-Latn' : 'en';
    }

    /**
     * Reciprocal hreflang cluster for a post: self + every PUBLISHED translation
     * sibling, plus an x-default that points at the English member. Returns an
     * empty array when the post has no published sibling, so the head emits only
     * a self-canonical and never a broken/one-sided hreflang.
     *
     * @return array<int, array{hreflang: string, href: string}>
     */
    private function hreflangCluster(BlogPost $post): array
    {
        $siblings = $post->translations();

        if ($siblings->isEmpty()) {
            return [];
        }

        $members = collect([$post])->concat($siblings);

        $links = $members
            ->map(fn (BlogPost $m) => [
                'hreflang' => $m->locale === 'ur-Latn' ? 'ur-Latn' : 'en',
                'href' => $this->postPublicUrl($m),
            ])
            ->values();

        // x-default is the English URL when the cluster has one (SEO convention),
        // otherwise the current post so the tag is never dangling.
        $english = $members->firstWhere('locale', 'en');
        $links->push([
            'hreflang' => 'x-default',
            'href' => $this->postPublicUrl($english ?? $post),
        ]);

        return $links->all();
    }

    private function postItemList(string $canonical, LengthAwarePaginator $posts): ?array
    {
        if ($posts->isEmpty()) {
            return null;
        }

        return JsonLd::itemList(
            $canonical,
            'Glow Halal Journal',
            // Locale-correct URLs: a Roman-Urdu post lives under /ur-roman —
            // pointing its English path here fed Google five 404s per crawl.
            $posts->getCollection()->map(fn (BlogPost $p) => url($this->localePrefix($p->locale).'/blog/'.$p->slug))->all(),
            ($posts->currentPage() - 1) * self::PER_PAGE + 1,
        );
    }

    private function readingTime(BlogPost $post): int
    {
        if ($post->reading_time_minutes) {
            return $post->reading_time_minutes;
        }

        $words = str_word_count(strip_tags((string) $post->content));

        return max(1, (int) ceil($words / 200));
    }
}
