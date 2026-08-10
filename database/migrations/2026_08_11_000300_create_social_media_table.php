<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Media assets (images/videos) attached to a social post. alt_text is required
 * at the form level for accessibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('type', 20)->default('image')->comment('App\Enums\SocialMediaType: image|video');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['social_post_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media');
    }
};
