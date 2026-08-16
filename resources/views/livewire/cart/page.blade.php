{{--
  The bag.

  Quantity changes go through Livewire's ordinary round trip: the line total,
  the cart total and the delivery estimate all re-render from the server with
  no page reload. The 300ms debounce means one request per pause, not one per
  keystroke — on a congested Pakistani cell that is the whole difference
  between a usable cart and a broken one.

  Each row carries its own spinner targeted at its own property, so changing
  one line never greys out the others.
--}}

@php
    $cart = $this->cart;
    $totals = $this->totals;
    $items = $cart?->items ?? collect();
    $quote = $totals->shippingQuote;
@endphp

<div>
    @if ($items->isEmpty())
        <div class="max-w-[var(--container-read)]">
            <h1 class="font-display text-display-sm">Your bag is empty</h1>
            <p class="mt-4 text-lead text-text-secondary">
                Nothing in here yet. Everything we make publishes its full ingredient list.
            </p>
            <div class="mt-8">
                <x-ui.button href="{{ route('shop.index') }}" size="lg">Browse the catalogue</x-ui.button>
            </div>
        </div>
    @else
        <h1 class="font-display text-display-sm">Your bag</h1>
        <p class="mt-2 text-meta text-text-secondary">
            {{ $totals->itemsCount }} {{ \Illuminate\Support\Str::plural('item', $totals->itemsCount) }}
        </p>

        <div class="mt-10 grid gap-12 lg:grid-cols-[1fr_22rem] lg:gap-16">

            {{-- Lines --------------------------------------------------------- --}}
            <ul class="grid gap-6">
                @foreach ($items as $item)
                    @php
                        $variant = $item->variant;
                        $available = $variant ? min($variant->max_per_order ?? 99, max(0, $variant->available_quantity), 99) : 99;
                        $image = $item->image_path_snapshot
                            ? asset('storage/' . ltrim($item->image_path_snapshot, '/'))
                            : '/images/placeholder/product-face-cream.svg';
                    @endphp

                    <li wire:key="line-{{ $item->id }}"
                        class="grid grid-cols-[5rem_1fr] gap-4 border-b border-border-subtle pb-6 sm:grid-cols-[7rem_1fr]">

                        <div class="product-plate overflow-hidden">
                            <img src="{{ $image }}" alt="" width="140" height="140" loading="lazy" decoding="async"
                                class="aspect-square w-full object-cover">
                        </div>

                        <div class="flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-title-sm">
                                        @if ($variant?->product?->slug)
                                            <a href="{{ route('products.show', $variant->product->slug) }}"
                                                class="no-underline text-text-primary hover:underline decoration-1 underline-offset-4">
                                                {{ $item->name_snapshot }}
                                            </a>
                                        @else
                                            {{ $item->name_snapshot }}
                                        @endif
                                    </h2>

                                    @if ($variant?->name)
                                        <p class="mt-1 text-meta text-text-secondary">{{ $variant->name }}</p>
                                    @endif

                                    <p class="mt-1 text-meta tabular-nums text-text-muted">
                                        {{ $item->unit_price_amount->format() }} each
                                    </p>
                                </div>

                                {{-- Per-line total. Re-rendered by the server on every
                                     quantity change — never bound with wire:text. --}}
                                <p class="text-price-sm tabular-nums text-text-primary" data-line-total="{{ $item->id }}">
                                    <span wire:loading.remove wire:target="quantities.{{ $item->id }}">
                                        {{ $item->unit_price_amount->times($item->quantity)->format() }}
                                    </span>
                                    {{-- Reserved dimensions: an unsized loading state is a
                                         guaranteed CLS hit (seo.md §8.9). --}}
                                    <span wire:loading wire:target="quantities.{{ $item->id }}"
                                        class="inline-block h-5 w-16 animate-[var(--animate-skeleton)] rounded-xs bg-ink-100"
                                        aria-hidden="true"></span>
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                {{--
                                  Optimistic quantity control.

                                  The steppers move the input on the client and dispatch a
                                  native `input` event, which the debounced wire:model
                                  picks up. That gives the shopper an instant number and
                                  sends exactly ONE request per pause — rather than one
                                  request per click, where three fast taps race each other
                                  and land on the wrong quantity.
                                --}}
                                <div class="inline-flex items-stretch rounded-sm border border-border-subtle"
                                    x-data="{
                                        step(direction) {
                                            const input = $refs.qty;
                                            const next = Math.min(
                                                Number(input.max || 99),
                                                Math.max(1, Number(input.value || 1) + direction)
                                            );
                                            if (next === Number(input.value)) return;
                                            input.value = next;
                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                    }">
                                    <button type="button" x-on:click="step(-1)"
                                        class="grid h-11 w-11 place-items-center text-text-primary hover:bg-surface-sunken"
                                        aria-label="Decrease quantity of {{ $item->name_snapshot }}">&minus;</button>

                                    <input type="number" inputmode="numeric" x-ref="qty"
                                        wire:model.live.debounce.300ms="quantities.{{ $item->id }}"
                                        min="1" max="{{ max(1, $available) }}"
                                        class="h-11 w-14 border-x border-border-subtle bg-transparent text-center text-body tabular-nums
                                            text-text-primary [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none"
                                        aria-label="Quantity of {{ $item->name_snapshot }}">

                                    <button type="button" x-on:click="step(1)"
                                        class="grid h-11 w-11 place-items-center text-text-primary hover:bg-surface-sunken disabled:text-text-muted"
                                        aria-label="Increase quantity of {{ $item->name_snapshot }}">+</button>
                                </div>

                                <button type="button" wire:click="remove({{ $item->id }})"
                                    class="min-h-11 text-meta font-semibold text-danger-700 underline decoration-1 underline-offset-4 hover:text-danger-800">
                                    Remove
                                </button>

                                @if ($variant && $variant->track_inventory && $available <= 5)
                                    <span class="text-meta text-text-muted">Only {{ $available }} left</span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- Summary ------------------------------------------------------- --}}
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-lg border border-border-subtle bg-surface-raised p-6">
                    <h2 class="text-title">Order summary</h2>

                    <dl class="mt-6 grid gap-3 text-body">
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-text-secondary">Subtotal</dt>
                            <dd class="tabular-nums text-text-primary" data-subtotal>{{ $totals->subtotal->format() }}</dd>
                        </div>

                        @if (! $totals->discount->isZero())
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="text-text-secondary">Discount</dt>
                                <dd class="tabular-nums text-success-700">&minus;{{ $totals->discount->format() }}</dd>
                            </div>
                        @endif

                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-text-secondary">
                                Delivery
                                @if ($quote?->isEstimate)
                                    <span class="block text-meta text-text-muted">Estimate — confirmed once you enter your city</span>
                                @endif
                            </dt>
                            <dd class="tabular-nums text-text-primary" data-shipping>
                                {{ $totals->shipping->isZero() ? 'Free' : $totals->shipping->format() }}
                            </dd>
                        </div>

                        <div class="mt-3 flex items-baseline justify-between gap-4 border-t border-border-subtle pt-4">
                            <dt class="text-title-sm">Total</dt>
                            <dd class="text-price tabular-nums text-text-primary" data-total>{{ $totals->grandTotal->format() }}</dd>
                        </div>
                    </dl>

                    <p class="mt-3 text-meta text-text-muted">
                        Prices include GST. A Cash on Delivery collection fee, if any, is shown at checkout.
                    </p>

                    <div class="mt-6 grid gap-3">
                        <x-ui.button href="{{ route('checkout.index') }}" size="lg" width="full">
                            Checkout
                        </x-ui.button>

                        {{-- The channel every real customer so far has actually
                             ordered on. Renders only when the owner has set a
                             WhatsApp number; the basket and total are prefilled
                             so nobody retypes anything. --}}
                        @php($waOrder = $this->whatsappOrderHref($this->cart, $totals->grandTotal->format()))
                        @if ($waOrder)
                            <x-ui.button :href="$waOrder" variant="secondary" width="full" external
                                data-gh-wa-loc="cart">
                                Order on WhatsApp instead
                            </x-ui.button>
                        @endif

                        <x-ui.button href="{{ route('shop.index') }}" variant="secondary" width="full">
                            Keep shopping
                        </x-ui.button>
                    </div>

                    <p class="mt-4 text-center text-meta text-text-secondary">
                        Cash on Delivery. No account needed.
                    </p>
                </div>
            </aside>
        </div>
    @endif
</div>
