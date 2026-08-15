<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the corporate "Glow Halal Editorial" byline with a named human.
 *
 * Every published post (19 on production) was authored by the Editorial
 * pseudo-user, so every article rendered "By Glow Halal Editorial" and emitted
 * a Person node of that name in BlogPosting.author. For YMYL herbal content a
 * corporate byline is a hard E-E-A-T ceiling, and the site's own migration
 * plan (seo-migration-and-content-plan.md §B4) mandates a real named author —
 * "no 'Admin'/'Team' bylines".
 *
 * The named human is the owner, whose user row already exists. Posts are
 * reassigned to that row and the display name's capitalisation is fixed
 * ("yousuf munir" → "Yousuf Munir") — it is a public byline now, not just a
 * login label. Both rows are looked up by email, never by id: ids differ
 * between the production DB and local snapshots.
 *
 * BlogDefaultsSeeder and the Filament form default are updated in the same
 * commit, so future drip posts and admin-created posts get the same byline
 * and the Editorial user cannot silently reacquire authorship. The Editorial
 * user row itself is kept — deleting a user would null author_id on anything
 * still pointing at it, and it costs nothing to keep.
 */
return new class extends Migration
{
    private const OWNER_EMAIL = 'yousufmunir59@gmail.com';

    private const EDITORIAL_EMAIL = 'editorial@glowhalal.com';

    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('blog_posts')) {
            return;
        }

        $owner = DB::table('users')->where('email', self::OWNER_EMAIL)->first();

        if (! $owner) {
            // Nothing sensible to reassign to on this database (e.g. a stripped
            // local snapshot). Skipping is safe: the seeder now creates the
            // owner byline on the next seed, and this migration is a no-op that
            // can be re-run logically via the seeder's backfill.
            return;
        }

        if ($owner->name === 'yousuf munir') {
            DB::table('users')->where('id', $owner->id)->update(['name' => 'Yousuf Munir']);
        }

        $editorialId = DB::table('users')->where('email', self::EDITORIAL_EMAIL)->value('id');

        if ($editorialId) {
            DB::table('blog_posts')
                ->where('author_id', $editorialId)
                ->update(['author_id' => $owner->id, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('blog_posts')) {
            return;
        }

        $ownerId = DB::table('users')->where('email', self::OWNER_EMAIL)->value('id');
        $editorialId = DB::table('users')->where('email', self::EDITORIAL_EMAIL)->value('id');

        // Coarse by necessity: any post the owner authored AFTER this migration
        // also moves back to Editorial. Acceptable for a rollback path that
        // exists for the deploy window only.
        if ($ownerId && $editorialId) {
            DB::table('blog_posts')
                ->where('author_id', $ownerId)
                ->update(['author_id' => $editorialId, 'updated_at' => now()]);
        }
    }
};
