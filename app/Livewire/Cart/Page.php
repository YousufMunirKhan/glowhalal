<?php

namespace App\Livewire\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Cart\CartCalculator;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use App\Services\Cart\CartTotals;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * The cart page.
 *
 * Quantity changes update the line total, the cart total and the shipping
 * estimate through Livewire's normal round trip — no page reload, and no
 * hand-rolled JavaScript. The 300ms debounce is the difference between one
 * request and one request per keystroke; on a congested Pakistani cell that is
 * the difference between a usable cart and a broken one.
 */
class Page extends Component
{
    /** @var array<int, int> Keyed by cart item id. */
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantities();
    }

    #[Computed(persist: false)]
    public function cart(): ?Cart
    {
        return app(CartManager::class)->current(createIfMissing: false)?->load([
            'items.variant.product.primaryImage',
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

    public function updatedQuantities(mixed $value, string $key): void
    {
        $this->setQuantity((int) $key, (int) $value);
    }

    public function increment(int $itemId): void
    {
        $this->setQuantity($itemId, ($this->quantities[$itemId] ?? 1) + 1);
    }

    public function decrement(int $itemId): void
    {
        $this->setQuantity($itemId, ($this->quantities[$itemId] ?? 1) - 1);
    }

    public function remove(int $itemId): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        app(CartService::class)->remove($item);

        unset($this->quantities[$itemId]);

        $this->refreshView();
    }

    private function setQuantity(int $itemId, int $quantity): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        if ($quantity < 1) {
            $this->remove($itemId);

            return;
        }

        app(CartService::class)->updateQuantity($item, $quantity);

        $this->refreshView();
    }

    private function findItem(int $itemId): ?CartItem
    {
        $cart = $this->cart;

        return $cart
            ? $cart->items()->whereKey($itemId)->with('variant')->first()
            : null;
    }

    private function refreshView(): void
    {
        unset($this->cart, $this->totals);

        $this->syncQuantities();

        $this->dispatch('cart-updated');
    }

    /** Snap the inputs back to what the server actually stored — stock may have clamped it. */
    private function syncQuantities(): void
    {
        $cart = $this->cart;

        $this->quantities = $cart
            ? $cart->items->mapWithKeys(fn (CartItem $item) => [$item->id => $item->quantity])->all()
            : [];
    }

    public function render()
    {
        return view('livewire.cart.page');
    }
}
