<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('position')->default(0)
                  ->comment('INCI declaration order — descending concentration. Order is regulatory, preserve it.');
            $table->decimal('concentration_percent', 5, 2)->nullable();
            $table->boolean('is_key_ingredient')->default(false)->comment('Featured on the product page.');
            $table->string('source_note', 255)->nullable()
                  ->comment('Resolves ingredients.halal_status = depends_on_source, e.g. "palm-derived glycerin".');
            $table->string('resolved_halal_status', 24)->nullable()
                  ->comment('Per-product override of the ingredient default, once the source is verified.');
            $table->timestamps();

            $table->primary(['product_id', 'ingredient_id']);
            $table->index(['product_id', 'position'], 'ingredient_product_product_position_index');
            $table->index('ingredient_id', 'ingredient_product_ingredient_index');
        });
    }

    public function down(): void { Schema::dropIfExists('ingredient_product'); }
};
