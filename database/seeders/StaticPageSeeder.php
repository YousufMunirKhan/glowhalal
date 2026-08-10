<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Creates the legal and service pages the storefront navigation links to, so
 * those links resolve instead of 404ing.
 *
 * Bodies are deliberately left EMPTY. The page renders an honest "not written
 * yet" state and points the visitor at /contact. Seeding invented policy text
 * would be worse than an empty page: customers act on returns and privacy
 * terms, and so do regulators. The owner writes the real content in
 * Admin → Content → Pages.
 *
 * `is_system` is true so the admin blocks deletion — these slugs are referenced
 * directly in the footer markup.
 *
 * Idempotent: matches on slug, and will not overwrite a body once written.
 */
class StaticPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['slug' => 'shipping-returns', 'title' => 'Shipping & Returns', 'position' => 10],
            ['slug' => 'faq', 'title' => 'Frequently Asked Questions', 'position' => 20],
            ['slug' => 'privacy', 'title' => 'Privacy Policy', 'position' => 30],
            ['slug' => 'terms', 'title' => 'Terms & Conditions', 'position' => 40],
            // Linked from the homepage's "we have not published reviews yet"
            // section, which explains the PKR 500 first-50-reviewers offer.
            ['slug' => 'reviews', 'title' => 'How Our Reviews Work', 'position' => 50],
        ];

        foreach ($pages as $row) {
            $page = Page::withTrashed()->firstOrNew(['slug' => $row['slug']]);

            // Never clobber content the owner has already written.
            $hadContent = filled($page->content);

            $page->fill([
                'title' => $page->exists ? $page->title : $row['title'],
                'template' => 'default',
                'status' => 'published',
                'published_at' => $page->published_at ?? now(),
                'is_system' => true,
                'show_in_footer' => true,
                'show_in_header' => false,
                'position' => $row['position'],
            ]);

            if (! $hadContent) {
                $page->content = null;
            }

            if ($page->trashed()) {
                $page->restore();
            }

            $page->save();

            $state = $hadContent ? 'kept existing content' : 'empty — write it in the admin';
            $this->command?->info("  /{$row['slug']} — {$row['title']} ({$state})");
        }
    }
}
