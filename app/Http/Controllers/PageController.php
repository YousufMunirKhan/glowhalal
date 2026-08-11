<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\Page;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Generic CMS page at `/{slug}` — Privacy, Terms, Shipping & Returns, FAQ and
 * anything else the owner creates in the admin panel.
 *
 * This route is declared LAST in routes/web.php so it can never shadow a real
 * one. `/about`, `/contact`, `/shop`, `/blog` and the rest match first; only an
 * unclaimed single-segment path reaches here.
 *
 * Unpublished pages 404 rather than render, so a draft is never reachable by
 * guessing its slug.
 *
 * Content is authored entirely in the admin. Nothing here invents copy: a page
 * with no body yet renders an honest empty state rather than filler. That rule
 * exists because earlier passes fabricated a founder, an address and a halal
 * certificate, all of which had to be removed.
 */
class PageController extends Controller
{
    use BuildsStorefrontLayout;

    public function __invoke(string $slug): View
    {
        $page = Page::query()->published()->where('slug', $slug)->first();

        if (! $page) {
            throw new NotFoundHttpException("No published page for slug [{$slug}].");
        }

        $canonical = $this->canonical();
        $title = $page->seoMeta?->meta_title ?: $page->title.' | Glow Halal';
        // Fall back to the page's own opening text so no CMS page ever ships
        // without a meta description (a Bing Webmaster error otherwise).
        $description = $page->seoMeta?->meta_description
            ?: str(trim(strip_tags((string) $page->content)))->squish()->limit(155, '…', preserveWords: true)->toString()
            ?: null;

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => $page->title],
        ];

        return view('pages.show', [
            ...$this->layoutData($slug),
            'page' => $page,
            'title' => str($title)->limit(65, '')->trim()->toString(),
            'description' => $description ? str($description)->limit(158)->toString() : null,
            'canonical' => $canonical,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description),
                JsonLd::breadcrumbs($canonical, $crumbs),
            ]),
        ]);
    }
}
