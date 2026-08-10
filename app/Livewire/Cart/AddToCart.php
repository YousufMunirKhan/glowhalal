<?php

namespace App\Livewire\Cart;

use App\Http\Controllers\CheckoutController;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The product-page buy box (architecture §5.5).
 *
 * The price rendered here is server-rendered on first paint from the default
 * variant — the same variant `ProductOffer::forProduct()` feeds to the JSON-LD
 * — so the visible price and `Product.offers.price` agree in the initial HTML.
 * It is deliberately NOT a `wire:text` binding, which renders empty
 * server-side and would silently delete the price for every crawler.
 */
class AddToCart extends Component
{
    /**
     * Public Livewire properties round-trip through the browser and are
     * attacker-modifiable. Without #[Locked] a visitor could repoint this
     * component at any product in the catalogue.
     */
    #[Locked]
    public int $productId;

    public ?int $variantId = null;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
        $this->variantId = $product->defaultVariant?->id
            ?? $product->variants->firstWhere('is_active', true)?->id;
    }

    #[Computed]
    public function product(): Product
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
            'variants.inventory',
            'variants.attributeValues.attribute',
        ])->findOrFail($this->productId);
    }

    #[Computed]
    public function variant(): ?ProductVariant
    {
        return $this->variantId
            ? $this->product->variants->firstWhere('id', $this->variantId)
            : null;
    }

    #[Computed]
    public function maxQuantity(): int
    {
        $variant = $this->variant;

        return $variant ? app(CartService::class)->maxFor($variant) : 0;
    }

    public function selectVariant(int $variantId): void
    {
        // Defence in depth: only variants belonging to THIS product are selectable.
        abort_unless($this->product->variants->contains('id', $variantId), 422);

        $this->variantId = $variantId;
        $this->quantity = 1;

        // Bust the computed cache, or the stock check reads the previous shade.
        unset($this->variant, $this->maxQuantity);
    }

    public function incrementQuantity(): void
    {
        $this->quantity = min($this->quantity + 1, max(1, $this->maxQuantity));
    }

    public function decrementQuantity(): void
    {
        $this->quantity = max(1, $this->quantity - 1);
    }

    public function add(CartService $cart): void
    {
        $variant = $this->validatedVariant();

        if (! $variant) {
            return;
        }

        $cart->add($variant, $this->quantity);

        $this->dispatch('cart-updated');
        $this->dispatch('added-to-bag', name: $this->product->name);

        // Consent-gated analytics (partials/tracking.blade.php) listens for this
        // browser event. `id` is the variant SKU — the same id used in the
        // product feeds — so GA4 / Meta events line up with the catalogue. No PII.
        $this->dispatch('gh-add-to-cart',
            id: $variant->sku,
            name: $this->product->name,
            price: (float) ($variant->price_amount?->toRupees() ?? 0),
            quantity: $this->quantity,
            currency: 'PKR',
        );
    }

    /**
     * Buy Now skips the bag entirely. It writes into a DETACHED cart — the
     * gh_cart cookie is never touched — so an existing basket is neither
     * merged into nor cleared, which is exactly what the owner asked for.
     */
    public function buyNow(CartService $cart, CartManager $carts)
    {
        $variant = $this->validatedVariant();

        if (! $variant) {
            return null;
        }

        $direct = $carts->createDetached();

        $cart->add($variant, $this->quantity, $direct);

        session()->put(CheckoutController::DIRECT_SESSION_KEY, $direct->token);

        return $this->redirect(route('checkout.index', ['direct' => 1]), navigate: false);
    }

    private function validatedVariant(): ?ProductVariant
    {
        $this->resetErrorBag();

        $variant = $this->variant;

        if (! $variant) {
            $this->addError('variantId', 'Choose an option first.');

            return null;
        }

        $max = $this->maxQuantity;

        if ($max < 1) {
            $this->addError('quantity', 'This option is out of stock.');

            return null;
        }

        if ($this->quantity > $max) {
            $this->quantity = $max;
            $this->addError('quantity', "Only {$max} left in stock.");

            return null;
        }

        $this->quantity = max(1, $this->quantity);

        return $variant;
    }

    public function render()
    {
        return view('livewire.cart.add-to-cart');
    }
}
