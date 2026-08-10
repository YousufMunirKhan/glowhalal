<?php

namespace App\Filament\Resources\Products\Concerns;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;
use Illuminate\Support\Str;

/**
 * Lets a non-technical owner give a SIMPLE product one price and one stock
 * number on the Details tab, without ever opening the Variants tab.
 *
 * Under the hood a product always sells through a variant + inventory row —
 * cart, checkout and JSON-LD all read from there and must not change. So the
 * two "simple" form fields are virtual: they are stripped out of the product
 * payload before save, then projected onto a single DEFAULT variant and its
 * inventory item afterwards.
 *
 * Products that already have MORE THAN ONE active variant are real variant
 * products; the simple fields are hidden for them (see ProductForm) and this
 * trait leaves their variants completely alone.
 */
trait HandlesSimpleProductPricing
{
    /** Simple price/stock captured out of the form payload before the product saves. */
    protected array $simpleProductPricing = [];

    /** Pull the virtual fields out of $data so they never reach the products table. */
    protected function extractSimpleProductPricing(array $data): array
    {
        foreach (['simple_price', 'simple_stock'] as $key) {
            if (array_key_exists($key, $data)) {
                $this->simpleProductPricing[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        return $data;
    }

    /** Project the captured simple fields onto the default variant + inventory. */
    protected function syncSimpleProductPricing(): void
    {
        /** @var Product $product */
        $product = $this->getRecord();
        $product->refresh();

        // A real variant product manages itself in the Variants tab — hands off.
        if ($product->variants()->where('is_active', true)->count() > 1) {
            return;
        }

        // Fields were hidden or absent → nothing to do, leave existing data intact.
        if (! array_key_exists('simple_price', $this->simpleProductPricing)) {
            return;
        }

        $price = $this->simpleProductPricing['simple_price'] ?? null;   // Money|null
        $stock = $this->simpleProductPricing['simple_stock'] ?? null;   // int|string|null

        // No price yet (an in-progress draft) → do not fabricate a variant.
        if (! $price instanceof Money) {
            return;
        }

        $variant = $product->variants()
            ->orderByDesc('is_default')
            ->orderBy('position')
            ->first();

        if ($variant === null) {
            // Creating the variant fires ProductVariantObserver::created, which
            // seeds an inventory row at the default location and recalculates the
            // product's denormalised price columns.
            $variant = $product->variants()->create([
                'name' => 'Standard',
                'sku' => $this->generateDefaultSku($product),
                'price_amount' => $price,
                'is_default' => true,
                'is_active' => true,
                'track_inventory' => true,
                'allow_backorder' => false,
                'position' => 0,
            ]);
        } else {
            // forceFill still applies the MoneyCast; updating the price fires the
            // observer that refreshes price_min_amount / price_max_amount.
            $variant->forceFill([
                'price_amount' => $price,
                'is_default' => true,
                'is_active' => true,
            ])->save();
        }

        // Stock lives on the inventory item at the default location. Do not touch
        // quantity_available — it is a DB-generated column.
        $location = InventoryLocation::where('is_default', true)->first()
            ?? InventoryLocation::first();

        if ($location !== null && $stock !== null && $stock !== '') {
            $item = InventoryItem::firstOrNew([
                'product_variant_id' => $variant->getKey(),
                'inventory_location_id' => $location->getKey(),
            ]);

            $item->quantity_on_hand = max(0, (int) $stock);
            $item->save();   // fires InventoryItemObserver → products.total_stock
        }
    }

    /** A unique SKU derived from sku_prefix (falling back to the slug). */
    protected function generateDefaultSku(Product $product): string
    {
        $base = Str::upper(Str::slug($product->sku_prefix ?: ($product->slug ?: 'sku')));
        $base = $base !== '' ? $base : 'SKU';

        $candidate = $base;
        $suffix = 1;

        while (ProductVariant::withTrashed()->where('sku', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
