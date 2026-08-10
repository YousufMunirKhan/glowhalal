<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Architecture §5.6 — CartService mutates, CartCalculator derives.
 *
 * Stock is deliberately NOT reserved here. Reserving at add-to-cart lets a
 * single abandoned tab hold the last unit hostage; reservation happens inside
 * the order transaction (§5.7).
 */
final class CartService
{
    public function __construct(
        private readonly CartManager $carts,
        private readonly CartCalculator $calculator,
    ) {}

    public function add(ProductVariant $variant, int $quantity = 1, ?Cart $cart = null): CartItem
    {
        $cart ??= $this->carts->current();
        $quantity = max(1, $quantity);

        $item = DB::transaction(function () use ($cart, $variant, $quantity) {
            $existing = $cart->items()->where('product_variant_id', $variant->id)->first();

            $target = $existing
                ? $existing->quantity + $quantity
                : $quantity;

            $target = $this->clamp($variant, $target);

            if ($existing) {
                $existing->forceFill([
                    'quantity' => $target,
                    'unit_price_amount' => $variant->price_amount,
                    'line_total_amount' => $variant->price_amount->times($target),
                ])->save();

                return $existing;
            }

            $variant->loadMissing(['product.primaryImage', 'attributeValues.attribute']);

            return $cart->items()->create([
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => $target,
                'unit_price_amount' => $variant->price_amount,
                'line_total_amount' => $variant->price_amount->times($target),
                'name_snapshot' => $variant->product?->name ?? $variant->sku,
                'options_snapshot' => $variant->optionsSnapshot(),
                'image_path_snapshot' => $variant->product?->primaryImage?->path,
            ]);
        });

        $this->calculator->recalculate($cart->refresh());

        return $item;
    }

    public function updateQuantity(CartItem $item, int $quantity): CartItem
    {
        if ($quantity < 1) {
            $this->remove($item);

            return $item;
        }

        $variant = $item->variant;

        $item->forceFill([
            'quantity' => $variant ? $this->clamp($variant, $quantity) : $quantity,
        ]);

        $item->line_total_amount = $item->unit_price_amount->times($item->quantity);
        $item->save();

        $this->calculator->recalculate($item->cart->refresh());

        return $item;
    }

    public function remove(CartItem $item): void
    {
        $cart = $item->cart;

        $item->delete();

        if ($cart) {
            $this->calculator->recalculate($cart->refresh());
        }
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();

        $this->calculator->recalculate($cart->refresh());
    }

    /** Never let a line exceed live stock or the per-order ceiling. */
    public function clamp(ProductVariant $variant, int $quantity): int
    {
        $ceiling = $this->maxFor($variant);

        return max(1, min($quantity, max(1, $ceiling)));
    }

    public function maxFor(ProductVariant $variant): int
    {
        if (! $variant->track_inventory || $variant->allow_backorder) {
            return min($variant->max_per_order ?? 99, 99);
        }

        return min(
            $variant->max_per_order ?? 99,
            max(0, $variant->available_quantity),
            99,
        );
    }
}
