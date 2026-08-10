<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32);
            $table->string('name', 120);
            $table->string('city', 120)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('code', 'inventory_locations_code_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('inventory_locations'); }
};
