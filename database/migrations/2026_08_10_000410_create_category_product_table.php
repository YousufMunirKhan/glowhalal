<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);

            $table->primary(['category_id', 'product_id']);
            $table->index(['product_id'], 'category_product_product_index');
            $table->index(['category_id', 'position'], 'category_product_category_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('category_product'); }
};
