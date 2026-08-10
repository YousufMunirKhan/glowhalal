<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Cart\CartCalculator;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use App\Services\Cart\CartTotals;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The slide-in cart drawer.
 *
 * Placed ONCE in the layout, so it is present on every page. It renders the
 * current cart's line items server-side, opens when 'added-to-bag' fires (or
 * when the header cart control dispatches 'open-cart-drawer'), and refreshes on
 * 'cart-updated'. Open/close animation is Alpine; the mutations are Livewire.
 *
 * Quantity and remove go through CartService — the same service the cart page
 * uses — and every mutation re-dispatches 'cart-updated' so the header badge
 * and any open cart page stay in step.
 */
class Drawer extends Component
{
    #[Computed(persist: false)]
    public function cart(): ?Cart
    {
        return app(CartManager::class)->current(createIfMissing: false)?->load([
            'items.variant.product',
            'items.variant.inventory',
        ]);
    }

    #[Computed(persist: false)]
    public function totals(): CartTotals
    {
        $cart = $this->cart;

        return $cart
            ? app(CartCalculator::class)->recalculate($cart)
            : CartTotals::empty();
    }

    #[On('added-to-bag')]
    #[On('cart-updated')]
    public function onCartUpdated(): void
    {
        // A no-op body is enough to force a re-render; the computed caches are
        // reset so the fresh cart is read.
        unset($this->cart, $this->totals);
    }

    public function increment(int $itemId): void
    {
        $this->changeQuantity($itemId, +1);
    }

    public function decrement(int $itemId): void
    {
        $this->changeQuantity($itemId, -1);
    }

    public function removeItem(int $itemId): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        app(CartService::class)->remove($item);

        unset($this->cart, $this->totals);

        // Keep the header badge and the cart page (if open) in step.
        $this->dispatch('cart-updated');
    }

    private function changeQuantity(int $itemId, int $delta): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        app(CartService::class)->updateQuantity($item, $item->quantity + $delta);

        unset($this->cart, $this->totals);

        $this->dispatch('cart-updated');
    }

    private function findItem(int $itemId): ?CartItem
    {
        $cart = $this->cart;

        return $cart
            ? $cart->items()->whereKey($itemId)->with('variant')->first()
            : null;
    }

    public function render()
    {
        return view('livewire.cart.drawer');
    }
}
