<?php

namespace App\Http\Controllers;

use App\Enums\HalalStatus;
use App\Http\Controllers\Concerns\BuildsStorefrontLayout;
use App\Models\FreeFromAttribute;
use App\Models\Ingredient;
use App\Support\JsonLd;
use Illuminate\Contracts\View\View;

/**
 * `/what-we-never-use` — nav item #2, and the brand's entire argument.
 *
 * With no third-party accreditation to point at, this table IS the trust
 * claim. It therefore has to be the real, complete, current list, generated
 * from the `ingredients` table rather than typed into a Blade file, so that an
 * exclusion added in the admin appears here and on the ingredient index at the
 * same moment.
 *
 * Two groups, and the split matters:
 *   - EXCLUDED       haram + mashbooh. We do not formulate with these at all.
 *   - SOURCE-CHECKED depends_on_source + unknown. Chemically identical whether
 *                    the feedstock was a plant or an animal, so the INCI name
 *                    cannot settle it and supplier documentation has to.
 *
 * Collapsing the two into one list would be the dishonest simplification: it
 * would let us print "no glycerin" when what is true is "no tallow glycerin".
 */
class ExclusionController extends Controller
{
    use BuildsStorefrontLayout;

    public function __invoke(): View
    {
        // Only halal alternatives may appear in the "what we use instead"
        // column — the column must not quietly recommend something doubtful.
        $withAlternatives = ['related' => fn ($q) => $q->where('halal_status', HalalStatus::Halal)];

        $excluded = Ingredient::query()
            ->whereIn('halal_status', [HalalStatus::Haram, HalalStatus::Mashbooh])
            ->with($withAlternatives)
            ->orderBy('name')
            ->get();

        $sourceChecked = Ingredient::query()
            ->whereIn('halal_status', [HalalStatus::DependsOnSource, HalalStatus::Unknown])
            ->with($withAlternatives)
            ->orderBy('name')
            ->get();

        $claims = FreeFromAttribute::query()->orderBy('position')->orderBy('name')->get();

        $canonical = $this->canonical();
        $title = 'What We Never Use - The Full Ingredient Exclusion List';
        $description = 'The complete list of ingredients Glow Halal will not formulate with, '
            .'with the INCI name printed on the pack and the reason for each exclusion.';

        // Every answer below is rendered visibly on the page. Nothing is marked
        // up that a reader cannot also read (SEO §4.11).
        $faqs = [
            [
                'question' => 'Does Glow Halal hold a halal certificate?',
                'answer' => 'No. Glow Halal holds no third-party halal accreditation and does not claim one. '
                    .'What we publish instead is the complete INCI list for every product we sell, and the list '
                    .'on this page of the ingredients we will not formulate with. You are being asked to check '
                    .'our work, not to take our word for it.',
            ],
            [
                'question' => 'Is Cetearyl Alcohol the same thing as Alcohol Denat.?',
                'answer' => 'No. Cetearyl, cetyl and stearyl alcohol are fatty alcohols — waxy, non-volatile '
                    .'emollients with no intoxicating property. Alcohol Denat. is ethanol. They share a word and '
                    .'nothing else, and excluding the fatty alcohols costs you a great many acceptable products '
                    .'for no reason.',
            ],
            [
                'question' => 'How do I check a product I already own against this list?',
                'answer' => 'Turn the pack over and read the INCI list — the block of Latin and chemical names, '
                    .'ordered by descending concentration until 1%. Compare it against the "on the label" column '
                    .'here, which gives the exact strings a manufacturer is permitted to print, including the CI '
                    .'colour numbers that hide the animal-derived pigments.',
            ],
        ];

        $crumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'What We Never Use'],
        ];

        return view('pages.what-we-never-use', [
            ...$this->layoutData('never-use'),
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'excluded' => $excluded,
            'sourceChecked' => $sourceChecked,
            'claims' => $claims,
            'faqs' => $faqs,
            'crumbs' => $crumbs,
            'schema' => $this->schema([
                JsonLd::webPage($canonical, $title, $description),
                JsonLd::breadcrumbs($canonical, $crumbs),
                JsonLd::faqPage($canonical, $faqs),
            ]),
        ]);
    }
}
