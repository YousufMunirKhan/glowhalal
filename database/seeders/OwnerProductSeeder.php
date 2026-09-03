<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The owner's two real products: Lookman-e-Hayat (Luqman-e-Hayat) herbal oil in
 * 50 ml and 100 ml. Modelled as TWO products because the owner wants two on the
 * homepage. Prices are stored in PAISA: 120000 = PKR 1,200.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CONTENT COMES FROM `data/lookman-products.json`, EXPORTED FROM PRODUCTION.
 *
 * This seeder used to carry hand-written copy inline, and production drifted a
 * long way past it. On 3 Sep 2026 the live 50 ml had 11 English FAQs and 5 Roman
 * Urdu ones (this file had 7 and no Urdu at all), full Roman Urdu name/slug/
 * description/how-to-use, and prices of Rs 1,200 / Rs 2,200 against the
 * Rs 1,800 / Rs 3,000 written here. Running the old seeder would have silently
 * destroyed every one of those admin edits.
 *
 * So the JSON is a snapshot of live, and the split below is deliberate:
 *
 *   • COMMERCIAL (price, compare-at, status, category, position) — ALWAYS synced.
 *     This file is the version-controlled source of truth for what things cost.
 *   • COPY (name, descriptions, how-to-use, FAQs, ingredients, Roman Urdu, SEO)
 *     — written ONLY when the product is created. The admin owns copy.
 *   • STOCK and IMAGES — create-only. Stock changes with every order and images
 *     are uploaded through the admin; a seeder must never reset either.
 *
 * To re-sync the JSON after editing copy in the admin, re-export from production
 * rather than editing this file by hand.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * ⚠️ INGREDIENTS: this is a RESOLD third-party product. `ingredients_note` lists
 * only what could be verified (sesame/til oil + guggul resin). The owner must
 * confirm and complete it from the physical bottle label.
 *
 * ⚠️ CLAIMS: therapeutic-adjacent herbal oil. Copy uses "traditionally used /
 * may soothe" framing plus a safety note — NO cure claims, no halal
 * certification claim (per routes/web.php rule).
 */
class OwnerProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = json_decode(file_get_contents(__DIR__.'/data/lookman-products.json'), true);

        if (! is_array($items) || $items === []) {
            $this->command?->error('  OwnerProductSeeder: data/lookman-products.json is missing or empty.');

            return;
        }

        DB::transaction(function () use ($items) {
            $skincare = Category::where('slug', 'skincare')->first();

            $oils = Category::updateOrCreate(
                ['slug' => 'oils'],
                [
                    'name' => 'Oils',
                    'parent_id' => $skincare?->id,
                    'description' => 'Herbal skin & massage oils. Every ingredient published in full.',
                    'is_active' => true,
                    'show_in_menu' => true,
                    'position' => 10,
                ]
            );

            // Self-sufficient: create the default location if the demo seeder
            // hasn't (production seeds this seeder without the demo data).
            $location = InventoryLocation::firstOrCreate(
                ['code' => 'MAIN'],
                ['name' => 'Main warehouse', 'is_default' => true, 'is_active' => true],
            );

            foreach ($items as $slug => $row) {
                // Always synced — this file is the source of truth for price.
                $commercial = [
                    'primary_category_id' => $oils->id,
                    'sku_prefix' => $row['sku_prefix'],
                    'brand' => $row['brand'],
                    'price_min_amount' => $row['price'],
                    'price_max_amount' => $row['price'],
                    'compare_at_max_amount' => $row['compare_at'],
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new_arrival' => true,
                    'return_window_days' => $row['return_window_days'],
                    'position' => $row['position'],
                ];

                // Create-only — the admin owns copy, stock and images.
                $copy = [
                    'name' => $row['name'],
                    'name_ur' => $row['name_ur'],
                    'slug_ur' => $row['slug_ur'],
                    'short_description' => $row['short_description'],
                    'short_description_ur' => $row['short_description_ur'],
                    'description' => $row['description'],
                    'description_ur' => $row['description_ur'],
                    'how_to_use' => $row['how_to_use'],
                    'how_to_use_ur' => $row['how_to_use_ur'],
                    'faqs' => $row['faqs'],
                    'faqs_ur' => $row['faqs_ur'],
                    'ingredients_note' => $row['ingredients_note'],
                    'meta_title_ur' => $row['meta_title_ur'],
                    'meta_description_ur' => $row['meta_description_ur'],
                    'total_stock' => $row['stock'],
                ];

                $product = Product::where('slug', $slug)->first();
                $isNew = $product === null;

                if ($isNew) {
                    $product = Product::create(
                        ['slug' => $slug, 'published_at' => now()] + $commercial + $copy
                    );
                } else {
                    // Never re-stamp published_at — a live product keeps its date.
                    $product->update($commercial);
                }

                $product->categories()->syncWithoutDetaching([$oils->id]);

                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $row['variant']['sku']],
                    [
                        'product_id' => $product->id,
                        'name' => $row['variant']['name'],
                        'price_amount' => $row['price'],
                        'weight_grams' => $row['variant']['weight'],
                        'is_default' => true,
                        'is_active' => true,
                        'track_inventory' => true,
                        'allow_backorder' => false,
                        'position' => 0,
                    ]
                );

                // Stock is live state — seed it once, never overwrite it.
                InventoryItem::firstOrCreate(
                    [
                        'product_variant_id' => $variant->id,
                        'inventory_location_id' => $location->id,
                    ],
                    [
                        'quantity_on_hand' => $row['stock'],
                        'quantity_reserved' => 0,
                        'reorder_level' => 5,
                        'reorder_quantity' => 20,
                    ]
                );

                // Photos are uploaded through the admin. This is a fresh-install
                // fallback only — it used to delete every image row that did not
                // match the hard-coded path, which would wipe an admin upload.
                if ($product->images()->doesntExist() && $row['image']) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $row['image'],
                        'disk' => 'public',
                        'alt_text' => 'Lookman-e-Hayat (Luqman-e-Hayat) herbal oil '
                            .$row['variant']['name'].' bottle',
                        'is_primary' => true,
                        'position' => 0,
                    ]);
                }

                // SEO meta carries the price, so it is refreshed with the price.
                $product->seoMeta()->updateOrCreate([], [
                    'meta_title' => $row['seo']['meta_title'],
                    'meta_description' => $row['seo']['meta_description'],
                ]);

                $verb = $isNew ? 'created' : 'price synced';
                $this->command?->info("  {$row['name']} — PKR ".number_format($row['price'] / 100)." ({$verb})");
            }
        });
    }
}
