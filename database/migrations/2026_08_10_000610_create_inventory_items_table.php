<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_location_id')->constrained()->restrictOnDelete();
            $table->integer('quantity_on_hand')->default(0)
                  ->comment('Signed. Can go negative only when allow_backorder is true.');
            $table->unsignedInteger('quantity_reserved')->default(0)
                  ->comment('Held by active carts/unpaid orders. See stock_reservations.');
            // Both operands are CAST to SIGNED deliberately. In MySQL, if either operand of `-`
            // is unsigned the entire expression is evaluated as unsigned regardless of the
            // generated column's declared signed type, so `on_hand 2 - reserved 5` throws
            // "BIGINT UNSIGNED value is out of range" instead of yielding -3. That case is
            // reachable in normal operation: backorders permit negative on-hand stock, and
            // checkout increments reservations against live stock.
            $table->integer('quantity_available')
                  ->storedAs('CAST(quantity_on_hand AS SIGNED) - CAST(quantity_reserved AS SIGNED)')
                  ->comment('Generated column — always consistent, and indexable. May be negative.');
            $table->unsignedInteger('reorder_level')->default(5);
            $table->unsignedInteger('reorder_quantity')->default(20);
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['product_variant_id', 'inventory_location_id'], 'inventory_items_variant_location_unique');
            $table->index('quantity_available', 'inventory_items_available_index');
        });
    }

    public function down(): void { Schema::dropIfExists('inventory_items'); }
};
