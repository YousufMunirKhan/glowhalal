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
 * homepage. Idempotent — updates by slug.
 *
 * Prices are stored in PAISA: 180000 = PKR 1,800.
 *
 * ⚠️ INGREDIENTS: this is a RESOLD third-party product. The ingredients_note
 * below lists only what could be verified from research (sesame/til oil + guggul
 * resin). The brand's whole promise is an ACCURATE, COMPLETE list — the owner
 * MUST confirm and complete it from the physical bottle label before selling.
 *
 * ⚠️ CLAIMS: this is a therapeutic-adjacent herbal oil. Copy uses "traditionally
 * used / may soothe" framing and a safety note — NO cure/treatment claims, no
 * halal certification claim (per routes/web.php rule).
 */
class OwnerProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
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

            // Shared, size-agnostic copy. Careful "traditional use" framing; no
            // medical cure claim; both name spellings for search intent.
            $description = <<<'HTML'
<p><strong>Lookman-e-Hayat</strong> (also spelled <strong>Luqman-e-Hayat</strong> or <strong>Lukman-e-Hayat Tel</strong>) is a traditional herbal oil that South Asian households have reached for over generations. It is a warm, amber sesame-oil base infused with <strong>guggul</strong> resin and traditional botanicals — valued as a gentle massage oil and a soothing everyday skin oil.</p>
<p>We stock it because people keep asking for it, and because it fits how we work: we tell you exactly what is in the bottle. The ingredient list is on this page, and the list of ingredients we never stock is public too.</p>

<h3>What it is traditionally used for</h3>
<p>In everyday use, this oil is most often reached for as:</p>
<ul>
  <li>A <strong>massage oil</strong> for tired muscles, shoulders, legs and joints after a long day.</li>
  <li>A <strong>skin oil</strong> to soften dry, rough or flaky patches and help lock in moisture.</li>
  <li>A <strong>scalp &amp; hair oil</strong>, massaged in before washing.</li>
  <li>A comforting oil for <strong>minor, everyday skin roughness</strong>.</li>
</ul>
<p>These are traditional, everyday uses. It is a herbal comfort oil, <strong>not a medicine</strong> — please read the safety note below.</p>

<h3>Why sesame and guggul?</h3>
<p><strong>Til (sesame) oil</strong> is a light, easily absorbed carrier oil rich in natural fatty acids, used for centuries in massage and skincare to soften and condition skin. <strong>Guggul</strong> (the resin of <em>Commiphora mukul</em>) is the warm, resinous note in the oil and a classic ingredient in traditional preparations. Together they make a nourishing oil that sinks in without feeling heavy or sticky.</p>

<h3>The honest part</h3>
<p>We publish the ingredient list for this oil on this page and make <strong>no certification claim</strong>. Check the list yourself and see <a href="/what-we-never-use">what we never use</a>. Want the supplier's documentation? Just ask.</p>

<h3>50 ml or 100 ml?</h3>
<p>It is the same oil in two sizes. The <strong>50 ml</strong> is ideal to try it or keep in a bag; the <strong>100 ml</strong> is better value if you already use it often for massage.</p>

<h3>Safety note — please read</h3>
<p>This is a traditional herbal oil for external use, <strong>not a medicine and not a substitute for medical care</strong>. It is not intended to diagnose, treat or cure any condition. For <strong>burns beyond very minor surface ones, deep or open wounds, or pain that does not settle</strong>, please see a doctor. Patch-test before first use, avoid the eyes and broken skin, keep out of reach of children, and stop use if irritation occurs.</p>
HTML;

            $howToUse = <<<'HTML'
<ul>
  <li><strong>Patch test first:</strong> dab a little on your inner forearm and wait a few hours before wider use.</li>
  <li><strong>For massage:</strong> warm a few drops between your palms and massage into muscles or joints in slow, circular motions until absorbed.</li>
  <li><strong>For skin:</strong> smooth a small amount over clean, dry skin on dry or rough areas — a little goes a long way.</li>
  <li><strong>For scalp &amp; hair:</strong> part the hair, massage into the scalp, leave 30&ndash;60 minutes (or overnight), then wash as usual.</li>
  <li><strong>External use only.</strong> Avoid the eyes, broken skin and open wounds.</li>
