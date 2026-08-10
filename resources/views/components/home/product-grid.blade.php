@props([
    'products' => [],
])

{{--
  Featured-essentials grid — dark-luxury skin. Cream product cards on the ivory
  band, champagne hairline, BLACK CTA.

  GATED BY CALLER on catalogue count: the homepage renders this grid ONLY when
  there are 4+ published products, and falls back to the 2-up luxury cards below
  that (design-system §4.1 — "three tiles in a grid is the single visual that
  says 'dead store'"). This component assumes that gate has already passed.

  NO FABRICATED SOCIAL PROOF. The mockup shows star ratings and review counts
  ("(1,250)"). This store publishes no star average until there are 5+ real
  reviews, so this card carries NONE — inventing a rating is the same class of
  false claim as inventing a certification.

  $products is the array shape built in routes/web.php (NOT an Eloquent model),
  so the card links to the product page for the real add-to-cart (the Livewire
  quick-add island lives on the PDP and needs the model).

  Contrast: charcoal #22211F on cream #F2EBDD ~13.6:1 PASS AAA; ivory on
  luxe-black CTA ~18:1 PASS AAA. The white product plate is never inverted.
--}}

<ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
    @foreach ($products as $product)
        <li>
            <article class="group flex h-full flex-col rounded-lg border border-luxe-border bg-cream p-4
                    transition-[border-color,box-shadow] duration-[var(--motion-fast)] ease-standard
                    hover:border-champagne hover:shadow-md">
                {{-- White plate — a product photograph is never inverted. --}}
                <a href="/products/{{ $product['slug'] }}"
                    class="block overflow-hidden rounded-sm bg-white ring-1 ring-luxe-border">
                    <img src="{{ $product['image'] }}" alt="{{ $product['image_alt'] }}"
                        width="600" height="600" loading="lazy" decoding="async"
                        class="block aspect-square w-full object-contain p-4
                            transition-transform duration-[var(--motion-slow)] ease-standard group-hover:scale-[1.03]">
                </a>

                <div class="mt-4 flex flex-1 flex-col">
                    <h3 class="text-title-sm text-charcoal">
                        <a href="/products/{{ $product['slug'] }}"
                            class="no-underline hover:underline hover:decoration-champagne hover:underline-offset-4">
                            {{ $product['name'] }}
                        </a>
                    </h3>

                    @if (!empty($product['descriptor']))
                        <p class="clamp-2 mt-1 text-meta text-muted-warm">{{ $product['descriptor'] }}</p>
                    @endif

                    <div class="mt-auto pt-4">
                        <p class="tnum text-price-sm text-charcoal">
                            PKR&nbsp;{{ number_format($product['price']) }}
                        </p>

                        {{-- $products is a plain array (routes/web.php), not an Eloquent
                             model, so the model-backed Livewire quick-add island cannot
                             mount here. Both CTAs link to the PDP, where the real buy box
                             (add to bag + Buy Now → checkout) lives. --}}
                        <div class="mt-3 flex flex-col gap-2">
                            <a href="/products/{{ $product['slug'] }}"
                                aria-label="Shop {{ $product['name'] }}"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm border
                                    border-transparent bg-luxe-black px-6 text-body font-semibold text-ivory no-underline
                                    transition-[background-color] duration-[var(--motion-fast)] ease-standard
                                    hover:bg-charcoal">
                                Shop now
                            </a>
                            <a href="/products/{{ $product['slug'] }}"
                                aria-label="View {{ $product['name'] }}"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm border-[1.5px]
                                    border-charcoal px-6 text-body font-semibold text-charcoal no-underline
                                    transition-[background-color] duration-[var(--motion-fast)] ease-standard
                                    hover:bg-ivory">
                                View product
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        </li>
    @endforeach
</ul>
