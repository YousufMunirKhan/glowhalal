{{--
  Slide-in cart drawer.

  One instance in the layout, present on every page. Alpine owns the open/close
  state and the animation; Livewire owns the cart data and the mutations. The
  root element carries x-data, so Livewire morphs the contents on 'cart-updated'
  without ever tearing down the Alpine state — the drawer stays open while its
  lines refresh.

  Opens on:  'open-cart-drawer' (header cart control) and 'added-to-bag' (add).
  Closes on: ESC, backdrop click, the close button.
--}}

@php
    $cart = $this->cart;
    $totals = $this->totals;
    $items = $cart?->items ?? collect();
@endphp

<div
    x-data="{
        open: false,
        openDrawer() {
            if (this.open) return;
            this.open = true;
            document.documentElement.classList.add('scroll-locked');
            this.$nextTick(() => this.$refs.closeButton && this.$refs.closeButton.focus());
        },
        close() {
            this.open = false;
            document.documentElement.classList.remove('scroll-locked');
        },
    }"
    x-on:open-cart-drawer.window="openDrawer()"
    x-on:added-to-bag.window="openDrawer()"
    x-on:keydown.escape.window="open && close()"
>
    {{-- Backdrop / scrim ------------------------------------------------- --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition-opacity ease-standard duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-standard duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        x-on:click="close()"
        class="fixed inset-0 z-[var(--z-overlay)] bg-black/50"
        aria-hidden="true"></div>

    {{-- Panel ------------------------------------------------------------ --}}
    <div x-show="open" x-cloak
        x-ref="panel" tabindex="-1"
        role="dialog" aria-modal="true" aria-label="Your bag"
        x-transition:enter="transition-transform ease-standard duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-standard duration-300"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 end-0 z-[var(--z-modal)] flex w-full max-w-[400px] flex-col
            border-s border-border-subtle bg-surface shadow-xl focus:outline-none">

        {{-- Header --}}
        <div class="flex items-center justify-between gap-4 border-b border-border-subtle px-5 py-4">
            <h2 class="text-title-sm text-text-primary">
                Your bag
                @if ($totals->itemsCount > 0)
                    <span class="ms-1 text-meta font-normal text-text-secondary">
                        ({{ $totals->itemsCount }} {{ \Illuminate\Support\Str::plural('item', $totals->itemsCount) }})
                    </span>
                @endif
            </h2>

            <button type="button" x-ref="closeButton" x-on:click="close()"
                class="tap-safe -me-2 grid place-items-center rounded-sm text-text-primary
                    hover:bg-black/5 transition-[background-color] duration-[var(--motion-fast)] ease-standard"
                aria-label="Close bag">
                <x-ui.icon name="close" />
            </button>
        </div>

        @if ($items->isEmpty())
            {{-- Honest empty state ---------------------------------------- --}}
            <div class="flex flex-1 flex-col items-center justify-center gap-4 px-6 text-center">
                <p class="text-title-sm text-text-primary">Your bag is empty</p>
                <p class="text-meta text-text-secondary">
                    Nothing in here yet. Everything we make publishes its full ingredient list.
                </p>
                <x-ui.button href="{{ route('shop.index') }}" variant="primary" size="md">
                    Browse the catalogue
                </x-ui.button>
            </div>
        @else
            {{-- Lines (scrollable) ---------------------------------------- --}}
            <ul class="flex-1 overflow-y-auto overscroll-contain px-5 py-4">
                @foreach ($items as $item)
                    @php
                        $variant = $item->variant;
                        $available = $variant
                            ? min($variant->max_per_order ?? 99, max(0, $variant->available_quantity), 99)
                            : 99;
                        $image = $item->image_path_snapshot
                            ? asset('storage/' . ltrim($item->image_path_snapshot, '/'))
                            : '/images/placeholder/product-face-cream.svg';
                    @endphp

                    <li wire:key="drawer-line-{{ $item->id }}"
                        class="grid grid-cols-[4rem_minmax(0,1fr)] gap-4 border-b border-border-subtle py-4 first:pt-0">

                        <div class="product-plate overflow-hidden rounded-sm bg-white ring-1 ring-border-subtle">
                            <img src="{{ $image }}" alt="" width="112" height="112" loading="lazy" decoding="async"
                                class="aspect-square w-full object-contain p-1.5">
                        </div>

                        <div class="flex min-w-0 flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="truncate text-body text-text-primary">
                                        @if ($variant?->product?->slug)
                                            <a href="{{ route('products.show', $variant->product->slug) }}"
                                                class="no-underline text-text-primary hover:underline decoration-1 underline-offset-4">
                                                {{ $item->name_snapshot }}
                                            </a>
                                        @else
                                            {{ $item->name_snapshot }}
                                        @endif
                                    </h3>
                                    @if ($variant?->name)
                                        <p class="mt-0.5 text-meta text-text-secondary">{{ $variant->name }}</p>
                                    @endif
                                    <p class="mt-0.5 text-meta tabular-nums text-text-muted">
                                        {{ $item->unit_price_amount->format() }} each
                                    </p>
                                </div>

                                <p class="shrink-0 text-body tabular-nums text-text-primary"
                                    data-drawer-line-total="{{ $item->id }}">
                                    {{ $item->unit_price_amount->times($item->quantity)->format() }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                {{-- Quantity stepper — server round trip via CartService. --}}
                                <div class="inline-flex items-stretch rounded-sm border border-border-subtle">
                                    <button type="button"
                                        wire:click="decrement({{ $item->id }})"
                                        wire:loading.attr="disabled" wire:target="decrement({{ $item->id }}),increment({{ $item->id }}),removeItem({{ $item->id }})"
                                        class="grid h-11 w-11 place-items-center text-text-primary hover:bg-surface-sunken disabled:text-text-muted"
                                        aria-label="Decrease quantity of {{ $item->name_snapshot }}">&minus;</button>

                                    <span class="grid h-11 w-11 place-items-center border-x border-border-subtle
                                            text-meta tabular-nums text-text-primary"
                                        aria-label="Quantity" aria-live="polite">{{ $item->quantity }}</span>

                                    <button type="button"
                                        wire:click="increment({{ $item->id }})"
                                        wire:loading.attr="disabled" wire:target="decrement({{ $item->id }}),increment({{ $item->id }}),removeItem({{ $item->id }})"
                                        @disabled($item->quantity >= $available)
                                        class="grid h-11 w-11 place-items-center text-text-primary hover:bg-surface-sunken disabled:text-text-muted"
                                        aria-label="Increase quantity of {{ $item->name_snapshot }}">+</button>
                                </div>

                                <button type="button" wire:click="removeItem({{ $item->id }})"
                                    wire:loading.attr="disabled" wire:target="removeItem({{ $item->id }})"
                                    class="inline-flex min-h-11 items-center text-meta font-semibold text-danger-700 underline decoration-1
                                        underline-offset-4 hover:text-danger-800"
                                    aria-label="Remove {{ $item->name_snapshot }} from bag">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Footer: subtotal + actions ------------------------------- --}}
            <div class="border-t border-border-subtle px-5 py-4">
                <div class="flex items-baseline justify-between gap-4">
                    <span class="text-body text-text-secondary">Subtotal</span>
                    <span class="text-price-sm tabular-nums text-text-primary" data-drawer-subtotal>
                        {{ $totals->subtotal->format() }}
                    </span>
                </div>
                <p class="mt-1 text-meta text-text-muted">
                    Delivery and any Cash on Delivery fee are calculated at checkout.
                </p>

                <div class="mt-4 grid gap-2">
                    <x-ui.button href="{{ route('checkout.index') }}" variant="primary" size="lg" width="full">
                        Checkout
                    </x-ui.button>
                    <x-ui.button href="{{ route('cart.index') }}" variant="secondary" size="md" width="full">
                        View cart
                    </x-ui.button>
                </div>
            </div>
        @endif
    </div>
</div>
