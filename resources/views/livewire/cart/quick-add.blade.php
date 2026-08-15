{{--
  One-click add from the catalogue grid.

  Single option  -> one click, straight into the bag.
  Several options -> a picker. Never a silent default: the wrong shade arrives
  at the door, the customer refuses the parcel, and a refused COD delivery
  costs two courier legs and collects nothing.

  The picker panel is server-rendered into the DOM and toggled with CSS via
  Alpine, not fetched on click — instant, no round trip, and nothing on this
  page depends on JavaScript to exist.
--}}

@php
    $variants = $this->product->variants;
    $inStock = $this->purchasableVariants;
    $soldOut = $inStock->isEmpty();
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">

    @if ($soldOut)
        <button type="button" disabled
            class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border border-border-subtle
                px-4 text-meta font-semibold text-text-muted">
            Out of stock
        </button>

    @elseif ($this->needsChoice)
        <button type="button"
            x-on:click="open = !open"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            aria-haspopup="true"
            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-sm bg-gold-surface px-4
                text-meta font-semibold text-ink-900 transition-[background-color] duration-[var(--motion-fast)]
                ease-standard hover:bg-gold-surface-hover">
            <span>Choose {{ $variants->count() > 3 ? 'a shade' : 'an option' }}</span>
            <x-ui.icon name="chevron-down" :size="16" x-bind:class="open ? 'rotate-180' : ''" class="transition-transform" />
        </button>

        <div x-show="open" x-cloak x-on:click.outside="open = false"
            class="absolute inset-x-0 bottom-full z-[var(--z-dropdown)] mb-2 rounded-md border border-border-subtle
                bg-surface-raised p-2 shadow-lg"
            role="group" aria-label="Choose an option for {{ $this->product->name }}">

            @foreach ($variants as $variant)
                {{-- Each row offers both actions for a CHOSEN variant: add to the
                     bag, or Buy Now straight to checkout. Selecting the row is the
                     "choose an option first" guard. --}}
                <div wire:key="qa-{{ $variant->id }}" class="flex items-stretch gap-1">
                    <button type="button"
                        wire:click="addVariant({{ $variant->id }})"
                        wire:loading.attr="disabled"
                        @disabled(! $variant->is_in_stock)
                        class="flex min-h-11 flex-1 items-center justify-between gap-3 rounded-sm px-3 text-start
                            text-meta text-text-primary hover:bg-surface-sunken disabled:text-text-muted
                            disabled:hover:bg-transparent">
                        <span>{{ $variant->name ?? $variant->sku }}</span>
                        <span class="tabular-nums text-text-secondary">
                            @if ($variant->is_in_stock)
                                {{ $variant->price_amount?->format() }}
                            @else
                                Sold out
                            @endif
                        </span>
                    </button>

                    @if ($variant->is_in_stock)
                        {{-- Native POST (BuyNowController), not wire:click — the
                             same detached-cart flow, but present in the plain
                             HTML so it works without JavaScript. --}}
                        <form method="POST" action="{{ route('buy-now', $this->product->slug) }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                            <button type="submit"
                                class="min-h-11 w-full rounded-sm bg-gold-surface px-3 text-meta font-semibold
                                    text-ink-900 transition-[background-color] duration-[var(--motion-fast)]
                                    ease-standard hover:bg-gold-surface-hover"
                                aria-label="Buy {{ $variant->name ?? $variant->sku }} now">
                                Buy now
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach

            <a href="{{ route('products.show', $this->product->slug) }}"
                class="mt-1 block px-3 py-2 text-meta text-text-gold underline decoration-1 underline-offset-4">
                See the full ingredient list
            </a>
        </div>

    @else
        <div class="flex flex-col gap-2">
            <button type="button"
                wire:click="addDefault"
                wire:loading.attr="disabled" wire:target="addDefault"
                class="inline-flex min-h-11 w-full items-center justify-center rounded-sm bg-gold-surface px-4
                    text-meta font-semibold text-ink-900 transition-[background-color] duration-[var(--motion-fast)]
                    ease-standard hover:bg-gold-surface-hover disabled:opacity-70">
                <span wire:loading.remove wire:target="addDefault">{{ $added ? 'Added — add another' : 'Add to bag' }}</span>
                <span wire:loading wire:target="addDefault">Adding…</span>
            </button>

            {{-- Buy Now: detached cart straight to checkout, existing bag
                 untouched. A native POST (BuyNowController resolves the default
                 variant) so the path exists without JavaScript. --}}
            <form method="POST" action="{{ route('buy-now', $this->product->slug) }}">
                @csrf
                <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-sm border-[1.5px]
                        border-text-primary bg-transparent px-4 text-meta font-semibold text-text-primary
                        transition-[background-color] duration-[var(--motion-fast)] ease-standard
                        hover:bg-surface-sunken">
                    Buy now
                </button>
            </form>
        </div>
    @endif

    @error('variant')
        <p class="mt-2 text-meta text-danger-700">{{ $message }}</p>
    @enderror

    @if ($added)
        <p class="mt-2 text-meta text-text-secondary">
            In your bag.
            <a href="{{ route('cart.index') }}" class="text-text-gold underline decoration-1 underline-offset-4">View bag</a>
        </p>
    @endif
</div>
