<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40);                 // 'shade', 'size', 'finish'
            $table->string('name', 80);                 // 'Shade'
            $table->string('type', 20)->default('select'); // App\Enums\AttributeType: select|color|size|text
            $table->boolean('is_variant_defining')->default(true)
                  ->comment('False = descriptive only, does not create variant permutations.');
            $table->boolean('is_filterable')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique('code', 'attributes_code_unique');
            $table->index(['is_filterable', 'position'], 'attributes_filterable_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('attributes'); }
};
