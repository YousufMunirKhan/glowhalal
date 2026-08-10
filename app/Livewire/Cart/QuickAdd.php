<?php

namespace App\Livewire\Cart;

use App\Http\Controllers\CheckoutController;
use App\Models\Product;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * One-click add from the catalogue grid.
 *
 * With one purchasable option this really is one click. With more than one it
 * opens a shade/size picker instead of silently adding a default — a customer
 * who receives the wrong shade refuses the COD delivery, and a refused COD
 * delivery is the most expensive failure mode this store has: the goods travel
 * twice and nothing is collected.
 */
class QuickAdd extends Component
{
    #[Locked]
    public int $productId;

    public bool $added = false;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
    }

    #[Computed]
    public function product(): Product
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
            'variants.inventory',
        ])->findOrFail($this->productId);
    }

    #[Computed]
    public function purchasableVariants()
    {
        return $this->product->variants->filter(fn ($v) => $v->is_in_stock)->values();
    }

    #[Computed]
    public function needsChoice(): bool
    {
        return $this->product->variants->count() > 1;
    }

    public function addVariant(int $variantId, CartService $cart): void
    {
        $variant = $this->product->variants->firstWhere('id', $variantId);

        abort_unless($variant !== null, 422);

        if (! $variant->is_in_stock) {
            $this->addError('variant', 'That option has just sold out.');

            return;
        }

        $cart->add($variant, 1);

        $this->added = true;

        $this->dispatch('cart-updated');
        $this->dispatch('added-to-bag', name: $this->product->name);

        // Consent-gated analytics (partials/tracking.blade.php) listens for this
        // browser event. `id` is the variant SKU — the same id used in the
        // product feeds — so GA4 / Meta events line up with the catalogue. No PII.
        $this->dispatch('gh-add-to-cart',
            id: $variant->sku,
            name: $this->product->name,
            price: (float) ($variant->price_amount?->toRupees() ?? 0),
            quantity: 1,
            currency: 'PKR',
        );
    }

    public function addDefault(CartService $cart): void
    {
        $variant = $this->purchasableVariants->first();

        if (! $variant) {
            $this->addError('variant', 'Out of stock.');

            return;
        }

        $this->addVariant($variant->id, $cart);
    }

    /**
     * Buy Now for a chosen variant. Mirrors AddToCart::buyNow — it writes into a
     * DETACHED cart (the gh_cart cookie is never touched), so the shopper's
     * existing bag is neither merged into nor cleared, then jumps to checkout.
     *
     * The multi-variant picker calls this per row, so a variant is always
     * chosen before Buy Now runs — the same "choose an option first" guard the
     * add path enforces, expressed through the UI rather than an error.
     */
    public function buyNow(int $variantId, CartService $cart, CartManager $carts)
    {
        $variant = $this->product->variants->firstWhere('id', $variantId);

        abort_unless($variant !== null, 422);

        if (! $variant->is_in_stock) {
            $this->addError('variant', 'That option has just sold out.');

            return null;
        }

        $direct = $carts->createDetached();

        $cart->add($variant, 1, $direct);

        session()->put(CheckoutController::DIRECT_SESSION_KEY, $direct->token);

        return $this->redirect(route('checkout.index', ['direct' => 1]), navigate: false);
    }

    /** Single-purchasable-option Buy Now — no picker needed. */
    public function buyNowDefault(CartService $cart, CartManager $carts)
    {
        $variant = $this->purchasableVariants->first();

        if (! $variant) {
            $this->addError('variant', 'Out of stock.');

            return null;
        }

        return $this->buyNow($variant->id, $cart, $carts);
    }

    public function render()
    {
        return view('livewire.cart.quick-add');
    }
}
