<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()
                  ->constrained()->cascadeOnDelete()
                  ->comment('NULL = shared across all variants. Set = shade/size specific gallery.');
            $table->string('disk', 32)->default('public');
            $table->string('path', 255);
            $table->string('alt_text', 255)
                  ->comment('Required. Enforced at the Filament layer and by validation, not nullable here.');
            $table->string('mime_type', 64)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->json('conversions')->nullable()
                  ->comment('{"thumb":"...webp","md":"...webp","lg":"...webp"} written by GenerateImageConversions job.');
            $table->string('blurhash', 40)->nullable()
                  ->comment('LQIP placeholder. Prevents CLS, which is a Core Web Vitals ranking input.');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'position'], 'product_images_product_position_index');
            $table->index(['product_id', 'is_primary'], 'product_images_product_primary_index');
            $table->index('product_variant_id', 'product_images_variant_index');
        });
    }

    public function down(): void { Schema::dropIfExists('product_images'); }
};
