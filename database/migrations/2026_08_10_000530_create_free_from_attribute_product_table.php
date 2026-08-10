<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('free_from_attribute_product', function (Blueprint $table) {
            $table->foreignId('free_from_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('evidence_note', 500)->nullable()
                  ->comment('"Confirmed with supplier, email 2026-07-14" / "Derived from full INCI review".');
            $table->timestamps();

            $table->primary(['free_from_attribute_id', 'product_id'], 'ffap_primary');
            $table->index(['product_id', 'is_verified'], 'ffap_product_verified_index');
            $table->index(['free_from_attribute_id', 'is_verified'], 'ffap_attribute_verified_index');
        });
    }

    public function down(): void { Schema::dropIfExists('free_from_attribute_product'); }
};
