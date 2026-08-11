<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Every seeder below is idempotent and safe to re-run against a live database.
 *
 * Deliberately NOT using WithoutModelEvents: the observers are what maintain
 * `products.price_min_amount`, `products.total_stock` and
 * `product_halal_profiles.is_certified`, so suppressing them would seed data
 * that is internally inconsistent from the first row.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always — real, production content. Order matters: reference/roles
        // first, then the owner's real products (self-sufficient), then redirects.
        $this->call([
            RoleSeeder::class,
            HalalReferenceSeeder::class,
            StaticPageSeeder::class,
            LegalPagesSeeder::class,
            OwnerProductSeeder::class,
            LookmanBlogSeeder::class,
            BilingualBlogSeeder::class,
            WinnableBlogSeeder::class,
            WinnableBlogUrduSeeder::class,
            // Catalogue-expansion drip: 8 posts (4 EN + 4 Roman Urdu), one
            // going live per day at 06:00 PKT via future published_at.
            CatalogueExpansionBlogSeeder::class,
            // After the posts, so it backfills category + author onto every one.
            BlogDefaultsSeeder::class,
            RedirectSeeder::class,
        ]);

        // DEMO / SAMPLE DATA — LOCAL ONLY. This is where the placeholder demo
        // products, demo orders and demo blog content come from; they must NEVER
        // seed into production, which is meant to ship with only the two real
        // Lookman-e-Hayat oils.
        if (app()->environment('local')) {
            $this->call([
                CatalogueDemoSeeder::class,
                SalesDemoSeeder::class,
            ]);

            foreach (['Database\Seeders\ContentDemoSeeder', 'Database\Seeders\BlogDemoSeeder'] as $optional) {
                if (class_exists($optional)) {
                    $this->call($optional);
                }
            }
        }
    }
}
