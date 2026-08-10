<?php

namespace App\Livewire\Cart;

use App\Services\Cart\CartManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The header cart control.
 *
 * Fixes the hardcoded-zero badge: it reads the REAL item count from the active
 * cart on first paint (server-rendered, so the badge is correct before any JS
 * runs) and re-reads it whenever a 'cart-updated' event fires after an add or
 * remove. It is also the control that opens the slide-in drawer — clicking it
 * dispatches the same browser event the drawer listens for.
 *
 * createIfMissing:false so a crawler hit never writes an empty `carts` row.
 */
class CartBadge extends Component
{
    #[Computed(persist: false)]
    public function count(): int
    {
        return (int) (app(CartManager::class)->current(createIfMissing: false)?->items_count ?? 0);
    }

    #[On('cart-updated')]
    public function onCartUpdated(): void
    {
        unset($this->count);
    }

    public function render()
    {
        return view('livewire.cart.cart-badge');
    }
}
