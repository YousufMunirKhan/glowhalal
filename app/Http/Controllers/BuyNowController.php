<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;

/**
 * "Buy now" from a listing/homepage card: adds the product's default variant to
 * a DETACHED cart (the shopper's existing bag is untouched) and sends them
 * straight to checkout — mirroring Livewire\Cart\AddToCart::buyNow(), but as a
 * plain POST so it works from the array-backed homepage cards that cannot mount
 * the model-based buy box.
 */
class BuyNowController extends Controller
{
    public function __invoke(Product $product, CartService $cart, CartManager $carts): RedirectResponse
    {
        $variant = $product->defaultVariant
            ?? $product->variants()->where('is_active', true)->orderBy('position')->first();

        if (! $variant) {
            return redirect()->route('products.show', $product);
        }

        // Mirror the Livewire buy box's guard (AddToCart::validatedVariant):
        // if the variant tracks stock, isn't backorderable and has none
        // available, maxFor() returns 0. Sending the shopper to checkout anyway
        // dead-ends them on an "insufficient stock" error, so bounce them back
        // to the product page — where the buy box already shows the stock state.
        if ($cart->maxFor($variant) < 1) {
            return redirect()
                ->route('products.show', $product)
                ->with('error', 'That item is currently out of stock.');
        }

        $direct = $carts->createDetached();
        $cart->add($variant, 1, $direct);
        session()->put(CheckoutController::DIRECT_SESSION_KEY, $direct->token);

        return redirect()->route('checkout.index', ['direct' => 1]);
    }
}
