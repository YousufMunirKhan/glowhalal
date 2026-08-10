<?php

namespace App\Http\Controllers;

use App\Enums\HalalStatus;
use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\Ingredient;
use App\Models\Page;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;

/**
 * `/about`.
 *
 * Content comes from the `pages` row with slug `about` when the owner has
 * written one; the template renders that body inside the page furniture. Until
 * then the page still has to exist and still has to be worth landing on, so it
 * renders the parts that are true without anyone having to write them: what we
 * publish, and the exclusion list, each row linking into
 * /halal-ingredients/{slug}. That section alone is what earns this page its
 * place in the internal link graph (SEO §6.1).
 *
 * The founder block, the company registration block and the address block are
 * rendered as explicit "not published yet" states driven by StoreSettings. They
 * are NOT filled with plausible values. A previous pass invented a founder
 * named "Ayesha Siddiqui" and a Gulberg address; both were false, both had to
 * be removed, and neither may come back through this controller.
 */
class AboutController extends Controller
{
    use BuildsStorefrontLayout;

    public function __invoke(): View
    {
        $page = Page::query()->published()->where('slug', 'about')->first();

        $excluded = Ingredient::query()
            ->whereIn('halal_status', [HalalStatus::Haram, HalalStatus::Mashbooh])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'inci_name', 'halal_status', 'has_glossary_page', 'status', 'published_at']);

        $canonical = $this->canonical();
        $title = $page?->seoMeta?->meta_title ?: 'About Glow Halal - Cosmetics With Every Ingredient Named';
        $description = $page?->resolvedMetaDescription()
            ?: 'Glow Halal publishes the full INCI list for every product it sells and the full list of '
              .'ingredients it will not formulate with. No certificate, no seal — the evidence itself.';

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'About'],
        ];

        return view('pages.about', [
            ...$this->layoutData('about'),
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => str($description)->limit(158)->toString(),
            'canonical' => $canonical,
            'page' => $page,
            'store' => $this->storeSettings(),
            'excluded' => $excluded,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description, 'AboutPage', [
                    'dateModified' => $page?->updated_at?->toIso8601String(),
                ]),
                JsonLd::breadcrumbs($canonical, $crumbs),
            ]),
        ]);
    }

    private function storeSettings(): \App\Settings\StoreSettings
    {
        return app(\App\Settings\StoreSettings::class);
    }
}
