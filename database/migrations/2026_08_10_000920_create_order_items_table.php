<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();

            $table->string('sku', 64);
            $table->string('product_name', 200);
            $table->string('variant_name', 200)->nullable();
            $table->string('product_slug', 220)->nullable()->comment('Reorder link. May 404 later — that is acceptable.');
            $table->json('options_snapshot')->nullable();
            $table->string('image_path_snapshot')->nullable();
            $table->json('halal_snapshot')->nullable()
                  ->comment('Certification state advertised at purchase. See §2.6 — this is a compliance record.');

            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_amount');
            $table->unsignedBigInteger('line_subtotal_amount');
            $table->unsignedBigInteger('line_discount_amount')->default(0);
            $table->unsignedBigInteger('line_tax_amount')->default(0);
            $table->unsignedBigInteger('line_total_amount');
            $table->unsignedBigInteger('unit_cost_amount')->nullable()->comment('Margin reporting snapshot.');

            $table->unsignedInteger('quantity_refunded')->default(0);
            $table->timestamps();

            $table->index('order_id', 'order_items_order_index');
            $table->index('product_id', 'order_items_product_index');
            $table->index('product_variant_id', 'order_items_variant_index');
            $table->index('sku', 'order_items_sku_index');
        });

        DB::statement("ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_check CHECK (quantity > 0)");
    }

    public function down(): void { Schema::dropIfExists('order_items'); }
};
