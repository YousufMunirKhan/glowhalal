<?php

use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;

/**
 * Roman-Urdu blog posts were written before the Roman-Urdu PDPs existed, so
 * their in-body product links point at the ENGLISH product pages — a UR reader
 * clicking "order karein" landed on an English page, and the UR PDPs received
 * zero internal link equity from the posts that should feed them.
 *
 * Rewrites those hrefs to the UR PDPs — but ONLY when the UR product page
 * actually exists and is publishable (otherwise the link would 404), and only
 * inside ur-Latn posts. English posts keep linking English PDPs. Idempotent:
 * once rewritten, the search string no longer matches.
 */
return new class extends Migration
{
    private const MAP = [
        'herbal-skin-oil-50ml' => '/ur-roman/products/lookman-e-hayat-tel-50ml',
        'herbal-skin-oil-100ml' => '/ur-roman/products/lookman-e-hayat-tel-100ml',
    ];

    public function up(): void
    {
        foreach (self::MAP as $enSlug => $urPath) {
            $product = Product::query()->where('slug', $enSlug)->first();

            // Guard: never create a link to a UR page that does not exist.
            if (! $product || ! $product->hasRomanUrdu()
                || '/ur-roman/products/'.$product->slug_ur !== $urPath) {
                continue;
            }

            BlogPost::withoutGlobalScopes()
                ->where('locale', 'ur-Latn')
                ->where('content', 'like', '%/products/'.$enSlug.'%')
                ->get(['id', 'content'])
                ->each(function (BlogPost $post) use ($enSlug, $urPath) {
                    BlogPost::withoutEvents(fn () => $post->forceFill([
                        'content' => str_replace('/products/'.$enSlug, $urPath, $post->content),
                    ])->save());
                });
        }
    }

    public function down(): void
    {
        foreach (self::MAP as $enSlug => $urPath) {
            BlogPost::withoutGlobalScopes()
                ->where('locale', 'ur-Latn')
                ->where('content', 'like', '%'.$urPath.'%')
                ->get(['id', 'content'])
                ->each(function (BlogPost $post) use ($enSlug, $urPath) {
                    BlogPost::withoutEvents(fn () => $post->forceFill([
                        'content' => str_replace($urPath, '/products/'.$enSlug, $post->content),
                    ])->save());
                });
        }
    }
};
