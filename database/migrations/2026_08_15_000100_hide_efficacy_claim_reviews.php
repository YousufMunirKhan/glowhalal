<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unpublish the four owner-collected reviews that describe burn marks and scars
 * disappearing.
 *
 * These are GENUINE customer messages (see OwnerReviewSeeder's header) — this
 * is not a fake-review cleanup. The problem is what they claim, not whether
 * they are real: "jalne ka nishan aik mahine mein chala gaya" reads as a
 * medical efficacy claim, and because SchemaGraph emits `aggregateRating` from
 * approved reviews (SchemaGraph.php:249) those claims are being amplified into
 * Google's rich results on a product landing page that also feeds
 * /feed/google.xml and /feed/meta.csv.
 *
 * Google Merchant Center and Meta Commerce both treat health claims on the
 * landing page as misrepresentation and suspend the catalogue for it; DRAP
 * separately prohibits cure/treat claims on herbal products. The seeder's own
 * author flagged this risk in the file (lines 21-25). The owner reviewed the
 * trade-off on 15 Aug 2026 and chose to unpublish these four.
 *
 * Status becomes `rejected` — the only non-public status that does not add a
 * permanent badge to the admin nav the way `pending` would. Nothing is deleted:
 * the rows stay in the table and the owner can re-approve any of them from
 * Admin → Reviews. ProductReviewObserver recounts `reviews_count` /
 * `reviews_average` on save, so the on-page rating line and the
 * `aggregateRating` node follow automatically.
 *
 * Matched on the exact body text rather than the author name so that an
 * unrelated future review from a customer of the same first name is never
 * caught by a reseed of this migration.
 */
return new class extends Migration
{
    /** The verbatim bodies to unpublish — customers' own words, unedited. */
    private const CLAIM_BODIES = [
        'Daag chala gaya — bohat acha oil hai.',
        'Bohat jaldi farak para, jalne ke daag chale gaye.',
        'Mere bache ke pair par jo daag tha, aik mahine mein chala gaya.',
        'Meri wife ke haath par jalne ka nishan aik mahine mein chala gaya.',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        DB::table('product_reviews')
            ->whereIn('body', self::CLAIM_BODIES)
            ->where('status', 'approved')
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        $this->recountAffectedProducts();
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_reviews')) {
            return;
        }

        DB::table('product_reviews')
            ->whereIn('body', self::CLAIM_BODIES)
            ->where('status', 'rejected')
            ->update(['status' => 'approved', 'updated_at' => now()]);

        $this->recountAffectedProducts();
    }

    /**
     * The observer only fires on Eloquent saves, and this migration writes with
     * the query builder for speed — so recompute the cached aggregates here.
     * Same formula as ProductReviewObserver::recount().
     */
    private function recountAffectedProducts(): void
    {
        if (! Schema::hasColumn('products', 'reviews_count')) {
            return;
        }

        $productIds = DB::table('product_reviews')
            ->whereIn('body', self::CLAIM_BODIES)
            ->distinct()
            ->pluck('product_id');

        foreach ($productIds as $productId) {
            $approved = DB::table('product_reviews')
                ->where('product_id', $productId)
                ->where('status', 'approved');

            $count = (clone $approved)->count();
            $avg = (clone $approved)->avg('rating');

            DB::table('products')->where('id', $productId)->update([
                'reviews_count' => $count,
                'reviews_average' => $avg === null ? null : round((float) $avg, 2),
            ]);
        }
    }
};
