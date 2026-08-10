<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);                  // "Karachi", "Punjab", "Rest of Pakistan"
            $table->json('provinces')->nullable();
            $table->json('cities')->nullable()->comment('Lowercased city names. Matched before provinces.');
            $table->boolean('is_fallback')->default(false)->comment('Exactly one zone catches everything else.');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'position'], 'shipping_zones_active_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('shipping_zones'); }
};
