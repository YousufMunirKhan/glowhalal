<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Structured FAQs for the product detail page.
 *
 * Stored as JSON: an ordered array of { "q": "...", "a": "..." }. Rendered as a
 * dedicated FAQ section on the product page and as FAQPage JSON-LD. Kept on the
 * product (not a separate table) because FAQs are 1:1 with a product and always
 * loaded with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('faqs')->nullable()->after('how_to_use');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('faqs');
        });
    }
};
