<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roman Urdu (ur-Latn) product content — the PDP counterpart of the bilingual
 * blog. One product row keeps one SKU, price and stock; these columns carry the
 * second CONTENT skin, rendered at /ur-roman/products/{slug_ur} with reciprocal
 * hreflang to the English page (never a duplicate: distinct slug, distinct
 * primary keyword, self-canonical — the same anti-cannibalization contract the
 * blog enforces).
 *
 * All nullable: a product with no slug_ur simply has no Roman Urdu page. The
 * route 404s rather than falling back to English content on the UR URL, which
 * would create the exact duplicate-content problem this schema exists to avoid.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ur', 200)->nullable()->after('name');
            $table->string('slug_ur', 220)->nullable()->after('slug');
            $table->text('short_description_ur')->nullable()->after('short_description');
            $table->longText('description_ur')->nullable()->after('description');
            $table->longText('how_to_use_ur')->nullable()->after('how_to_use');
            $table->json('faqs_ur')->nullable()->after('faqs');
            $table->string('meta_title_ur', 255)->nullable();
            $table->text('meta_description_ur')->nullable();

            $table->unique('slug_ur', 'products_slug_ur_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_ur_unique');
            $table->dropColumn([
                'name_ur', 'slug_ur', 'short_description_ur', 'description_ur',
                'how_to_use_ur', 'faqs_ur', 'meta_title_ur', 'meta_description_ur',
            ]);
        });
    }
};
