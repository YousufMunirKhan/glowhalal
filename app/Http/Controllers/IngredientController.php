<?php

namespace App\Http\Controllers;

use App\Enums\HalalStatus;
use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\Ingredient;
use App\Models\Product;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * `/halal-ingredients` and `/halal-ingredients/{slug}` — the Ingredient Index.
 *
 * SEO §5.3 calls this the moat, and §8 is the reason it is a plain controller
 * with a GET form rather than a Livewire component: search, letter jump and
 * pagination all live in the query string and all render server-side. A
 * crawler and a phone on a dropped connection get the same HTML. A
 * `wire:model.live` filter may be layered on later as an enhancement, but the
 * `?q=` form has to keep working underneath it.
 *
 * The index lists EVERY reference entry. `/halal-ingredients/{slug}` renders
 * only rows with a published glossary page (`has_glossary_page` + published
 * status); everything else 404s, because a stub page with three fields on it
 * is a thin-content liability, not a page.
 */
class IngredientController extends Controller
{
    use BuildsStorefrontLayout;

    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $letter = strtoupper(substr((string) $request->query('letter', ''), 0, 1));
        $letter = preg_match('/^[A-Z]$/', $letter) ? $letter : '';
        $status = (string) $request->query('status', '');
        $status = HalalStatus::tryFrom($status)?->value ?? '';

        $query = Ingredient::query()->orderBy('name');

        if ($q !== '') {
            // LIKE rather than the fulltext index: the index is on (name,
            // description) only, and a shopper types the INCI string off the
            // pack — "CI 75470" — which lives in inci_name and aliases.
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';

            $query->where(fn ($sub) => $sub
                ->where('name', 'like', $like)
                ->orWhere('inci_name', 'like', $like)
                ->orWhere('aliases', 'like', $like)
                ->orWhere('description', 'like', $like));
        }

        if ($letter !== '') {
            $query->where('name', 'like', $letter.'%');
        }

        if ($status !== '') {
            $query->where('halal_status', $status);
        }

        $ingredients = $query
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $totalCount = Ingredient::query()->count();

        // Letters that actually have entries — a jump row of 26 links to 21
        // empty result sets is a crawl trap and a dead end for a reader.
        $activeLetters = Ingredient::query()
            ->selectRaw('UPPER(LEFT(name, 1)) as letter')
            ->distinct()
            ->pluck('letter')
            ->filter(fn ($l) => preg_match('/^[A-Z]$/', (string) $l))
            ->all();

        $canonical = $this->paginatedCanonical($ingredients->currentPage());
        $page = $ingredients->currentPage();

        $title = $page > 1
            ? 'Halal Cosmetic Ingredients A-Z - Page '.$page
            : 'Halal Cosmetic Ingredients A-Z: What Is It Made From?';

        $description = 'Look up any cosmetic ingredient by its INCI name and read what it is made from, '
            .'where it comes from, and whether Glow Halal will formulate with it.'
            .($page > 1 ? ' Page '.$page.' of '.$ingredients->lastPage().'.' : '');

        // A filtered or searched view is a slice of the index, not a page of
        // its own: canonical back to the clean URL and keep it out of the index.
        $isFiltered = $q !== '' || $letter !== '' || $status !== '';
        $robots = $isFiltered ? 'noindex,follow' : 'index,follow';

