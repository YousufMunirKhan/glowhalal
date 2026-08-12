<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * Companion to the Merchant-Center softening: the two oils' SEO meta
 * descriptions still carried "known for fading old burn marks", which renders
 * in the <head> (crawled by Google Merchant Center's landing-page check) and as
 * the search snippet. Drops just that claim clause; the keyword, size, price and
 * COD text all stay, so the snippet is unchanged apart from the health claim.
 * Idempotent — str_replace no-ops once the clause is gone; both sizes share it.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['herbal-skin-oil-50ml', 'herbal-skin-oil-100ml'] as $slug) {
            $meta = Product::where('slug', $slug)->first()?->seoMeta;

            if (! $meta) {
                continue;
            }

            $meta->meta_description = str_replace(
                ', known for fading old burn marks',
                '',
                (string) $meta->meta_description,
            );
            $meta->save();
        }
    }

    public function down(): void
    {
        // One-way content softening; nothing to roll back.
    }
};
