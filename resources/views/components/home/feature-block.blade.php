@props([
    'product' => [],
    'index' => 1,
    'volume' => 'Volume one',
])

{{--
  Design system §4.1 — the editorial feature block. THE ANTI-EMPTY-STORE
  COMPONENT.

  This replaces the product grid below 4 SKUs. Three tiles in a grid is the
  single visual that says "dead store", so the founding collection is rendered
  as one full-bleed editorial block per product instead. Never a 3-tile grid.

  DEVIATION, and the reason for it: the image used to be full-bleed to the
  viewport edge on mobile. On the redesigned homepage this block sits on a
  #1C1C1C band, and a full-bleed white plate at 375px is a flashbang — this
  store's stated shopping window is 10pm to 1am, in bed. The plate is now
  contained and framed with a 1px hairline, so it reads as a lit object on a
  counter rather than as a hole cut in the page. It is still unmistakably a
  photographic plate and not a card, because nothing else on the page is white.

  The plate itself stays WHITE in every theme and on every band. Never invert a
  product photograph — §1.2 treats that as a counterfeit-fear mitigation, not
  an aesthetic.

  The ingredient counts sit ABOVE the price, deliberately: the counts are the
  differentiator, the price is not.
--}}

@php
    $imageFirst = $index % 2 === 1;
@endphp

<article class="md:grid md:grid-cols-2 md:items-center md:gap-10 lg:gap-16">

    <div @class(['md:order-2' => !$imageFirst])>
        <div class="product-plate overflow-hidden rounded-sm ring-1 ring-border-subtle">
            <img src="{{ $product['image'] }}" alt="{{ $product['image_alt'] }}"
                width="800" height="1000" loading="lazy" decoding="async"
                class="block aspect-square w-full object-contain xs:aspect-4/5 md:aspect-3/4">
        </div>
    </div>

    <div @class(['mt-6 md:mt-0', 'md:order-1' => !$imageFirst])>
        <x-ui.overline>
            {{ $volume }} <span aria-hidden="true">·</span>
            <span class="tnum">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span>
        </x-ui.overline>

        <h3 class="mt-2 font-display text-display-sm tracking-display text-text-primary">
            <a href="/products/{{ $product['slug'] }}" class="no-underline hover:underline hover:underline-offset-4">
                {{ $product['name'] }}
            </a>
        </h3>

        <p class="mt-2 text-body text-text-secondary">{{ $product['descriptor'] }}</p>

        {{-- The counts are the differentiator. The zeroes are the message. --}}
        <p class="mt-3 text-body text-text-primary">
            <span class="tnum font-semibold">{{ $product['ingredient_count'] }}</span> ingredients.
            <span class="tnum font-semibold">0</span> animal-derived.
            <span class="tnum font-semibold">0</span> alcohol.
        </p>

        @if (!empty($product['free_from']))
            <ul class="mt-4 flex flex-wrap gap-2">
                @foreach ($product['free_from'] as $inci)
                    <li>
                        <x-ui.free-from-chip :inci="$inci"
                            href="/what-we-never-use#{{ \Illuminate\Support\Str::slug($inci) }}" />
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="tnum mt-5 text-price text-text-primary">PKR&nbsp;{{ number_format($product['price']) }}</p>

        {{-- $product is the plain array mapped in routes/web.php, not an Eloquent
             model, so the model-backed Livewire buy box cannot mount here. Both
             CTAs are links to the PDP, where the real buy box (add to bag + Buy
             Now → checkout, on the live variant) lives. The primary is therefore
             labelled "Shop now" (matching the hero), not "Buy now": a link that
             opens the product page does not itself buy anything, and the label
             must say what the control actually does. --}}
        <div class="mt-5 flex flex-col gap-3 xs:flex-row">
            <x-ui.button variant="primary" size="md" width="full-mobile"
                href="/products/{{ $product['slug'] }}"
                aria-label="Shop {{ $product['name'] }}">
                Shop now
            </x-ui.button>
            <x-ui.button variant="secondary" size="md" width="full-mobile"
                href="/products/{{ $product['slug'] }}">
                View details
            </x-ui.button>
        </div>
    </div>
</article>
