<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180)->comment('Common name, e.g. "Carmine"');
            $table->string('slug', 200);
            $table->string('inci_name', 200)->nullable()
                  ->comment('INCI standard name, e.g. "CI 75470". The label-accurate identifier.');
            $table->string('cas_number', 40)->nullable();
            $table->string('ec_number', 40)->nullable();
            $table->json('aliases')->nullable()->comment('["Cochineal","Crimson Lake","Natural Red 4"]');

            $table->string('origin', 24)->nullable()
                  ->comment('App\Enums\IngredientOrigin: plant|mineral|synthetic|marine|microbial|animal|unknown');
            $table->string('halal_status', 24)->default('unknown')
                  ->comment('App\Enums\HalalStatus: halal|haram|mashbooh|depends_on_source|not_applicable|unknown');
            $table->text('halal_notes')->nullable()
                  ->comment('Why. e.g. "Glycerin may be plant- or tallow-derived; source verification required."');

            $table->boolean('is_animal_derived')->default(false);
            $table->boolean('is_alcohol')->default(false)
                  ->comment('Intoxicating alcohol only. Fatty alcohols (cetyl, cetearyl) are NOT flagged here.');
            $table->string('function', 80)->nullable()->comment('emollient|surfactant|colourant|preservative|humectant');
            $table->text('description')->nullable()->comment('Short summary. Feeds cards and meta description fallback.');
            $table->text('benefits')->nullable();
            $table->boolean('is_common_allergen')->default(false);
            $table->boolean('is_key_ingredient_candidate')->default(false)
                  ->comment('Marketing-worthy actives, e.g. niacinamide — surfaced on product pages.');

            // ---- Editorial content page: /halal-ingredients/{slug} — see §2.7 ----
            $table->longText('content')->nullable()
                  ->comment('Filament RichEditor body for the standalone ingredient page.');
            $table->string('verdict_summary', 500)->nullable()
                  ->comment('The one-line answer, rendered above the fold: "Carmine is not halal — it is derived from insects."');
            $table->string('hero_image_path')->nullable();
            $table->string('hero_image_alt', 255)->nullable();
            $table->string('status', 20)->default('draft')->comment('App\Enums\PostStatus: draft|published|archived');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete()
                  ->comment('Who signed off the halal ruling. These pages make religious claims.');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('has_glossary_page')->default(false)
                  ->comment('True = a real page exists at /halal-ingredients/{slug}. Drives sitemap inclusion (§7.5).');

            $table->unsignedInteger('products_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'ingredients_slug_unique');
            $table->index('inci_name', 'ingredients_inci_index');
            $table->index(['halal_status', 'is_animal_derived'], 'ingredients_halal_animal_index');
            $table->index(['has_glossary_page', 'status', 'published_at'], 'ingredients_page_published_index');
            $table->fullText(['name', 'description'], 'ingredients_fulltext_index');
        });
    }

    public function down(): void { Schema::dropIfExists('ingredients'); }
};
