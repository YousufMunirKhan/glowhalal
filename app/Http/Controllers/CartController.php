<?php

namespace App\Http\Controllers;

use App\Services\Cart\CartManager;
use Illuminate\Contracts\View\View;

class CartController extends Controller
{
    public function __invoke(CartManager $carts): View
    {
        // createIfMissing: false — otherwise every crawler hit and every stray
        // request writes a `carts` row, and abandoned-cart reporting becomes
        // meaningless noise (architecture §5.5).
        $cart = $carts->current(createIfMissing: false);

        return view('cart.index', [
            'cartCount' => (int) ($cart?->items_count ?? 0),
        ]);
    }
}
