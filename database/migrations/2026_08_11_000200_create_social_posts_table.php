<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The composer's master record. One social_post fans out to several
 * social_post_targets (one per platform). Publishing is MANUAL in Phase 0.
 *
 * Honesty gates live here as real columns, not just docs:
 *  - compliance_checked must be true before status can leave draft.
 *  - contains_sensitive_content (burns/cuts/skin-safety) forces safety_note.
 *  - uses_ugc forces a consented social_asset (validated in the resource).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Internal working title — never shown publicly.');
            $table->string('status', 20)->default('draft')
                  ->comment('App\Enums\SocialPostStatus: draft|needs_review|scheduled|posted|archived');
            $table->string('pillar', 30)->nullable()
                  ->comment('App\Enums\SocialPillar');
            $table->string('language', 20)->default('roman_urdu')
                  ->comment('App\Enums\ContentLanguage');

            $table->text('caption_base')->nullable()->comment('Default caption; per-platform overrides live on targets.');
            $table->json('hashtags')->nullable();
            $table->string('link_url')->nullable();
            $table->string('cta_type', 30)->default('none')
                  ->comment('App\Enums\SocialCtaType');

            $table->timestamp('scheduled_at')->nullable()->comment('Stored UTC; entered as PKT in the UI.');
            $table->timestamp('published_at')->nullable();

            // ---- Honesty / compliance -------------------------------------
            $table->boolean('contains_sensitive_content')->default(false)
                  ->comment('Burns / cuts / skin-safety content — forces a safety note.');
            $table->text('safety_note')->nullable();
            $table->boolean('compliance_checked')->default(false)
                  ->comment('True only when the whole honesty checklist is ticked.');
            $table->boolean('uses_ugc')->default(false);

            $table->foreignId('social_asset_id')->nullable()
                  ->constrained('social_assets')->nullOnDelete();
            $table->foreignId('created_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
