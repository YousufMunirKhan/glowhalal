<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    public function created(ProductVariant $variant): void
    {
        $this->ensureInventoryItem($variant);
        $this->enforceSingleDefault($variant);
        $this->recalculateProductPrices($variant->product_id);
    }

    public function updated(ProductVariant $variant): void
    {
        $this->enforceSingleDefault($variant);

        if ($variant->wasChanged(['price_amount', 'compare_at_amount', 'is_active'])) {
            $this->recalculateProductPrices($variant->product_id);
        }
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->recalculateProductPrices($variant->product_id);
    }

    public function restored(ProductVariant $variant): void
    {
        $this->recalculateProductPrices($variant->product_id);
    }

    /** Exactly one default variant per product. */
    private function enforceSingleDefault(ProductVariant $variant): void
    {
        if (! $variant->is_default) {
            // If the product now has no default at all, promote the lowest-positioned active variant.
            $hasDefault = ProductVariant::where('product_id', $variant->product_id)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                ProductVariant::where('product_id', $variant->product_id)
                    ->where('is_active', true)
                    ->orderBy('position')
                    ->limit(1)
                    ->update(['is_default' => true]);
            }

            return;
        }

        ProductVariant::where('product_id', $variant->product_id)
            ->whereKeyNot($variant->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }

    private function ensureInventoryItem(ProductVariant $variant): void
    {
        $location = InventoryLocation::where('is_default', true)->first()
            ?? InventoryLocation::first();

        if (! $location) {
            return;
        }

        InventoryItem::firstOrCreate(
            ['product_variant_id' => $variant->getKey(), 'inventory_location_id' => $location->getKey()],
            ['quantity_on_hand' => 0, 'quantity_reserved' => 0, 'reorder_level' => 5, 'reorder_quantity' => 20],
        );
    }

    /** Maintains products.price_min_amount / price_max_amount / compare_at_max_amount. */
    private function recalculateProductPrices(?int $productId): void
    {
        if (! $productId || ! ($product = Product::withTrashed()->find($productId))) {
            return;
        }

        $aggregate = ProductVariant::where('product_id', $productId)
            ->where('is_active', true)
            ->selectRaw('MIN(price_amount) AS min_price, MAX(price_amount) AS max_price, MAX(compare_at_amount) AS max_compare')
            ->first();

        $product->forceFill([
            'price_min_amount' => (int) ($aggregate?->min_price ?? 0),
            'price_max_amount' => (int) ($aggregate?->max_price ?? 0),
            'compare_at_max_amount' => $aggregate?->max_compare !== null ? (int) $aggregate->max_compare : null,
        ])->saveQuietly();
    }
}
