<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url', 512)->nullable();
            $table->string('og_title', 180)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('og_type', 40)->nullable()->default('website');
            $table->string('twitter_card', 40)->nullable()->default('summary_large_image');
            $table->boolean('is_indexable')->default(true);
            $table->boolean('is_followable')->default(true);
            $table->json('structured_data_overrides')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id'], 'seo_metas_seoable_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('seo_metas'); }
};
