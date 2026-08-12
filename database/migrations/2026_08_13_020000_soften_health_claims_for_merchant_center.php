<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * De-risk the two oil pages + the product feed for Google Merchant Center /
 * Meta Commerce, which treat "fades/removes burn marks" as an unapproved health
 * claim and can suspend the catalog over it.
 *
 * The feed description is the product's short_description (FeedController::
 * plainDescription), so fixing the short_description fixes the feed AND the
 * on-page lead in one edit. The body's single definitive claim is reworded to
 * clearly-hedged, traditional-cosmetic, "not a medicine" framing. Nothing is
 * invented and the honest, well-hedged detail lower down (and the safety notes)
 * stay — only the merchant-facing, definitive wording is softened.
 *
 * Idempotent: str_replace no-ops once the old wording is gone. Applies to both
 * sizes (identical fragments).
 */
return new class extends Migration
{
    public function up(): void
    {
        $shortFrom = 'Traditionally used to fade old burn marks (purane jalne ke daag) and for massage.';
        $shortTo = 'A traditional herbal oil for massage and everyday skin care.';

        $descPairs = [
            [
                'Across Pakistan and South Asia it is best known for one thing — <strong>gently fading the look of old burn marks</strong> — and as an everyday massage oil.',
                'It is used across Pakistan and South Asia as an everyday massage and skin oil. It is a traditional cosmetic herbal oil, not a medicine — for external use only, and results vary from person to person.',
            ],
            [
                'its most popular traditional use.',
                'a traditional use.',
            ],
        ];

        foreach (['herbal-skin-oil-50ml', 'herbal-skin-oil-100ml'] as $slug) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            $product->short_description = str_replace($shortFrom, $shortTo, (string) $product->short_description);

            $desc = (string) $product->description;
            foreach ($descPairs as [$from, $to]) {
                $desc = str_replace($from, $to, $desc);
            }
            $product->description = $desc;

            $product->save();
        }
    }

    public function down(): void
    {
        // One-way content softening; nothing to roll back.
    }
};
