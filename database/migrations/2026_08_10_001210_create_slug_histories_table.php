<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slug_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('sluggable');
            $table->string('slug', 240);
            $table->string('model_type_key', 40)->comment('product|category|blog_post|page — for URL reconstruction.');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['sluggable_type', 'slug'], 'slug_histories_type_slug_unique');
            $table->index(['sluggable_type', 'sluggable_id'], 'slug_histories_model_index');
        });
    }

    public function down(): void { Schema::dropIfExists('slug_histories'); }
};
