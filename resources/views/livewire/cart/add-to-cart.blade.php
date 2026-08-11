{{--
  Product-page buy box.

  The price below is ordinary server-rendered text for the selected variant,
  which on first paint is the default variant — the same one the JSON-LD offer
  describes. It is NOT `wire:text`: a `wire:text` price renders as an empty
  element in the initial HTML, which deletes the price for every crawler and
  puts the visible page out of step with `Product.offers.price` (seo.md §8.12).
  When a shopper picks another shade the price re-renders on the server, so the
  two can never drift apart.
--}}

@php
    $variant = $this->variant;
    $variants = $this->product->variants;
    $max = $this->maxQuantity;
    $hasChoice = $variants->count() > 1;
@endphp

<div class="flex flex-col gap-6">

    <div>
        <p class="text-price tabular-nums text-text-primary" data-price>
            {{ $variant?->price_amount?->format() ?? '—' }}
        </p>

        @if ($variant?->compare_at_amount && $variant->compare_at_amount->minorUnits > $variant->price_amount->minorUnits)
            <p class="mt-1 text-meta text-text-secondary">
                <s class="tabular-nums">{{ $variant->compare_at_amount->format() }}</s>
                <span class="ms-2 font-semibold text-text-gold">
                    {{ $variant->compare_at_amount->minus($variant->price_amount)->format() }} off
                </span>
            </p>
        @endif

        <p class="mt-2 text-meta text-text-secondary">
            @if ($max > 0)
                {{-- "@if" was glued to a word ("stock@if"); Blade does not
                     compile a directive immediately preceded by a word
                     character, so it printed the raw code. Compute the low-stock
                     suffix in PHP and echo it instead. --}}
                @php $lowStock = $variant && $variant->track_inventory && $max <= 5; @endphp
                In stock{{ $lowStock ? ", only {$max} left" : '' }}.
                Cash on Delivery available.
            @else
                Out of stock. This page stays up — the formula has not changed.
            @endif
        </p>
    </div>

    @if ($hasChoice)
        <fieldset>
            <legend class="text-overline uppercase text-text-gold">
                {{ $variants->first()?->name && str_contains(strtolower($variants->first()->name), 'ml') ? 'Size' : 'Shade' }}
            </legend>

            <div class="mt-3 flex flex-wrap gap-2" role="radiogroup" aria-label="Choose an option">
                @foreach ($variants as $option)
                    <button type="button"
                        wire:key="variant-{{ $option->id }}"
                        wire:click="selectVariant({{ $option->id }})"
                        role="radio"
                        aria-checked="{{ $variantId === $option->id ? 'true' : 'false' }}"
                        @disabled(! $option->is_in_stock)
                        @class([
                            'inline-flex min-h-11 items-center gap-2 rounded-sm border px-4 text-meta font-semibold',
                            'transition-[background-color,border-color] duration-[var(--motion-fast)] ease-standard',
                            'border-text-primary bg-surface-inverse text-text-on-inverse' => $variantId === $option->id,
                            'border-border-subtle text-text-primary hover:bg-surface-sunken' => $variantId !== $option->id && $option->is_in_stock,
                            'border-border-subtle text-text-muted line-through' => ! $option->is_in_stock,
                        ])>
                        {{ $option->name ?? $option->sku }}
                    </button>
                @endforeach
            </div>

            @error('variantId')
                <p class="mt-2 text-meta text-danger-700">{{ $message }}</p>
            @enderror
        </fieldset>
    @endif

    <div class="flex flex-wrap items-end gap-4">
        <div>
            <label for="qty-{{ $productId }}" class="block text-overline uppercase text-text-gold">Quantity</label>

            <div class="mt-3 inline-flex items-stretch rounded-sm border border-border-subtle">
                <button type="button" wire:click="decrementQuantity"
                    class="grid h-12 w-12 place-items-center text-text-primary hover:bg-surface-sunken disabled:text-text-muted"
                    @disabled($quantity <= 1) aria-label="Decrease quantity">&minus;</button>

                <input id="qty-{{ $productId }}" type="number" inputmode="numeric"
                    wire:model.live.blur="quantity"
                    min="1" max="{{ max(1, $max) }}"
                    class="h-12 w-14 border-x border-border-subtle bg-transparent text-center text-body tabular-nums text-text-primary
                        [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none"
                    aria-label="Quantity">

                <button type="button" wire:click="incrementQuantity"
                    class="grid h-12 w-12 place-items-center text-text-primary hover:bg-surface-sunken disabled:text-text-muted"
                    @disabled($quantity >= $max) aria-label="Increase quantity">+</button>
            </div>
        </div>
    </div>

    @error('quantity')
        <p class="-mt-4 text-meta text-danger-700">{{ $message }}</p>
    @enderror

    <div class="flex flex-col gap-3">
        {{-- Add to bag is a <button>, never an <a href>. An anchor that mutates
             a cart invites crawlers into cart-mutation URLs (seo.md §8.8). --}}
        <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add"
            @disabled($max === 0)
            class="inline-flex min-h-14 items-center justify-center rounded-sm bg-gold-surface px-8 text-body
                font-semibold text-ink-900 transition-[background-color] duration-[var(--motion-fast)] ease-standard
                hover:bg-gold-surface-hover active:bg-gold-surface-active disabled:bg-ink-200 disabled:text-text-muted">
            <span wire:loading.remove wire:target="add">{{ $max === 0 ? 'Out of stock' : 'Add to bag' }}</span>
            <span wire:loading wire:target="add">Adding…</span>
        </button>

        {{-- Buy Now writes into a detached cart. The existing bag is neither
             merged into nor cleared. --}}
        <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="buyNow"
            @disabled($max === 0)
            class="inline-flex min-h-14 items-center justify-center rounded-sm border-[1.5px] border-text-primary
                bg-transparent px-8 text-body font-semibold text-text-primary
                transition-[background-color] duration-[var(--motion-fast)] ease-standard
                hover:bg-surface-sunken disabled:border-border-subtle disabled:text-text-muted">
            <span wire:loading.remove wire:target="buyNow">Buy now</span>
            <span wire:loading wire:target="buyNow">Taking you to checkout…</span>
        </button>
    </div>

    <div x-data="{ shown: false, name: '' }"
        x-on:added-to-bag.window="name = $event.detail.name; shown = true; setTimeout(() => shown = false, 5000)"
        x-show="shown" x-cloak
        class="rounded-sm border border-border-subtle bg-surface-sunken px-4 py-3 text-meta text-text-primary"
        role="status">
        Added to your bag.
        <a href="{{ route('cart.index') }}" class="ms-1 text-text-gold underline decoration-1 underline-offset-4">View bag</a>
    </div>
</div>
