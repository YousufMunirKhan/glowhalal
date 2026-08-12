<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

/**
 * Real customer reviews for the flagship Lookman-e-Hayat oil, collected by the
 * owner over WhatsApp after Cash-on-Delivery orders and provided verbatim.
 * Attached to the 50 ml product (the page we are pushing for the head term).
 *
 * These are GENUINE customer testimonials — not fabricated, not copied. That is
 * the only reason they are allowed here at all (seo.md §4.7 / content-honesty
 * rule: never seed fake reviews). Wording is the customers' own; nothing is
 * embellished. order_id is left null because these were not matched back to an
 * order row, so NO "Verified purchase" badge is shown — which is the honest
 * state.
 *
 * ⚠️ NOTE FOR THE OWNER: several reviews describe burn marks fading. They are
 * real customer experiences, but they read as strong efficacy claims. Google
 * Merchant Center / Meta Commerce can treat health claims on the landing page
 * as misrepresentation and suspend the catalog. If that becomes a problem,
 * reviews can be edited/hidden in Admin → Reviews without touching this file.
 *
 * Idempotent — keyed on (product_id, author_name); re-running updates in place.
 */
class OwnerReviewSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('slug', 'herbal-skin-oil-50ml')->first();

        if (! $product) {
            $this->command?->warn('  50ml product not found — skipping reviews.');

            return;
        }

        // author, rating, title, body — customers' own words (Roman Urdu).
        $reviews = [
            ['Maira Yousuf', 5, 'Best oil', 'Bohat acha oil hai, best. Zaroor recommend karti hoon.'],
            ['Owais',        5, 'Daag chala gaya', 'Daag chala gaya — bohat acha oil hai.'],
            ['Yousuf',       5, 'Jaldi farak para', 'Bohat jaldi farak para, jalne ke daag chale gaye.'],
            ['Rais',         4, 'Bache ke pair ka daag gaya', 'Mere bache ke pair par jo daag tha, aik mahine mein chala gaya.'],
            ['Bilal',        5, 'Nishan chala gaya', 'Meri wife ke haath par jalne ka nishan aik mahine mein chala gaya.'],
        ];

        foreach ($reviews as [$name, $rating, $title, $body]) {
            ProductReview::updateOrCreate(
                ['product_id' => $product->id, 'author_name' => $name],
                [
                    'rating' => $rating,
                    'title' => $title,
                    'body' => $body,
                    'status' => 'approved',
                    'user_id' => null,
                    'order_id' => null,   // not order-linked => no "Verified purchase" badge
                ],
            );
        }

        // Refresh the cached aggregates the page and JSON-LD read (no observer
        // maintains these). Average over APPROVED reviews only.
        $approved = $product->reviews()->approved();
        $product->forceFill([
            'reviews_count' => (clone $approved)->count(),
            'reviews_average' => round((float) (clone $approved)->avg('rating'), 2),
        ])->save();

        $this->command?->info("  {$product->name}: {$product->reviews_count} reviews, avg {$product->reviews_average}");
    }
}
