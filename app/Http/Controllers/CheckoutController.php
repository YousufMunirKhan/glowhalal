<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /** Session key holding the token of a Buy Now cart (see Livewire\Cart\AddToCart::buyNow). */
    public const DIRECT_SESSION_KEY = 'gh.checkout.direct';

    public function __invoke(Request $request, CartManager $carts): View|RedirectResponse
    {
        $direct = $request->boolean('direct');

        if ($direct && ! $request->session()->has(self::DIRECT_SESSION_KEY)) {
            return redirect()->route('cart.index');
        }

        if (! $direct) {
            $cart = $carts->current(createIfMissing: false);

            if (! $cart || $cart->items_count < 1) {
                return redirect()->route('cart.index');
            }
        }

        return view('checkout.index', ['direct' => $direct]);
    }
}
