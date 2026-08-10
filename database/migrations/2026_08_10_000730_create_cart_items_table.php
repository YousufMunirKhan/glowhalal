<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_amount')
                  ->comment('Snapshot at add-to-cart. Revalidated against the live price at checkout.');
            $table->unsignedBigInteger('line_total_amount')->default(0);
            $table->string('name_snapshot', 200);
            $table->json('options_snapshot')->nullable()
                  ->comment('[{"attribute":"Shade","value":"Nude Rose","hex":"#C08081"}] — renders the cart without joins.');
            $table->string('image_path_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id'], 'cart_items_cart_variant_unique');
            $table->index('product_variant_id', 'cart_items_variant_index');
        });

        DB::statement("ALTER TABLE cart_items ADD CONSTRAINT cart_items_quantity_check CHECK (quantity > 0)");
    }

    public function down(): void { Schema::dropIfExists('cart_items'); }
};
