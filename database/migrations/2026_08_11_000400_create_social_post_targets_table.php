<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per platform a post is planned for. This is the manual publishing
 * checklist: the owner copies the caption, opens the app, posts by hand, then
 * flips status to posted_manually and (optionally) records the live URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_post_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->string('platform', 30)->comment('App\Enums\SocialPlatform');
            $table->text('caption_override')->nullable();
            $table->string('status', 20)->default('pending')
                  ->comment('App\Enums\SocialTargetStatus: pending|scheduled|posted_manually|skipped');
            $table->string('external_url')->nullable()->comment('Live URL of the manually published post.');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['social_post_id', 'platform']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_post_targets');
    }
};
