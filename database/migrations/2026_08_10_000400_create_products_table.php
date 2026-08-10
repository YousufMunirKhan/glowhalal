<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_category_id')->nullable()
                  ->constrained('categories')->nullOnDelete()
                  ->comment('Drives breadcrumbs and canonical category. Many-to-many lives in category_product.');
            $table->string('name', 200);
            $table->string('slug', 220);
            $table->string('sku_prefix', 40)->nullable();
            $table->string('brand', 100)->nullable();
            $table->text('short_description')->nullable()
                  ->comment('Plain text. Used for listing cards and meta description fallback.');
            $table->longText('description')->nullable()
                  ->comment('Filament RichEditor output. See §4.6 for storage format.');
            $table->longText('how_to_use')->nullable();
            $table->longText('ingredients_note')->nullable()
                  ->comment('Free-text INCI caveats. The structured list is in ingredient_product.');

            // Denormalised pricing, maintained by ProductVariantObserver
            $table->unsignedBigInteger('price_min_amount')->default(0)->comment('paisa');
            $table->unsignedBigInteger('price_max_amount')->default(0)->comment('paisa');
            $table->unsignedBigInteger('compare_at_max_amount')->nullable()->comment('paisa');

            $table->string('status', 20)->default('draft')
                  ->comment('App\Enums\ProductStatus: draft|active|archived');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->unsignedTinyInteger('return_window_days')->default(7);

            // Denormalised aggregates
            $table->unsignedInteger('total_stock')->default(0)
                  ->comment('Sum of available quantity across variants. Maintained by inventory events.');
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('reviews_average', 3, 2)->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);

            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug', 'products_slug_unique');
            $table->index(['status', 'published_at'], 'products_status_published_index');
            $table->index(['primary_category_id', 'status'], 'products_category_status_index');
            $table->index(['status', 'is_featured', 'position'], 'products_featured_index');
            $table->index(['status', 'price_min_amount'], 'products_status_price_index');
            $table->index(['status', 'created_at'], 'products_status_created_index');
            $table->fullText(['name', 'short_description'], 'products_fulltext_index');
        });
    }

    public function down(): void { Schema::dropIfExists('products'); }
};
