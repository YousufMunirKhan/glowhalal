<?php

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * Wire the blog → product ORDER CTA. The oil blog posts pull most of the site's
 * organic impressions (GSC: the "uses/benefits/price" post alone had 653), but
 * they had no product attached, so blog.show's order block never rendered and
 * that traffic never saw a buy button. This attaches the flagship 50 ml + 100 ml
 * to every oil-related post (EN + Roman-Urdu, matched by slug) so each shows the
 * image + price + "View & order" + "Order on WhatsApp" CTA. Idempotent
 * (syncWithoutDetaching); non-oil posts (shilajit, neem, sidr, cream) are left
 * alone on purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        $productIds = Product::whereIn('slug', ['herbal-skin-oil-50ml', 'herbal-skin-oil-100ml'])
            ->pluck('id')->all();

        if ($productIds === []) {
            return;
        }

        // Slug fragments that mark an oil post in either locale.
        $patterns = ['%hayat%', '%herbal-oil%', '%herbal-tel%', '%champi%', '%-ka-tel%', '%herbal-oils%'];

        $posts = BlogPost::where(function ($q) use ($patterns) {
            foreach ($patterns as $p) {
                $q->orWhere('slug', 'like', $p);
            }
        })->get();

        foreach ($posts as $post) {
            $post->products()->syncWithoutDetaching($productIds);
        }
    }

    public function down(): void
    {
        // Leaving the attachments in place is harmless; no rollback.
    }
};
