<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value', 120);               // 'Nude Rose'
            $table->string('slug', 140);                // 'nude-rose'
            $table->char('hex_color', 7)->nullable();   // '#C08081' — swatch, only for type=color
            $table->string('swatch_image_path')->nullable()
                  ->comment('For multi-tone or glitter shades a flat hex is not enough.');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'slug'], 'attribute_values_attribute_slug_unique');
            $table->index(['attribute_id', 'position'], 'attribute_values_attribute_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('attribute_values'); }
};
