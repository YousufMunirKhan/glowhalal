<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Seo\ProductOffer;
use App\Support\Seo\SchemaGraph;
use Illuminate\Contracts\View\View;

/**
 * The product detail page. Everything indexable — name, price, the full INCI
 * list, the exclusion list, the description — is server-rendered Blade. The
 * only Livewire on the page is the buy box, and its price is rendered by the
 * server on first paint from the same ProductOffer that feeds the JSON-LD, so
 * the visible price and `Product.offers.price` cannot disagree.
 */
class ProductController extends Controller
{
    public function show(Product $product): View
    {
        $product->load([
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
            'variants.inventory',
            'variants.attributeValues.attribute',
            'primaryCategory',
            'categories',
            'images',
            'ingredients',
            'verifiedFreeFromAttributes',
            'halalProfile',
            'seoMeta',
        ]);

        $offer = ProductOffer::forProduct($product);

        $category = $product->primaryCategory;
        $canonical = route('products.show', $product->slug);

        $trail = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Shop', 'url' => route('shop.index')],
        ];

        if ($category) {
            foreach ($category->ancestorTrail() as $ancestor) {
                $trail[] = ['name' => $ancestor->name, 'url' => route('shop.category', $ancestor->slug)];
            }

            $trail[] = ['name' => $category->name, 'url' => route('shop.category', $category->slug)];
        }

        $trail[] = ['name' => $product->name, 'url' => $canonical];

        $metaTitle = $product->resolvedMetaTitle() ?? $product->name;
        $metaDescription = \Illuminate\Support\Str::limit(
            trim(strip_tags((string) ($product->resolvedMetaDescription() ?? ''))),
            158,
            '',
        ) ?: null;

        $graph = SchemaGraph::make()
            ->organization()
            ->webPage($canonical, $product->name, $metaDescription)
            ->breadcrumbs($trail)
            ->product($product, $offer, $category)
            // Same Q&As the page renders visibly below — never an invisible set.
            ->faqPage($canonical, $product->faqs ?? []);

        // Out of stock keeps its URL, stays index,follow, and reports
        // availability honestly (seo.md §2.6). Never 404 or noindex a
        // temporarily sold-out product — the ranking is re-earned from scratch.
        return view('products.show', [
            'product' => $product,
            'offer' => $offer,
            'category' => $category,
            'trail' => $trail,
            'canonical' => $canonical,
            'robots' => $product->isIndexable() ? 'index,follow' : 'noindex,follow',
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'schema' => $graph->toJson(),
        ]);
    }
}