        if ($isFiltered) {
            $canonical = url('/halal-ingredients');
        }

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Ingredient Index'],
        ];

        return view('ingredients.index', [
            ...$this->layoutData('ingredient-index'),
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'ingredients' => $ingredients,
            'totalCount' => $totalCount,
            'activeLetters' => $activeLetters,
            'q' => $q,
            'letter' => $letter,
            'status' => $status,
            'isFiltered' => $isFiltered,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'CollectionPage'),
                JsonLd::breadcrumbs($canonical, $crumbs),
                JsonLd::definedTermSet(url('/halal-ingredients'), $totalCount),
                JsonLd::itemList(
                    $canonical,
                    'Cosmetic ingredient index',
                    $ingredients->getCollection()
                        ->filter(fn (Ingredient $i) => $this->hasPage($i))
                        ->map(fn (Ingredient $i) => url('/halal-ingredients/'.$i->slug))
                        ->values()
                        ->all(),
                    ($ingredients->currentPage() - 1) * self::PER_PAGE + 1,
                ),
            ]),
        ]);
    }

    public function show(string $slug): View
    {
        /** @var Ingredient $ingredient */
        $ingredient = Ingredient::query()
            ->published()
            ->where('slug', $slug)
            ->with(['related' => fn ($q) => $q->orderBy('name')])
            ->firstOrFail();

        // SEO §3.5 rule 3 — the commercial handoff, query-driven so it is
        // correct at 5 products and at 5,000. "Products made without X" means
        // products that do not have X on their ingredient list.
        $productsWithout = Product::query()
            ->published()
            ->whereDoesntHave('ingredients', fn ($q) => $q->where('ingredients.id', $ingredient->id))
            ->orderBy('name')
            ->limit(4)
            ->get(['id', 'name', 'slug', 'short_description']);

        $canonical = $this->canonical();
        $title = 'Is '.$ingredient->name.' Halal? What It Is, Sources & Alternatives';
        // verdict_summary (the explicit halal/haram ruling) is withheld for now —
        // no ruling is surfaced until it can cite a fatwa or a recognised
        // standard. The meta description leads with the neutral description.
        $description = $ingredient->description
            ?: $ingredient->name.' — what it is made from, how it is listed on an INCI panel, and whether Glow Halal formulates with it.';

        $faqs = $this->faqsFor($ingredient);

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Ingredient Index', 'url' => url('/halal-ingredients')],
            ['name' => $ingredient->name],
        ];

        $aliases = collect($ingredient->aliases ?? [])->filter()->values();

        return view('ingredients.show', [
            ...$this->layoutData('ingredient-index'),
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => str($description)->limit(158)->toString(),
            'canonical' => $canonical,
            'ingredient' => $ingredient,
            'aliases' => $aliases,
            'productsWithout' => $productsWithout,
            'faqs' => $faqs,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'WebPage', [
                    'datePublished' => $ingredient->published_at?->toIso8601String(),
                    'dateModified' => $ingredient->updated_at?->toIso8601String(),
                ]),
                JsonLd::breadcrumbs($canonical, $crumbs),
                JsonLd::article($canonical, [
                    'headline' => str($title)->limit(110, '')->toString(),
                    'description' => $description,
                    'about' => ['@id' => $canonical.'#term'],
                    'datePublished' => $ingredient->published_at?->toIso8601String(),
                    'dateModified' => $ingredient->updated_at?->toIso8601String(),
                ]),
                JsonLd::definedTerm($canonical, [
                    'name' => $ingredient->name,
                    'alternateName' => collect([$ingredient->inci_name])->merge($aliases)->filter()->unique()->values()->all(),
                    'description' => $ingredient->description,
                    'termCode' => $ingredient->inci_name,
                ]),
                JsonLd::faqPage($canonical, $faqs),
            ]),
        ]);
    }

    private function hasPage(Ingredient $ingredient): bool
    {
        return $ingredient->has_glossary_page
            && $ingredient->status === \App\Enums\PostStatus::Published
            && ($ingredient->published_at === null || $ingredient->published_at->isPast());
    }

    /**
     * FAQ entries are built from fields that are ALSO rendered on the page, so
     * the markup can never describe an answer the reader cannot see.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function faqsFor(Ingredient $ingredient): array
    {
        $faqs = [];

        // The "Is X halal?" FAQ entry (answer = verdict_summary) is withheld for
        // now: no halal/haram ruling is published as structured data until it can
        // cite a fatwa or a recognised standard, and it is no longer shown on the
        // page (FAQ answers must match visible content).
        // The "Why is X treated that way?" FAQ (answer = halal_notes) is
        // withheld: that is unsourced religious reasoning and is no longer shown
        // on the page, so it must not appear in structured data either.

        $labelNames = collect([$ingredient->inci_name])
            ->merge($ingredient->aliases ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($labelNames->isNotEmpty()) {
            $faqs[] = [
                'question' => 'How does '.$ingredient->name.' appear on an ingredient list?',
                'answer' => 'On an INCI panel it is printed as '.$labelNames->join(', ', ' or ').'.',
            ];
        }

        return $faqs;
    }
}
