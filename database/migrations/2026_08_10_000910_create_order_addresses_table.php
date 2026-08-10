<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12)->comment('shipping|billing');
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete()
                  ->comment('Provenance only. Never read for display — the snapshot below is authoritative.');
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 20);
            $table->string('alternate_phone', 20)->nullable();
            $table->string('line_1', 255);
            $table->string('line_2', 255)->nullable();
            $table->string('area', 120)->nullable();
            $table->string('city', 120);
            $table->string('province', 40);
            $table->string('postal_code', 12)->nullable();
            $table->char('country_code', 2)->default('PK');
            $table->text('delivery_instructions')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'type'], 'order_addresses_order_type_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('order_addresses'); }
};
