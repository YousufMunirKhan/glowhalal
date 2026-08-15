<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "Buy now" as a plain POST: adds a variant to a DETACHED cart (the shopper's
 * existing bag is untouched) and sends them straight to checkout — mirroring
 * Livewire\Cart\AddToCart::buyNow().
 *
 * Serves every Buy Now form on the site: the array-backed homepage cards
 * (which cannot mount the model-based buy box), the shop-card quick-add, and
 * the PDP buy box. Being a real form post rather than a wire:click, it is the
 * one purchase path that works identically with JavaScript on, off, or for a
 * non-browser client reading the plain HTML.
 *
 * Optional inputs: `variant_id` (must belong to this product and be active —
 * anything else falls back to the default variant rather than erroring, since
 * a stale form should degrade, not dead-end) and `quantity` (clamped to
 * [1, available stock]).
 */
class BuyNowController extends Controller
{
    public function __invoke(Request $request, Product $product, CartService $cart, CartManager $carts): RedirectResponse
    {
        $variant = null;

        if ($request->filled('variant_id')) {
            $variant = $product->variants()
                ->where('is_active', true)
                ->whereKey($request->input('variant_id'))
                ->first();
        }

        $variant ??= $product->defaultVariant
            ?? $product->variants()->where('is_active', true)->orderBy('position')->first();

        if (! $variant) {
            return redirect()->route('products.show', $product);
        }

        // Mirror the Livewire buy box's guard (AddToCart::validatedVariant):
        // if the variant tracks stock, isn't backorderable and has none
        // available, maxFor() returns 0. Sending the shopper to checkout anyway
        // dead-ends them on an "insufficient stock" error, so bounce them back
        // to the product page — where the buy box already shows the stock state.
        $max = $cart->maxFor($variant);

        if ($max < 1) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', 'That item is currently out of stock.');
        }

        // Clamp rather than reject: asking for 7 when 5 remain buys 5, which
        // checkout's summary then shows before anything is charged.
        $quantity = min(max(1, (int) $request->input('quantity', 1)), $max);

        $direct = $carts->createDetached();
        $cart->add($variant, $quantity, $direct);
        session()->put(CheckoutController::DIRECT_SESSION_KEY, $direct->token);

        return redirect()->route('checkout.index', ['direct' => 1]);
    }
}
