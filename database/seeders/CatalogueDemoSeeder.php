<?php

namespace Database\Seeders;

use App\Enums\AlcoholStatus;
use App\Enums\HalalStatus;
use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\FreeFromAttribute;
use App\Models\Ingredient;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Database\Seeder;

/**
 * Realistic demo catalogue. Idempotent — keyed on slug and SKU.
 * Prices are PKR at believable Pakistani retail levels for this category.
 */
class CatalogueDemoSeeder extends Seeder
{
    public function run(): void
    {
        $location = $this->location();
        $categories = $this->categories();

        $this->products($categories, $location);
    }

    private function location(): InventoryLocation
    {
        return InventoryLocation::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main warehouse', 'city' => 'Karachi', 'is_default' => true, 'is_active' => true],
        );
    }

    /** @return array<string, Category> */
    private function categories(): array
    {
        $tree = [
            ['name' => 'Makeup', 'children' => ['Lips', 'Face']],
            ['name' => 'Skincare', 'children' => ['Moisturisers', 'Cleansers']],
            ['name' => 'Nails', 'children' => []],
            ['name' => 'Gift Sets', 'children' => []],
        ];

        $bySlug = [];
        $position = 0;

        foreach ($tree as $node) {
            $parent = Category::firstOrCreate(
                ['slug' => str($node['name'])->slug()->toString()],
                [
                    'name' => $node['name'],
                    'is_active' => true,
                    'show_in_menu' => true,
                    'is_featured' => true,
                    'position' => $position += 10,
                    'description' => "Halal {$node['name']} — every ingredient named, every claim evidenced.",
                ],
            );

            $bySlug[$parent->slug] = $parent;
            $childPosition = 0;

            foreach ($node['children'] as $childName) {
                $child = Category::firstOrCreate(
                    ['slug' => str($childName)->slug()->toString()],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'show_in_menu' => true,
                        'position' => $childPosition += 10,
                        'description' => "Halal {$childName} from Glow Halal.",
                    ],
                );

                $bySlug[$child->slug] = $child;
            }
        }

        return $bySlug;
    }

    private function products(array $categories, InventoryLocation $location): void
    {
        $definitions = [
            [
                'name' => 'Rose Petal Matte Lipstick',
                'brand' => 'Glow Halal',
                'sku_prefix' => 'GH-LIP',
                'primary' => 'lips',
                'extra_categories' => ['makeup'],
                'short_description' => 'A carmine-free matte lipstick in four wearable rose tones, coloured with mineral iron oxides rather than insect-derived pigment.',
                'description' => '<p>Getting a true rose red without carmine is genuinely harder than the industry admits — it is why so many "clean" brands quietly keep CI 75470 on the label. This formulation reaches full opacity using iron oxides and a shea butter base, so the colour holds without the insect-derived pigment.</p><p>The finish is a soft matte rather than a flat one: comfortable for a full day, with none of the tightness that usually comes with long-wear matte formulas.</p>',
                'how_to_use' => '<ul><li>Line the natural lip edge first for a sharper result.</li><li>Apply from the centre outward, then blot once and reapply for full opacity.</li><li>Remove with an oil-based cleanser — matte pigment resists water alone.</li></ul>',
                'ingredients_note' => 'Ricinus Communis (Castor) Seed Oil, Butyrospermum Parkii (Shea) Butter, Cetearyl Alcohol, Candelilla Wax, CI 77491, CI 77492, Tocopherol.',
                'is_featured' => true,
                'is_new_arrival' => true,
                'halal' => [
                    'overall_status' => HalalStatus::Halal,
                    'alcohol_status' => AlcoholStatus::FattyAlcoholOnly,
                    'is_vegan' => true,
                    'is_cruelty_free' => true,
                    'is_self_declared' => true,
                    'is_wudu_friendly' => false,
                    'manufacturer_name' => 'Glow Halal Manufacturing',
                    'manufacturing_country' => 'PK',
                    'summary' => 'Coloured with mineral iron oxides, not carmine. The only alcohol present is cetearyl alcohol, a waxy emollient with no intoxicating properties.',
                ],
                'claims' => ['carmine_free', 'alcohol_free', 'vegan', 'cruelty_free', 'animal_derived_free'],
                'ingredients' => [
                    ['shea-butter', 0, 18.0, true, 'West African, plant-pressed'],
                    ['cetearyl-alcohol', 1, 6.5, false, null],
                    ['iron-oxides', 2, 4.0, true, 'Mineral pigment — carmine substitute'],
                ],
                'variants' => [
                    ['Nude Rose', 2450, 2950, 12],
                    ['Deep Rosewood', 2450, 2950, 8],
                    ['Soft Blush', 2450, null, 3],
                    ['Classic Red', 2650, null, 0],
                ],
            ],
            [
                'name' => 'Barrier Repair Night Cream',
                'brand' => 'Glow Halal',
                'sku_prefix' => 'GH-CRM',
                'primary' => 'moisturisers',
                'extra_categories' => ['skincare'],
                'short_description' => 'A niacinamide night cream with documented plant-derived glycerin — supplier-evidenced, not assumed.',
                'description' => '<p>Glycerin is where halal label-reading usually fails: the INCI name is identical whether the feedstock was palm or tallow. We hold supplier documentation for the plant origin of every batch, and we will show it to you on request.</p><p>Formulated around 5% niacinamide with a shea and squalane base, for overnight barrier support rather than immediate surface shine.</p>',
                'how_to_use' => '<ul><li>Apply to clean skin as the final step of an evening routine.</li><li>Warm a pea-sized amount between fingertips before pressing into the skin.</li><li>Introduce every other night if you have not used niacinamide before.</li></ul>',
                'ingredients_note' => 'Aqua, Glycerin, Squalane, Niacinamide, Butyrospermum Parkii (Shea) Butter, Cetearyl Alcohol, Tocopherol, Phenoxyethanol.',
                'is_featured' => true,
                'halal' => [
                    'overall_status' => HalalStatus::Halal,
                    'alcohol_status' => AlcoholStatus::FattyAlcoholOnly,
                    'is_vegan' => true,
                    'is_cruelty_free' => true,
                    'is_self_declared' => true,
                    'manufacturer_name' => 'Glow Halal Manufacturing',
                    'manufacturing_country' => 'PK',
                    'summary' => 'The glycerin in this formulation is documented plant-derived (palm), evidenced by supplier documentation rather than assumed from the INCI name.',
                ],
                'claims' => ['alcohol_free', 'paraben_free', 'vegan', 'cruelty_free', 'pork_derivative_free'],
                'ingredients' => [
                    ['glycerin', 0, 8.0, true, 'Palm-derived; supplier documentation on file'],
                    ['squalane', 1, 5.0, true, 'Olive-derived'],
                    ['niacinamide', 2, 5.0, true, null],
                    ['shea-butter', 3, 3.0, false, null],
                    ['cetearyl-alcohol', 4, 2.5, false, null],
                ],
                'variants' => [
                    ['50 ml', 3850, 4500, 22],
                    ['15 ml travel', 1450, null, 40],
                ],
            ],
            [
                'name' => 'Breathable Wudu-Friendly Nail Polish',
                'brand' => 'Glow Halal',
                'sku_prefix' => 'GH-NAI',
                'primary' => 'nails',
                'extra_categories' => [],
                'short_description' => 'A water-permeable nail polish that allows ablution without removal, in six muted shades.',
                'description' => '<p>Conventional nail polish forms a sealed film that water cannot pass through, which is why it has traditionally been incompatible with wudu. This formulation uses a permeable polymer matrix that admits water to the nail plate while holding colour.</p><p>Permeability has been laboratory-tested rather than claimed. The trade-off is honest: wear time is shorter than a sealed polish, typically four to five days before touch-up.</p>',
                'how_to_use' => '<ul><li>Apply two thin coats rather than one thick one — permeability depends on film thickness.</li><li>Allow sixty seconds between coats.</li><li>Skip a top coat; a conventional sealer will negate the permeability.</li></ul>',
                'ingredients_note' => 'Ethyl Acetate, Butyl Acetate, Nitrocellulose, Acrylates Copolymer, CI 77491, CI 77891.',
                'is_new_arrival' => true,
                'halal' => [
                    'overall_status' => HalalStatus::Halal,
                    'alcohol_status' => AlcoholStatus::None,
                    'is_wudu_friendly' => true,
                    'is_vegan' => true,
                    'is_cruelty_free' => true,
                    'is_self_declared' => true,
                    'manufacturer_name' => 'Glow Halal Manufacturing',
                    'manufacturing_country' => 'PK',
                    'summary' => 'Water permeability is laboratory-tested. This product currently carries a manufacturer declaration rather than third-party certification, and is labelled as such.',
                ],
                'claims' => ['wudu_friendly', 'carmine_free', 'vegan', 'cruelty_free'],
                'ingredients' => [
                    ['iron-oxides', 0, 3.0, true, 'Mineral pigment'],
                ],
                'variants' => [
                    ['Sand', 1650, null, 15],
                    ['Dusty Rose', 1650, null, 11],
                    ['Slate', 1650, null, 6],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $this->createProduct($definition, $categories, $location);
        }
    }

    private function createProduct(array $d, array $categories, InventoryLocation $location): void
    {
        $slug = str($d['name'])->slug()->toString();

        $product = Product::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $d['name'],
                'brand' => $d['brand'],
                'sku_prefix' => $d['sku_prefix'],
                'primary_category_id' => $categories[$d['primary']]->id ?? null,
                'short_description' => $d['short_description'],
                'description' => $d['description'],
                'how_to_use' => $d['how_to_use'],
                'ingredients_note' => $d['ingredients_note'],
                'status' => ProductStatus::Active,
                'published_at' => now()->subDays(random_int(2, 30)),
                'is_featured' => $d['is_featured'] ?? false,
                'is_new_arrival' => $d['is_new_arrival'] ?? false,
                'return_window_days' => 14,
            ],
        );

        // Categories
        $categoryIds = collect([$d['primary'], ...$d['extra_categories']])
            ->map(fn (string $key) => $categories[$key]->id ?? null)
            ->filter()
            ->values()
            ->mapWithKeys(fn ($id, $i) => [$id => ['position' => $i]])
            ->all();

        $product->categories()->syncWithoutDetaching($categoryIds);

        // Halal profile
        $product->halalProfile()->updateOrCreate([], [
            ...$d['halal'],
            'last_reviewed_at' => now()->subDays(5),
            'reviewed_by_user_id' => 1,
        ]);

        // Verified "free from" claims
        $claims = FreeFromAttribute::whereIn('code', $d['claims'])->pluck('id');
        $product->freeFromAttributes()->syncWithoutDetaching(
            $claims->mapWithKeys(fn ($id) => [$id => [
                'is_verified' => true,
                'verified_by_user_id' => 1,
                'verified_at' => now()->subDays(5),
                'evidence_note' => 'Derived from full INCI review and supplier confirmation.',
            ]])->all()
        );

        // Ingredients in INCI order
        foreach ($d['ingredients'] as [$ingredientSlug, $position, $concentration, $isKey, $sourceNote]) {
            $ingredient = Ingredient::where('slug', $ingredientSlug)->first();

            if (! $ingredient) {
                continue;
            }

            $product->ingredients()->syncWithoutDetaching([$ingredient->id => [
                'position' => $position,
                'concentration_percent' => $concentration,
                'is_key_ingredient' => $isKey,
                'source_note' => $sourceNote,
                'resolved_halal_status' => $sourceNote !== null ? HalalStatus::Halal->value : null,
            ]]);
        }

        // Variants + inventory
        foreach ($d['variants'] as $i => [$variantName, $price, $compareAt, $stock]) {
            $sku = $d['sku_prefix'].'-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);

            $variant = ProductVariant::firstOrCreate(
                ['sku' => $sku],
                [
                    'product_id' => $product->id,
                    'name' => $variantName,
                    'price_amount' => Money::fromRupees($price),
                    'compare_at_amount' => $compareAt ? Money::fromRupees($compareAt) : null,
                    'cost_amount' => Money::fromRupees((int) round($price * 0.45)),
                    'weight_grams' => 60,
                    'is_default' => $i === 0,
                    'is_active' => true,
                    'track_inventory' => true,
                    'position' => $i,
                ],
            );

            $item = InventoryItem::firstOrCreate(
                ['product_variant_id' => $variant->id, 'inventory_location_id' => $location->id],
                ['quantity_on_hand' => $stock, 'quantity_reserved' => 0, 'reorder_level' => 5, 'reorder_quantity' => 24],
            );

            if ($item->wasRecentlyCreated === false && $item->quantity_on_hand !== $stock) {
                $item->update(['quantity_on_hand' => $stock]);
            }
        }

        // NO certifications are seeded. Glow Halal holds no third-party halal
        // certification, and a demo certificate row would surface on the storefront
        // as a credential the business does not have. Every demo product is
        // therefore modelled as `is_self_declared` — the weaker, truthful claim
        // (§2.5). Add real certificates through the admin if they are ever issued.

        $product->seoMeta()->firstOrCreate([], [
            'meta_title' => $d['name'].' — Halal Cosmetics',
            'meta_description' => str($d['short_description'])->limit(155)->toString(),
            'og_type' => 'product',
            'is_indexable' => true,
            'is_followable' => true,
        ]);
    }
}
