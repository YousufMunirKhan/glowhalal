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
 * ⚠️ RESOLVED 15 Aug 2026: the four reviews describing burn marks and scars
 * fading are now seeded as `rejected` (unpublished). They are real customer
 * experiences, but they read as strong efficacy claims, and Google Merchant
 * Center / Meta Commerce can treat health claims on the landing page as
 * misrepresentation and suspend the catalog. Only the one claim-free review
 * stays `approved`, so `aggregateRating` is built from that alone. The owner
 * can re-approve any of them in Admin → Reviews; doing so re-exposes the claim.
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

        // author, rating, title, body, status — customers' own words (Roman Urdu).
        //
        // The four `rejected` rows are kept here verbatim ON PURPOSE: they are
        // real messages and deleting them would lose the record. They stay
        // unpublished because they describe burn marks and scars fading, which
        // reads as a medical efficacy claim on a product page that feeds
        // Merchant Center and Meta Commerce (owner's decision, 15 Aug 2026 —
        // see migration 2026_08_15_000100_hide_efficacy_claim_reviews).
        // Seeding them as `approved` here would silently undo that migration on
        // the next `db:seed`.
        $reviews = [
            ['Maira Yousuf', 5, 'Best oil', 'Bohat acha oil hai, best. Zaroor recommend karti hoon.', 'approved'],
            ['Owais',        5, 'Daag chala gaya', 'Daag chala gaya — bohat acha oil hai.', 'rejected'],
            ['Yousuf',       5, 'Jaldi farak para', 'Bohat jaldi farak para, jalne ke daag chale gaye.', 'rejected'],
            ['Rais',         4, 'Bache ke pair ka daag gaya', 'Mere bache ke pair par jo daag tha, aik mahine mein chala gaya.', 'rejected'],
            ['Bilal',        5, 'Nishan chala gaya', 'Meri wife ke haath par jalne ka nishan aik mahine mein chala gaya.', 'rejected'],
        ];

        foreach ($reviews as [$name, $rating, $title, $body, $status]) {
            ProductReview::updateOrCreate(
                ['product_id' => $product->id, 'author_name' => $name],
                [
                    'rating' => $rating,
                    'title' => $title,
                    'body' => $body,
                    'status' => $status,
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
