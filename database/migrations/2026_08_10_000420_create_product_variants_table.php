<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64);
            $table->string('barcode', 64)->nullable();
            $table->string('name', 200)->nullable()
                  ->comment('Derived label, e.g. "Nude Rose / 4.2g". Denormalised for order snapshots.');

            $table->unsignedBigInteger('price_amount')->comment('paisa, PKR');
            $table->unsignedBigInteger('compare_at_amount')->nullable()
                  ->comment('paisa. Struck-through "was" price. Must exceed price_amount to display.');
            $table->unsignedBigInteger('cost_amount')->nullable()
                  ->comment('paisa. Never exposed to the storefront. Margin reporting only.');

            $table->unsignedInteger('weight_grams')->default(0)
                  ->comment('Drives weight-based shipping rates.');
            $table->unsignedSmallInteger('length_mm')->nullable();
            $table->unsignedSmallInteger('width_mm')->nullable();
            $table->unsignedSmallInteger('height_mm')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->unsignedSmallInteger('max_per_order')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('sku', 'product_variants_sku_unique');
            $table->index(['product_id', 'position'], 'product_variants_product_position_index');
            $table->index(['product_id', 'is_default'], 'product_variants_product_default_index');
            $table->index(['is_active', 'price_amount'], 'product_variants_active_price_index');
        });

        DB::statement("
            ALTER TABLE product_variants ADD CONSTRAINT product_variants_compare_at_check
            CHECK (compare_at_amount IS NULL OR compare_at_amount >= price_amount)
        ");
    }

    public function down(): void { Schema::dropIfExists('product_variants'); }
};
