<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The default blog taxonomy: one category every post falls under, and one
 * consistent author byline so every article is "published by" the same name.
 *
 * Both are looked up by a stable key (category slug / author email) by the
 * blog-post seeders and by the Filament form's default(), so new posts pick
 * them up automatically. This seeder also backfills any existing post that was
 * created before a category/author existed — it only fills NULLs, never
 * overwrites a choice the owner has already made in admin.
 *
 * DEPLOY-SAFE + idempotent: firstOrCreate by key, and the backfill is a
 * whereNull update.
 */
class BlogDefaultsSeeder extends Seeder
{
    public const CATEGORY_SLUG = 'herbal-care';

    // The standing byline is a NAMED HUMAN, not a corporate label. It was
    // 'editorial@glowhalal.com' / "Glow Halal Editorial" until 15 Aug 2026;
    // for YMYL herbal content a corporate byline is an E-E-A-T ceiling, and
    // the migration plan (§B4) forbids Admin/Team bylines. Existing posts were
    // reassigned by 2026_08_15_000300_byline_named_author; these constants
    // keep every FUTURE post (drip and admin default) on the same byline.
    public const AUTHOR_EMAIL = 'yousufmunir59@gmail.com';

    public const AUTHOR_NAME = 'Yousuf Munir';

    public function run(): void
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => self::CATEGORY_SLUG],
            [
                'name' => 'Herbal Care',
                'description' => 'Honest guides to herbal oils and skincare — uses, how-to, safety and prices in Pakistan.',
                'is_active' => true,
                'position' => 1,
            ],
        );

        // On production this row already exists (the owner's real account) and
        // firstOrCreate leaves it untouched. On a fresh database it is created
        // as a byline record with a random, unusable password — sign-in comes
        // from a password reset, never from a seeded credential.
        $author = User::firstOrCreate(
            ['email' => self::AUTHOR_EMAIL],
            [
                'name' => self::AUTHOR_NAME,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ],
        );

        // Backfill only what is missing, so admin choices are never clobbered.
        BlogPost::whereNull('blog_category_id')->update(['blog_category_id' => $category->id]);
        BlogPost::whereNull('author_id')->update(['author_id' => $author->id]);
    }
}
