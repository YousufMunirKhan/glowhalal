<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bilingual foundation for the Journal — SEO anti-cannibalization.
 *
 * `locale`               'en' (root) | 'ur-Latn' (Roman Urdu under /ur-roman).
 *                        Roman Urdu is Latin script, so it is a distinct locale,
 *                        NOT a translation-file toggle.
 * `translation_group_id` UUID shared by every language version of the same post.
 *                        Members of one group are ALTERNATES of each other (each
 *                        keeps its own self-canonical); the group drives the
 *                        reciprocal hreflang cluster and the sitemap alternates.
 *
 * Backfill: every existing row is English and becomes its own single-member
 * group. A fresh UUID per row means each post is already a valid (lonely)
 * cluster; adding a Roman-Urdu sibling later is just an INSERT that reuses the
 * same translation_group_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('locale', 12)->default('en')->after('slug')
                  ->comment("Content locale: 'en' | 'ur-Latn' (Roman Urdu, Latin script → LTR).");
            $table->uuid('translation_group_id')->nullable()->after('locale')
                  ->comment('Shared across language versions of the same post. Drives hreflang + sitemap alternates.');

            $table->index('translation_group_id', 'blog_posts_translation_group_index');
            // A single language version per group: one 'en' + one 'ur-Latn', never two 'en'.
            $table->unique(['translation_group_id', 'locale'], 'blog_posts_group_locale_unique');
            // Listing/sitemap queries filter by locale + status + date.
            $table->index(['locale', 'status', 'published_at'], 'blog_posts_locale_status_index');
        });

        // Backfill existing rows: English, each its own fresh group.
        DB::table('blog_posts')
            ->select('id')
            ->orderBy('id')
            ->each(function ($row) {
                DB::table('blog_posts')
                    ->where('id', $row->id)
                    ->update([
                        'locale' => 'en',
                        'translation_group_id' => (string) Str::uuid(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique('blog_posts_group_locale_unique');
            $table->dropIndex('blog_posts_translation_group_index');
            $table->dropIndex('blog_posts_locale_status_index');
            $table->dropColumn(['locale', 'translation_group_id']);
        });
    }
};
