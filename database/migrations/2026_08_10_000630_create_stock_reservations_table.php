<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->morphs('reservable');          // Cart during checkout, Order once placed
            $table->unsignedInteger('quantity');
            $table->string('status', 20)->default('held')
                  ->comment('App\Enums\ReservationStatus: held|committed|released|expired');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'stock_reservations_status_expires_index');
            $table->index(['product_variant_id', 'status'], 'stock_reservations_variant_status_index');
        });
    }

    public function down(): void { Schema::dropIfExists('stock_reservations'); }
};
