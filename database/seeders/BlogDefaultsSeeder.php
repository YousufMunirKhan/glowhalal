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

    public const AUTHOR_EMAIL = 'editorial@glowhalal.com';

    public const AUTHOR_NAME = 'Glow Halal Editorial';

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

        // The author is a byline record, not a login. It gets a random,
        // unusable password so no one can sign in as it.
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
