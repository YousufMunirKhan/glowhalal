<?php

namespace App\Observers;

use App\Enums\ProductStatus;
use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product): void
    {
        // Publishing without an explicit date stamps "now" — a published product
        // with a null published_at would otherwise sort to the bottom of every feed.
        if ($product->status === ProductStatus::Active && $product->published_at === null) {
            $product->published_at = now();
        }
    }

    public function created(Product $product): void
    {
        $product->seoMeta()->firstOrCreate([], [
            'meta_title' => $product->name,
            'meta_description' => str($product->short_description ?? '')->limit(160)->toString() ?: null,
            'og_type' => 'product',
            'is_indexable' => true,
            'is_followable' => true,
        ]);
    }

    public function updated(Product $product): void
    {
        if ($product->wasChanged('slug')) {
            $product->recordSlugHistory($product->getOriginal('slug'));
        }
    }
}
