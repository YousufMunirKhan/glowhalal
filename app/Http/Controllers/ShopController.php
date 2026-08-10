<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Seo\SchemaGraph;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The catalogue. Plain server-rendered Blade, deliberately — every product
 * name, price and link is in the initial HTML response with JavaScript
 * disabled, and pagination is real `?page=n` anchors (seo.md §8.1, §8.7,
 * §8.11). The only Livewire on this page is the add-to-bag control, which is a
 * button and carries no indexable content.
 */
class ShopController extends Controller
{
    private const PER_PAGE = 12;

    /** Only these sort keys are honoured; anything else falls back to the default. */
    private const SORTS = ['newest', 'price-asc', 'price-desc'];

    public function index(Request $request): View
    {
        return $this->render($request, null);
    }

    public function category(Request $request, Category $category): View
    {
        return $this->render($request, $category);
    }

    private function render(Request $request, ?Category $category): View
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : null;

        $products = $this->query($category, $sort)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $page = max(1, (int) $request->query('page', 1));
        $isFiltered = $sort !== null;

        $title = $category
            ? "Halal {$category->name} in Pakistan"
            : 'Shop all products';

        $heading = $category ? $category->name : 'Everything we make';

        $intro = $category
            ? ($category->description ?: null)
            : 'Every product below publishes its full INCI ingredient list and the list of ingredients we will not formulate with. Cash on Delivery nationwide.';

        // Category intro copy renders on page 1 only (seo.md §2.3) — repeating
        // it on every page of a paginated set is duplicate content.
        if ($page > 1) {
            $intro = null;
        }

        $metaTitle = $category?->resolvedMetaTitle() ?? $title;
        $metaDescription = $category?->resolvedMetaDescription()
            ?? 'Halal cosmetics from Karachi with the full ingredient list published on every product. Cash on Delivery across Pakistan.';

        if ($page > 1) {
            $metaTitle .= " - Page {$page}";
            $metaDescription = rtrim($metaDescription, '.')." — page {$page} of {$products->lastPage()}.";
        }

        // seo.md §2.5: pagination is self-canonical (never page N -> page 1,
        // which Google reads as a soft-404 and uses to drop deep products).
        // Filtered views canonical to the clean URL and go noindex,follow.
        $baseUrl = $category ? route('shop.category', $category->slug) : route('shop.index');
        $canonical = $isFiltered
            ? $baseUrl
            : ($page > 1 ? $baseUrl.'?page='.$page : $baseUrl);

        $trail = $this->trail($category);

        $graph = SchemaGraph::make()
            ->organization()
            ->collectionPage($canonical, $title, $intro)
            ->breadcrumbs($trail)
            ->itemList(
                $baseUrl,
                $title,
                collect($products->items()),
                ($products->currentPage() - 1) * $products->perPage() + 1,
            );

        return view('shop.index', [
            'category' => $category,
            'categories' => $this->menuCategories(),
            'products' => $products,
            'heading' => $heading,
            'intro' => $intro,
            'sort' => $sort,
            'trail' => $trail,
            'canonical' => $canonical,
            'robots' => $isFiltered ? 'noindex,follow' : 'index,follow',
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'schema' => $graph->toJson(),
        ]);
    }

    private function query(?Category $category, ?string $sort): Builder
    {
        $query = Product::query()
            ->published()
            ->with(['primaryImage', 'defaultVariant', 'verifiedFreeFromAttributes', 'variants' => fn ($q) => $q->where('is_active', true)]);

        if ($category) {
            $query->inCategory($category);
        }

        return match ($sort) {
            'price-asc' => $query->orderBy('price_min_amount')->orderBy('id'),
            'price-desc' => $query->orderByDesc('price_max_amount')->orderBy('id'),
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            default => $query->orderByDesc('is_featured')->orderBy('position')->orderBy('id'),
        };
    }

    /** @return array<int, array{name: string, url: string}> */
    private function trail(?Category $category): array
    {
        $trail = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Shop', 'url' => route('shop.index')],
        ];

        if (! $category) {
            return $trail;
        }

        foreach ($category->ancestorTrail() as $ancestor) {
            $trail[] = ['name' => $ancestor->name, 'url' => route('shop.category', $ancestor->slug)];
        }

        $trail[] = ['name' => $category->name, 'url' => route('shop.category', $category->slug)];

        return $trail;
    }

    /**
     * Category chips. A category with nothing in it is a dead end for a
     * shopper and a soft-404 for a crawler, so empty ones never render.
     */
    private function menuCategories()
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('depth')
            ->orderBy('position')
            ->get()
            ->filter(fn (Category $c) => Product::published()->inCategory($c)->exists())
            ->values();
    }
}
