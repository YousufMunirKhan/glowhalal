<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 40)->nullable();          // "Home", "Office"
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->string('line_1', 255);
            $table->string('line_2', 255)->nullable();
            $table->string('area', 120)->nullable();           // sector / block / colony
            $table->string('city', 120);
            $table->string('province', 40);                    // App\Enums\PakistanProvince
            $table->string('postal_code', 12)->nullable();     // optional: rarely used in PK
            $table->char('country_code', 2)->default('PK');
            $table->text('delivery_instructions')->nullable(); // "near Faysal Bank, red gate"
            $table->boolean('is_default_shipping')->default(false);
            $table->boolean('is_default_billing')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_default_shipping'], 'addresses_user_default_ship_index');
            $table->index(['city', 'province'], 'addresses_city_province_index');
        });

        DB::statement("
            ALTER TABLE addresses ADD CONSTRAINT addresses_province_check
            CHECK (province IN ('punjab','sindh','kpk','balochistan','gilgit_baltistan','ajk','islamabad'))
        ");
    }

    public function down(): void { Schema::dropIfExists('addresses'); }
};
