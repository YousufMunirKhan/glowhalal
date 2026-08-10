<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;

class InventoryItemObserver
{
    public function saved(InventoryItem $item): void
    {
        $this->recalculateProductStock($item);
    }

    public function deleted(InventoryItem $item): void
    {
        $this->recalculateProductStock($item);
    }

    /** Maintains products.total_stock — the column every listing page filters on. */
    private function recalculateProductStock(InventoryItem $item): void
    {
        $variant = ProductVariant::withTrashed()->find($item->product_variant_id);

        if (! $variant || ! ($product = Product::withTrashed()->find($variant->product_id))) {
            return;
        }

        $total = (int) InventoryItem::query()
            ->whereIn('product_variant_id', ProductVariant::where('product_id', $product->getKey())->select('id'))
            ->sum('quantity_available');

        $product->forceFill(['total_stock' => max(0, $total)])->saveQuietly();
    }
}
