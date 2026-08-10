<?php

namespace Database\Seeders;

use App\Enums\HalalStatus;
use App\Enums\IngredientOrigin;
use App\Enums\PostStatus;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

/**
 * Reference rows for the ingredient index and the `/what-we-never-use` table.
 *
 * DatabaseSeeder already reserves a hook for this class ("Content seeder is
 * owned by the content workstream; call it if present"), so it runs as part of
 * a normal `db:seed` and is idempotent — `firstOrCreate` on the slug, so an
 * admin's later edits in Filament are never overwritten.
 *
 * ---------------------------------------------------------------------------
 * WHAT THIS FILE IS ALLOWED TO CONTAIN
 * ---------------------------------------------------------------------------
 *
 * Verifiable facts about cosmetic ingredients: what a substance is, what it is
 * made from, which INCI and CI names it hides behind, and — stated as the
 * position of certifying bodies rather than as a ruling of our own — why it is
 * treated as permissible or not.
 *
 * WHAT IT MUST NEVER CONTAIN: a halal certificate, a certifying body's
 * endorsement of Glow Halal, a standard number presented as ours, a named
 * scholar or reviewer who has not actually reviewed the copy, a founder, an
 * address, or a testimonial. Glow Halal holds no halal accreditation. The only
 * claims this brand makes are (a) it publishes every ingredient in full, and
 * (b) it does not formulate with the ingredients listed below.
 *
 * Religious language is deliberately hedged ("the majority position among
 * certifying bodies…") because manufactured certainty on a contested question
 * is exactly what destroys trust with the audience this site is built for.
 *
 * `reviewed_by_user_id` and `reviewed_at` are left NULL on every row. There is
 * no credentialed reviewer on this project yet, and the ingredient template
 * therefore renders no reviewer line rather than a fabricated one.
 */
class ContentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->ingredients();
        $this->relatedLinks();
    }

    private function ingredients(): void
    {
        foreach ($this->rows() as $row) {
            $related = $row['related'] ?? [];
            unset($row['related']);

            Ingredient::firstOrCreate(
                ['slug' => str($row['name'])->slug()->toString()],
                [
                    ...$row,
                    'slug' => str($row['name'])->slug()->toString(),
                    'status' => $row['status'] ?? PostStatus::Draft,
                    'has_glossary_page' => $row['has_glossary_page'] ?? false,
                ],
            );
        }
    }

    /**
     * "See also" edges. Also drives the "What we use instead" column of the
     * exclusion table, which only ever lists an ingredient that is itself
     * recorded as halal — so the column cannot quietly recommend something
     * doubtful.
     */
    private function relatedLinks(): void
    {
        $edges = [
            'carmine' => ['iron-oxides'],
            'denatured-alcohol' => ['glycerin'],
            'lanolin' => ['shea-butter', 'squalane'],
            'tallow' => ['shea-butter', 'glycerin'],
            'hydrolyzed-collagen' => ['niacinamide'],
            'hydrolyzed-keratin' => ['niacinamide'],
            'squalane' => ['shea-butter'],
        ];

        foreach ($edges as $slug => $relatedSlugs) {
            $ingredient = Ingredient::where('slug', $slug)->first();

            if (! $ingredient) {
                continue;
            }

            $ids = Ingredient::whereIn('slug', $relatedSlugs)->pluck('id', 'slug');

            $sync = [];
            foreach (array_values($relatedSlugs) as $position => $relatedSlug) {
                if (isset($ids[$relatedSlug])) {
                    $sync[$ids[$relatedSlug]] = ['position' => $position];
                }
            }

            $ingredient->related()->syncWithoutDetaching($sync);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(): array
    {
        return [
            [
                'name' => 'Denatured Alcohol',
                'inci_name' => 'Alcohol Denat.',
                'aliases' => ['SD Alcohol 40', 'SD Alcohol 40-B', 'Ethanol', 'Ethyl Alcohol', 'Alcohol'],
                'origin' => IngredientOrigin::Synthetic,
                'halal_status' => HalalStatus::Haram,
                'is_alcohol' => true,
                'function' => 'solvent',
                'description' => 'Ethanol made deliberately undrinkable with a bitterant, used as a fast-evaporating solvent in toners, setting sprays and gel textures.',
                'halal_notes' => 'This is the intoxicating alcohol, not a fatty alcohol. Denaturing changes the taste, not the chemistry, and the majority position among Pakistani and Gulf certifying bodies is that ethanol-based cosmetic solvents are impermissible. It is the single most common reason a mainstream toner or setting spray fails halal review, and it is frequently the only excluded ingredient in an otherwise acceptable formula. Fatty alcohols — cetyl, cetearyl, stearyl — are waxes and are not affected by this ruling; the naive "contains alcohol" reading of an INCI list is what wrongly flags them.',
                'verdict_summary' => 'Denatured alcohol is not halal — it is intoxicating ethanol, and no product in this catalogue contains it.',
                'content' => '<h2>What denatured alcohol actually is</h2><p>Alcohol Denat. is ordinary ethanol with a small quantity of a bittering agent added so that it cannot be drunk and is therefore not taxed as a beverage. Denaturing is a fiscal and safety measure. It does not alter the molecule, and the substance in the bottle is chemically the same ethanol found in any other alcoholic preparation.</p><h2>Why formulators reach for it</h2><p>It evaporates fast, it dissolves things water will not, and it leaves a cool, weightless finish. That combination is very hard to reproduce, which is why it survives in toners, setting sprays, gel-textured moisturisers and most fragranced products.</p><h2>How to spot it on a label</h2><p>Look for <strong>Alcohol Denat.</strong>, <strong>SD Alcohol 40</strong>, <strong>SD Alcohol 40-B</strong>, <strong>Ethanol</strong>, or plain <strong>Alcohol</strong>. Position matters: near the top of an INCI list it is a primary solvent and will be present at a high percentage.</p><h2>The alcohols that are not this</h2><p><strong>Cetyl Alcohol</strong>, <strong>Cetearyl Alcohol</strong> and <strong>Stearyl Alcohol</strong> are fatty alcohols — waxy, non-volatile emollients with no intoxicating property. They share a word, not a chemistry. Excluding them costs you a great many perfectly acceptable products for no reason.</p><h2>What we use instead</h2><p>Plant-derived glycerin and glycol-based solvent systems carry actives without ethanol. They feel heavier on the skin, which is a genuine formulation trade-off rather than a marketing one, and it is the reason so few brands make the switch.</p>',
                'status' => PostStatus::Published,
                'published_at' => now(),
                'has_glossary_page' => true,
                'is_animal_derived' => false,
                'reading_time_minutes' => 4,
            ],
            [
                'name' => 'Gelatin',
                'inci_name' => 'Gelatin',
                'aliases' => ['Gelatine', 'Hydrolyzed Gelatin'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Mashbooh,
                'is_animal_derived' => true,
                'function' => 'gelling agent',
                'description' => 'A protein obtained by boiling animal skin, bone and connective tissue, used as a gelling and film-forming agent.',
                'halal_notes' => 'Gelatin is most often porcine, which is categorically excluded. Bovine gelatin can be acceptable where the animal was slaughtered according to Islamic requirements, but that provenance is almost never documented down to the cosmetic-grade supplier. Because the INCI name is identical in every case, a gelatin-containing product cannot be evidenced as acceptable from its label alone — which is why it is excluded here rather than assessed case by case.',
            ],
            [
                'name' => 'Lanolin',
                'inci_name' => 'Lanolin',
                'aliases' => ['Lanolin Alcohol', 'PEG-75 Lanolin', 'Wool Wax', 'Wool Grease'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Mashbooh,
                'is_animal_derived' => true,
                'is_common_allergen' => true,
                'function' => 'emollient',
                'description' => 'A waxy grease recovered from sheep wool, used as an occlusive emollient in balms and heavy creams.',
                'halal_notes' => 'Lanolin is taken from the fleece of a living animal, and several certifying bodies treat it as permissible on that basis. Others do not, and the extraction and refining chain is rarely documented. Glow Halal publishes a no-animal-derived-ingredients claim across the catalogue, so lanolin is excluded on that ground rather than on a ruling of our own. It is also a well-known contact allergen.',
            ],
            [
                'name' => 'Tallow',
                'inci_name' => 'Sodium Tallowate',
                'aliases' => ['Tallow Acid', 'Tallow Glycerides', 'Sodium Tallowate', 'Glycerin (tallow-derived)'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Haram,
                'is_animal_derived' => true,
                'function' => 'surfactant',
                'description' => 'Rendered animal fat, and the feedstock behind a large share of the world\'s soap bases, stearates and cheap glycerin.',
                'halal_notes' => 'Rendered fat may be porcine, and where it is bovine the slaughter method is effectively never evidenced for a cosmetic-grade input. It is the reason plant sourcing has to be documented for glycerin, stearic acid and the whole stearate family rather than assumed — the INCI name for the plant-derived and the tallow-derived versions of those materials is identical.',
            ],
            [
                'name' => 'Hydrolyzed Keratin',
                'inci_name' => 'Hydrolyzed Keratin',
                'aliases' => ['Keratin', 'Keratin Amino Acids'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Mashbooh,
                'is_animal_derived' => true,
                'function' => 'conditioning agent',
                'description' => 'A protein fragment obtained from wool, horn, hoof or feathers, used to condition hair and the skin barrier.',
                'halal_notes' => 'Keratin is recovered from slaughterhouse by-products, and the species and slaughter method are not disclosed at ingredient level. Some suppliers now offer a wool-only route, but the INCI name does not distinguish it. Excluded here under the same no-animal-derived-ingredients claim that applies across the catalogue.',
            ],
            [
                'name' => 'Hydrolyzed Collagen',
                'inci_name' => 'Hydrolyzed Collagen',
                'aliases' => ['Collagen', 'Soluble Collagen', 'Marine Collagen'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Mashbooh,
                'is_animal_derived' => true,
                'function' => 'conditioning agent',
                'description' => 'A protein taken from the hide, bone or scales of animals and fish, used as a film-forming humectant.',
                'halal_notes' => 'Bovine and porcine collagen carry the same provenance problem as gelatin, which is the same protein at a different stage of processing. Marine collagen avoids that objection but is still an animal-derived input, and the INCI name rarely states the source. Excluded on the catalogue-wide animal-derived claim.',
            ],
            [
                'name' => 'Shellac',
                'inci_name' => 'Shellac',
                'aliases' => ['Lac Resin', 'Shellac Wax', 'CI 75440'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Haram,
                'is_animal_derived' => true,
                'function' => 'film former',
                'description' => 'A resin secreted by the lac insect, used as a glossy film former in mascara, nail products and coatings.',
                'halal_notes' => 'Shellac is an insect secretion, harvested with the insects embedded in the resin. The majority position that excludes insect-derived carmine applies to it on the same reasoning, and it carries the additional problem of being a hard, water-impermeable film — which is the separate objection raised about nail products and wudu.',
            ],
            [
                'name' => 'Guanine',
                'inci_name' => 'CI 75170',
                'aliases' => ['Pearl Essence', 'Fish Scale Extract'],
                'origin' => IngredientOrigin::Marine,
                'halal_status' => HalalStatus::Mashbooh,
                'is_animal_derived' => true,
                'function' => 'colourant',
                'description' => 'A pearlescent pigment obtained from fish scales, used for shimmer in eyeshadows, highlighters and nail products.',
                'halal_notes' => 'Fish is broadly permissible, and several certifying bodies treat guanine as acceptable on that basis. It is nonetheless an animal-derived input, and synthetic mica and borosilicate pearls reach the same finish with a documented source, so it is excluded here rather than relied upon.',
            ],
        ];
    }
}