</ul>
HTML;

            // Verified key ingredients only. OWNER TO CONFIRM/COMPLETE from the label.
            $ingredientsNote = 'Sesamum Indicum (Til / Sesame) Seed Oil, Commiphora Mukul (Guggul) Resin Extract, traditional botanical extracts. '
                .'(Full label list to be confirmed from the product packaging.)';

            // Structured FAQ — targets the exact Google "People Also Ask" questions
            // for this product, rendered as a dedicated section on the page.
            $faqs = [
                ['q' => 'What is Lookman-e-Hayat oil used for?', 'a' => 'Traditionally, it is used as a massage oil for tired muscles and joints, and as a soothing oil for dry or rough skin, scalp and hair. It is a herbal comfort oil for everyday external use — not a medicine, and not intended to treat or cure any condition.'],
                ['q' => 'Can you use Lookman-e-Hayat oil on the face?', 'a' => 'It can be used on the skin, including the face, as a light massage or overnight moisturising oil. Use only a small amount, patch-test first, and keep it away from the eyes. If your skin is very oily or acne-prone, use it sparingly at night and stop if you notice any breakouts or irritation.'],
                ['q' => 'What is the price of Lookman-e-Hayat oil?', 'a' => 'We sell it at Rs 1,800 for 50 ml and Rs 3,000 for 100 ml, with Cash on Delivery across Pakistan and free delivery over Rs 3,000.'],
                ['q' => 'Does Lookman-e-Hayat oil have side effects?', 'a' => 'Used externally as directed, it is generally well tolerated. As with any oil, some people may notice redness or irritation. Patch-test on your inner forearm first, avoid the eyes and broken skin, keep it away from open wounds, and stop use if irritation appears. External use only.'],
                ['q' => 'What is it made of, and who makes it?', 'a' => 'It is a traditional herbal preparation — a sesame (til) oil base infused with guggul resin and botanicals. The same formulation is made by several manufacturers; the maker of the bottle we ship is printed on its label, and we can share supplier documentation on request.'],
                ['q' => 'Is it halal?', 'a' => 'The ingredients are plant-derived and we publish the full list so you can check for yourself. We hold no third-party halal certification and do not claim one.'],
                ['q' => 'Do you deliver, and can I pay cash?', 'a' => 'Yes — we ship across Pakistan with Cash on Delivery, so you pay when it reaches your door. You can also message us on WhatsApp to order or ask a question.'],
            ];

            $items = [
                [
                    'slug' => 'herbal-skin-oil-50ml',
                    'name' => 'Lookman-e-Hayat Herbal Oil (Luqman-e-Hayat Tel) — 50 ml',
                    'sku_prefix' => 'GH-OIL-50',
                    'sku' => 'GH-OIL-50',
                    'volume' => '50 ml',
                    'price' => 180000,   // PKR 1,800
                    'stock' => 25,
                    'weight' => 90,
                    'image' => 'products/lookman-e-hayat-50ml.jpg',
                    'short' => 'Traditional Lookman-e-Hayat herbal oil — a sesame-and-guggul massage & skin oil, in a handy 50 ml bottle. Full ingredient list on this page. Cash on Delivery.',
                ],
                [
                    'slug' => 'herbal-skin-oil-100ml',
                    'name' => 'Lookman-e-Hayat Herbal Oil (Luqman-e-Hayat Tel) — 100 ml',
                    'sku_prefix' => 'GH-OIL-100',
                    'sku' => 'GH-OIL-100',
                    'volume' => '100 ml',
                    'price' => 300000,   // PKR 3,000
                    'stock' => 25,
                    'weight' => 160,
                    'image' => 'products/lookman-e-hayat-100ml.jpg',
                    'short' => 'Traditional Lookman-e-Hayat herbal oil — a sesame-and-guggul massage & skin oil, in a value 100 ml bottle. Full ingredient list on this page. Cash on Delivery.',
                ],
            ];

            foreach ($items as $i => $row) {
                $product = Product::updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'primary_category_id' => $oils->id,
                        'name' => $row['name'],
                        'sku_prefix' => $row['sku_prefix'],
                        'brand' => 'Lookman-e-Hayat',
                        'short_description' => $row['short'],
                        'description' => $description,
                        'how_to_use' => $howToUse,
                        'faqs' => $faqs,
                        'ingredients_note' => $ingredientsNote,
                        'price_min_amount' => $row['price'],
                        'price_max_amount' => $row['price'],
                        'status' => 'active',
                        'published_at' => now(),
                        'is_featured' => true,
                        'is_new_arrival' => true,
                        'return_window_days' => 7,
                        'total_stock' => $row['stock'],
                        'position' => $i,
                    ]
                );

                $product->categories()->syncWithoutDetaching([$oils->id]);

                $variant = ProductVariant::updateOrCreate(
                    ['sku' => $row['sku']],
                    [
                        'product_id' => $product->id,
                        'name' => $row['volume'],
                        'price_amount' => $row['price'],
                        'weight_grams' => $row['weight'],
                        'is_default' => true,
                        'is_active' => true,
                        'track_inventory' => true,
                        'allow_backorder' => false,
                        'position' => 0,
                    ]
                );

                InventoryItem::updateOrCreate(
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

                // Primary product image (real photo in storage/app/public/products).
                // Remove any stale image rows (e.g. the old .svg) so there is
                // exactly one primary image.
                $product->images()->where('path', '!=', $row['image'])->delete();
                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'path' => $row['image']],
                    [
                        'disk' => 'public',
                        'alt_text' => 'Lookman-e-Hayat (Luqman-e-Hayat) herbal oil '.$row['volume'].' bottle',
                        'is_primary' => true,
                        'position' => 0,
                    ]
                );

                // SEO meta — keyword + geo + price + buy intent, to compete with the
                // Daraz listing for "lookman e hayat oil price pakistan". This
                // overrides the stale seoMeta row that held the old placeholder copy.
                $priceLabel = number_format($row['price'] / 100);
                $product->seoMeta()->updateOrCreate([], [
                    'meta_title' => 'Lookman-e-Hayat Oil '.$row['volume'].' — Price in Pakistan',
                    'meta_description' => 'Buy Lookman-e-Hayat (Luqman-e-Hayat) herbal oil '.$row['volume']
                        .' — traditional sesame & guggul massage & skin oil. Rs '.$priceLabel
                        .', Cash on Delivery across Pakistan.',
                ]);

                $this->command?->info("  {$row['name']} — PKR ".number_format($row['price'] / 100));
            }
        });
    }
}
