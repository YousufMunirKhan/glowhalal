<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140);
            $table->string('description', 500)->nullable();
            $table->string('image_path')->nullable();
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('posts_count')->default(0);
            $table->timestamps();

            $table->unique('slug', 'blog_categories_slug_unique');
            $table->index(['is_active', 'position'], 'blog_categories_active_position_index');
        });
    }

    public function down(): void { Schema::dropIfExists('blog_categories'); }
};
