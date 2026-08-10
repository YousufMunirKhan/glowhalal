<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "shop this article"
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_post_product', function (Blueprint $table) {
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['blog_post_id', 'product_id']);
            $table->index('product_id', 'blog_post_product_product_index');
        });
    }

    public function down(): void { Schema::dropIfExists('blog_post_product'); }
};
