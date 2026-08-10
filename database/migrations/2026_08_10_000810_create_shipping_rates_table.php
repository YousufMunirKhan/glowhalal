<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);                  // "Standard (3–5 days)"
            $table->string('type', 20)->default('flat')
                  ->comment('App\Enums\ShippingRateType: flat|free_over|weight_based');
            $table->unsignedBigInteger('amount')->default(0)->comment('paisa');
            $table->unsignedBigInteger('free_over_subtotal_amount')->nullable();
            $table->unsignedInteger('min_weight_grams')->nullable();
            $table->unsignedInteger('max_weight_grams')->nullable();
            $table->unsignedBigInteger('cod_surcharge_amount')->default(0)
                  ->comment('COD carries a real courier fee in Pakistan; make it explicit rather than baked in.');
            $table->unsignedTinyInteger('min_delivery_days')->nullable();
            $table->unsignedTinyInteger('max_delivery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['shipping_zone_id', 'is_active', 'position'], 'shipping_rates_zone_active_index');
        });
    }

    public function down(): void { Schema::dropIfExists('shipping_rates'); }
};
