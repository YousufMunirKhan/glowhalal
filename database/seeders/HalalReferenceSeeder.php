<?php

namespace Database\Seeders;

use App\Enums\HalalStatus;
use App\Enums\IngredientOrigin;
use App\Enums\PostStatus;
use App\Models\CertificationBody;
use App\Models\FreeFromAttribute;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

/**
 * Reference data for the halal model.
 *
 * NOTE ON CERTIFICATION BODIES (§2.1): the architecture is explicit that only
 * real, client-verified bodies should be seeded, because publishing a
 * certifier's name incorrectly is a legal exposure. The two rows below are
 * marked `is_recognised = false` and exist purely so the admin UI is not empty.
 * Replace them with client-confirmed bodies before launch.
 */
class HalalReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->freeFromAttributes();
        $this->certificationBodies();
        $this->ingredients();
    }

    private function freeFromAttributes(): void
    {
        $claims = [
            ['alcohol_free', 'Alcohol-Free', 'Contains no ethanol or denatured alcohol. Fatty alcohols such as cetyl and cetearyl are emollients, not intoxicants, and do not affect this claim.'],
            ['carmine_free', 'Carmine-Free', 'No CI 75470 — the crimson pigment rendered from cochineal insects, ubiquitous in reds and pinks.'],
            ['gelatin_free', 'Gelatin-Free', 'No porcine or bovine gelatin in capsules, gels or setting agents.'],
            ['pork_derivative_free', 'Pork Derivative-Free', 'Umbrella claim covering tallow, porcine collagen and pork-derived stearates.'],
            ['animal_derived_free', 'No Animal-Derived Ingredients', 'Stricter than most vegan-adjacent claims; derived from a full INCI review.'],
            ['wudu_friendly', 'Wudu-Friendly', 'Water-permeable formulation that permits ablution without removal.'],
            ['paraben_free', 'Paraben-Free', 'No methyl-, ethyl-, propyl- or butylparaben.'],
            ['sulfate_free', 'Sulfate-Free', 'No SLS or SLES.'],
            ['cruelty_free', 'Cruelty-Free', 'No animal testing at any stage.'],
            ['vegan', 'Vegan', 'No animal-derived inputs at all.'],
            ['fragrance_free', 'Fragrance-Free', 'Fragrance compounds can carry undeclared alcohol carriers.'],
            ['shellac_free', 'Shellac-Free', 'No lac-insect resin, common in mascara and nail products.'],
        ];

        foreach ($claims as $i => [$code, $name, $description]) {
            FreeFromAttribute::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'slug' => str($name)->slug()->toString(),
                    'short_description' => str($description)->limit(150)->toString(),
                    'description' => $description,
                    'is_filterable' => true,
                    'requires_verification' => true,
                    'position' => $i * 10,
                ],
            );
        }
    }

    private function certificationBodies(): void
    {
        $bodies = [
            [
                'name' => 'Example Halal Certification Body A',
                'short_name' => 'EHCB-A',
                'country_code' => 'PK',
                'description' => 'PLACEHOLDER. Replace with a client-confirmed certifier before launch — see §2.1.',
            ],
            [
                'name' => 'Example Halal Certification Body B',
                'short_name' => 'EHCB-B',
                'country_code' => 'PK',
                'description' => 'PLACEHOLDER. Replace with a client-confirmed certifier before launch — see §2.1.',
            ],
        ];

        foreach ($bodies as $i => $body) {
            CertificationBody::firstOrCreate(
                ['slug' => str($body['name'])->slug()->toString()],
                [
                    ...$body,
                    'is_recognised' => false,   // listed for transparency, not presented as an authority
                    'is_active' => true,
                    'position' => $i * 10,
                ],
            );
        }
    }

    private function ingredients(): void
    {
        $ingredients = [
            [
                'name' => 'Carmine',
                'inci_name' => 'CI 75470',
                'aliases' => ['Cochineal', 'Crimson Lake', 'Natural Red 4', 'Carminic Acid'],
                'origin' => IngredientOrigin::Animal,
                'halal_status' => HalalStatus::Haram,
                'halal_notes' => 'Carmine is extracted from the dried bodies of the female cochineal insect (Dactylopius coccus). The majority position across Pakistani and Gulf certifying bodies treats insect-derived colourants as impermissible for cosmetic use, and it is the single most common reason a red or pink shade fails halal review.',
                'is_animal_derived' => true,
                'is_key_ingredient_candidate' => false,
                'function' => 'colourant',
                'description' => 'A crimson pigment rendered from cochineal insects, used across lipsticks, blushes and cream products.',
                'verdict_summary' => 'Carmine is not halal — it is derived from insects, and every shade containing it is excluded from this catalogue.',
                'content' => '<h2>What carmine actually is</h2><p>Carmine is not a plant pigment, despite frequently appearing under the reassuring label "natural red". It is produced by drying and crushing the female cochineal insect, then extracting carminic acid and precipitating it onto an aluminium salt. Roughly 70,000 insects yield a single pound of dye.</p><h2>Why it appears in so many products</h2><p>It is exceptionally stable. Carmine does not fade under UV the way most botanical reds do, which is why it survived into modern cosmetic chemistry despite the cost. If you are looking at a long-wear red lipstick from a mainstream brand, carmine is the default assumption until the INCI list says otherwise.</p><h2>How to spot it on a label</h2><p>Look for <strong>CI 75470</strong>, <strong>Carmine</strong>, <strong>Cochineal Extract</strong>, or <strong>Carminic Acid</strong>. "Natural Red 4" is the same substance. A product listing none of these, and no other animal-derived input, can carry a carmine-free claim.</p><h2>What we use instead</h2><p>Iron oxides (CI 77491) and synthetic organic pigments reach the same depth of red without an animal source. They require more careful formulation to match carmine\'s clarity, which is the actual reason many brands never switch.</p>',
                'has_glossary_page' => true,
                'status' => PostStatus::Published,
            ],
            [
                'name' => 'Glycerin',
                'inci_name' => 'Glycerin',
                'aliases' => ['Glycerol', 'Glycerine'],
                'origin' => IngredientOrigin::Unknown,
                'halal_status' => HalalStatus::DependsOnSource,
                'halal_notes' => 'Glycerin is chemically identical regardless of feedstock. Plant-derived glycerin (palm, coconut, soy) is halal; tallow-derived glycerin is not. The INCI name cannot distinguish them, so the ruling turns entirely on supplier documentation — which is why every product in this catalogue carries a per-formulation source note.',
                'is_animal_derived' => false,
                'is_key_ingredient_candidate' => true,
                'function' => 'humectant',
                'description' => 'A humectant that draws water into the skin. Halal status depends entirely on whether the feedstock was plant or animal fat.',
                'benefits' => 'Draws moisture into the upper layers of the skin, softens texture, and improves the spreadability of a formulation.',
                'verdict_summary' => 'Glycerin is halal when plant-derived and not halal when tallow-derived — the label alone cannot tell you which, so ask for the source.',
                'content' => '<h2>The same molecule, two very different sources</h2><p>Glycerin is a simple three-carbon alcohol. It can be produced by splitting vegetable oils — palm, coconut, soy — or by rendering animal fat. The finished molecule is indistinguishable in both cases, and INCI labelling gives you exactly one word for both: <strong>Glycerin</strong>.</p><h2>Why this matters more than almost any other ingredient</h2><p>Glycerin appears in a very large share of cleansers, moisturisers, serums and lip products. If you are trying to buy halal cosmetics by reading labels alone, glycerin is where that method fails. There is no spelling, no CI number, no asterisk that distinguishes plant glycerin from tallow glycerin.</p><h2>What resolves it</h2><p>Only supplier documentation. A brand that cannot tell you the feedstock of its glycerin has not asked. We record the source for every formulation that contains it, and where the supplier cannot evidence a plant origin, the product does not go on sale.</p><h2>Related ingredients with the same problem</h2><p>Stearic acid, magnesium stearate, mono- and diglycerides, and collagen sit in exactly the same position: halal or not depending entirely on feedstock, with no way to tell from the label.</p>',
                'has_glossary_page' => true,
                'status' => PostStatus::Published,
            ],
            // Supporting rows: real ingredients that make product INCI lists complete,
            // deliberately without glossary pages (§2.7 — do not publish thin pages).
            [
                'name' => 'Cetearyl Alcohol',
                'inci_name' => 'Cetearyl Alcohol',
                'origin' => IngredientOrigin::Plant,
                'halal_status' => HalalStatus::Halal,
                'halal_notes' => 'A fatty alcohol, not an intoxicant. Waxy, non-volatile, and broadly accepted — the naive "contains alcohol" reading of an INCI list is what wrongly flags it.',
                'function' => 'emollient',
                'description' => 'A waxy fatty alcohol used to thicken and soften emulsions.',
            ],
            [
                'name' => 'Niacinamide',
                'inci_name' => 'Niacinamide',
                'origin' => IngredientOrigin::Synthetic,
                'halal_status' => HalalStatus::Halal,
                'is_key_ingredient_candidate' => true,
                'function' => 'active',
                'description' => 'Vitamin B3 derivative. Supports the skin barrier and evens tone.',
                'benefits' => 'Reduces the appearance of pores, improves barrier function, and evens out pigmentation over time.',
            ],
            [
                'name' => 'Iron Oxides',
                'inci_name' => 'CI 77491, CI 77492, CI 77499',
                'origin' => IngredientOrigin::Mineral,
                'halal_status' => HalalStatus::Halal,
                'function' => 'colourant',
                'description' => 'Mineral pigments providing red, yellow and black tones. The standard carmine-free route to a true red.',
            ],
            [
                'name' => 'Shea Butter',
                'inci_name' => 'Butyrospermum Parkii Butter',
                'origin' => IngredientOrigin::Plant,
                'halal_status' => HalalStatus::Halal,
                'is_key_ingredient_candidate' => true,
                'function' => 'emollient',
                'description' => 'Rich plant butter pressed from the nut of the shea tree.',
                'benefits' => 'Occlusive and softening; particularly effective on dry lips and cuticles.',
            ],
            [
                'name' => 'Squalane',
                'inci_name' => 'Squalane',
                'origin' => IngredientOrigin::Plant,
                'halal_status' => HalalStatus::DependsOnSource,
                'halal_notes' => 'Historically shark-liver derived. Olive- and sugarcane-derived squalane is now standard and is halal, but the source must be evidenced rather than assumed.',
                'is_key_ingredient_candidate' => true,
                'function' => 'emollient',
                'description' => 'A lightweight emollient. Modern cosmetic squalane is usually olive- or sugarcane-derived.',
            ],
            // The exclusion list proper — the classic haram/mashbooh cosmetic
            // inputs this brand commits never to stock. Real reference data
            // (moved from the demo seeder so production carries the full list).
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

        foreach ($ingredients as $data) {
            $slug = str($data['name'])->slug()->toString();

            $ingredient = Ingredient::firstOrCreate(
                ['slug' => $slug],
                [
                    'has_glossary_page' => false,
                    'status' => PostStatus::Draft,
                    ...$data,
                    'published_at' => ($data['status'] ?? null) === PostStatus::Published ? now()->subDays(random_int(3, 40)) : null,
                    'reviewed_by_user_id' => ($data['has_glossary_page'] ?? false) ? 1 : null,
                    'reviewed_at' => ($data['has_glossary_page'] ?? false) ? now()->subDays(2) : null,
                    'reading_time_minutes' => ($data['has_glossary_page'] ?? false) ? 3 : null,
                    'author_id' => 1,
                ],
            );

            $ingredient->seoMeta()->firstOrCreate([], [
                'meta_title' => "Is {$ingredient->name} halal?",
                'meta_description' => $ingredient->verdict_summary
                    ?? str($ingredient->description ?? '')->limit(155)->toString(),
                'og_type' => 'article',
                'is_indexable' => (bool) $ingredient->has_glossary_page,
                'is_followable' => true,
            ]);
        }

        // "See also" cluster links — §2.7.
        $carmine = Ingredient::where('slug', 'carmine')->first();
        $glycerin = Ingredient::where('slug', 'glycerin')->first();
        $ironOxides = Ingredient::where('slug', 'iron-oxides')->first();
        $squalane = Ingredient::where('slug', 'squalane')->first();

        if ($carmine && $ironOxides) {
            $carmine->related()->syncWithoutDetaching([$ironOxides->id => ['position' => 0]]);
        }

        if ($glycerin && $squalane) {
            $glycerin->related()->syncWithoutDetaching([$squalane->id => ['position' => 0]]);
        }
    }
}
