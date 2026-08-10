{{--
  Homepage — light, product-forward redesign (owner-approved direction).

  Reworked from the near-black editorial layout to the clean, light, commercial
  look of the approved mockup, in the brand's OWN palette: gold + black on
  light, matching the logo. The mockup was green; the site is gold, so the logo
  and the page agree.

  WHAT DID NOT CHANGE — the two hard rules, because they are about truth, not
  taste (see routes/web.php header and design-system §4.1):

    1. NO THIRD-PARTY HALAL CLAIMS. Glow Halal holds no accreditation from any
       body and claims none. There is no "Halal Certified" seal, badge, issuing
       organisation or reference number anywhere on this page. The mockup's
       certification seal was replaced with a TRUE, checkable claim: we publish
       the full INCI list of every product, and the full list of what we never
       use.

    2. NO FABRICATED SOCIAL PROOF. The mockup's star ratings and review counts
       are gone. No star average renders until there are 5+ real reviews; below
       that the reviews section states the absence plainly.

  The product GRID renders only at 4+ published SKUs; below that the founding
  collection falls back to editorial feature-blocks — "three tiles in a grid is
  the single visual that says 'dead store'" (§4.1).

  Everything is server-rendered Blade; nothing sits behind a Livewire island or
  a client fetch, so a crawler and a pre-JS screenshot see the whole page.
--}}

@php
    $productCount = count($products);

    // Homepage entity graph (seo.md §4.13). For a zero-authority domain the
    // structured-data entity is the highest-leverage organic signal.
    //
    // The Organization + WebSite + WebPage nodes are built by App\Support\JsonLd
    // — the SINGLE source of truth for the sitewide entity — so the home page,
    // the shop/product pages (via SchemaGraph) and every content page all emit
    // the IDENTICAL rich OnlineStore node: same @id `/#organization`, the same
    // description, areaServed PK, knowsAbout and sameAs, WebSite.inLanguage
    // `en-PK`, and NO invented phone/email/address (JsonLd omits anything the
    // owner has not set in StoreSettings). No SearchAction — there is no /search
    // route. No third-party halal claim appears here, per the site-wide rule.
    $base = rtrim(url('/'), '/');
    $graph = [
        \App\Support\JsonLd::organization(),
        \App\Support\JsonLd::website(),
        \App\Support\JsonLd::webPage(\App\Support\JsonLd::siteUrl(), $meta['title'], $meta['description'], 'WebPage'),
    ];

    if ($productCount > 0) {
        $items = [];
        foreach ($products as $i => $p) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => $base.'/products/'.$p['slug'],
                'name' => $p['name'],
            ];
        }
        $graph[] = [
            '@type' => 'ItemList',
            '@id' => $base.'/#featured',
            'name' => 'Featured essentials',
            'itemListElement' => $items,
        ];
    }

    $jsonLd = ['@context' => 'https://schema.org', '@graph' => $graph];
@endphp

<x-layouts.app :title="$meta['title']" :description="$meta['description']" current="home" :products="$products"
    :company="$company" :founder="$founder">

    <x-slot:head>
        <script type="application/ld+json">
            {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    </x-slot:head>

    {{-- ── Hero — product slider (one slide per product) ─────────────────── --}}
    <x-home.hero :products="$products" />

    {{-- ── Trust rail — three facts, each with a place to check it ────────── --}}
    {{-- Light restyle of the old dark rail. Each row is a fact about this site,
         never a third-party approval line. --}}
    <section aria-label="What this site publishes" class="border-y border-luxe-border bg-cream">
        <div class="container-page">
            <ul class="md:flex md:divide-x md:divide-luxe-border">
                @foreach ([
                    ['label' => 'Every ingredient published', 'detail' => 'Full INCI list, every product', 'href' => '/halal-ingredients'],
                    ['label' => 'Every exclusion published', 'detail' => 'The '.$neverUseTotal.' we will not formulate with', 'href' => '/what-we-never-use'],
                    ['label' => 'Cash on Delivery', 'detail' => 'Nationwide, no account needed', 'href' => '/shipping-returns'],
                ] as $item)
                    <li class="border-t border-luxe-border first:border-t-0 md:flex-1 md:border-t-0">
                        <a href="{{ $item['href'] }}"
                            class="flex min-h-16 items-center gap-3 py-3 text-charcoal no-underline
                                hover:underline hover:decoration-champagne hover:underline-offset-[3px] md:min-h-20 md:px-5">
                            <x-ui.icon name="check" :size="18" stroke-width="2.5" class="shrink-0 text-champagne" />
                            <span>
                                <span class="block text-body font-semibold leading-tight">{{ $item['label'] }}</span>
                                <span class="mt-0.5 block text-meta text-muted-warm">{{ $item['detail'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ── Our essentials ────────────────────────────────────────────────── --}}
    <section aria-labelledby="collection-heading" class="section-y bg-ivory">
        <div class="container-page">
            <div class="mx-auto max-w-read text-center">
                <h2 id="collection-heading"
                    class="font-display text-display tracking-display text-charcoal">
                    Our essentials
                    <span aria-hidden="true" class="text-champagne">&#10022;</span>
                </h2>
                <p class="mt-3 text-lead text-muted-warm">
                    Simple. Effective. Nothing hidden — each formula published in full, down to the last ingredient.
                </p>
            </div>

            <div class="mt-10 md:mt-12">
                @if ($productCount >= 4)
                    {{-- Enough SKUs for a grid to read as a shop, not a stub. --}}
                    <x-home.product-grid :products="$products" />
                @elseif ($productCount >= 1)
                    {{-- Below 4: a centred 2-up luxury card grid on cream cards,
                         never a 3-tile grid (§4.1). $products is a plain array, so
                         the CTAs link to the PDP where the real Add-to-bag lives. --}}
                    <ul class="mx-auto grid max-w-3xl gap-6 sm:grid-cols-2 sm:gap-8">
                        @foreach ($products as $product)
                            <li>
                                <article class="group flex h-full flex-col rounded-lg border border-luxe-border bg-cream p-5
                                        transition-[border-color,box-shadow] duration-[var(--motion-fast)] ease-standard
                                        hover:border-champagne hover:shadow-md">
                                    {{-- White plate — a product photograph is never inverted. --}}
                                    <a href="/products/{{ $product['slug'] }}"
                                        class="block overflow-hidden rounded-sm bg-white ring-1 ring-luxe-border">
                                        <img src="{{ $product['image'] }}" alt="{{ $product['image_alt'] }}"
                                            width="600" height="600" loading="lazy" decoding="async"
                                            class="block aspect-square w-full object-contain p-6
                                                transition-transform duration-[var(--motion-slow)] ease-standard group-hover:scale-[1.03]">
                                    </a>

                                    <div class="mt-5 flex flex-1 flex-col">
                                        <h3 class="font-display text-title tracking-display text-charcoal">
                                            <a href="/products/{{ $product['slug'] }}"
                                                class="no-underline hover:underline hover:decoration-champagne hover:underline-offset-4">
                                                {{ $product['name'] }}
                                            </a>
                                        </h3>

                                        @if (!empty($product['descriptor']))
                                            <p class="mt-2 text-body text-muted-warm">{{ $product['descriptor'] }}</p>
                                        @endif

                                        <div class="mt-auto pt-5">
                                            <p class="tnum text-price text-charcoal">
                                                PKR&nbsp;{{ number_format($product['price']) }}
                                            </p>

                                            {{-- Two CTAs: Buy now (POST → detached cart → checkout) and
                                                 Shop now (→ product page, where Add-to-bag + drawer live).
                                                 $products is a plain array, so Buy now goes through the
                                                 buy-now route rather than the model-based buy box. --}}
                                            <div class="mt-4 flex flex-col gap-2">
                                                <form method="POST" action="{{ route('buy-now', $product['slug']) }}">
                                                    @csrf
                                                    <button type="submit"
                                                        aria-label="Buy {{ $product['name'] }} now"
                                                        class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm
                                                            border border-transparent bg-luxe-black px-6 text-meta font-semibold uppercase
                                                            tracking-caps text-ivory transition-[background-color]
                                                            duration-[var(--motion-fast)] ease-standard hover:bg-charcoal">
                                                        <x-ui.icon name="cart" :size="18" />
                                                        Buy now
                                                    </button>
                                                </form>
                                                <a href="/products/{{ $product['slug'] }}"
                                                    aria-label="Shop {{ $product['name'] }}"
                                                    class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm
                                                        border-[1.5px] border-charcoal px-6 text-meta font-semibold uppercase
                                                        tracking-caps text-charcoal no-underline transition-[background-color]
                                                        duration-[var(--motion-fast)] ease-standard hover:bg-cream">
                                                    Shop now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                @else
                    {{-- No published products yet — state it, don't fake a grid. --}}
                    <div class="mx-auto max-w-xl rounded-lg border border-luxe-border bg-cream p-8 text-center">
                        <p class="text-body text-charcoal">
                            The founding collection is being finalised — every formula is published in full
                            before it goes on sale.
                        </p>
                        <div class="mt-5">
                            <a href="/what-we-never-use"
                                class="inline-flex min-h-12 items-center justify-center gap-2 rounded-sm border-[1.5px]
                                    border-charcoal px-6 text-body font-semibold text-charcoal no-underline
                                    transition-[background-color] duration-[var(--motion-fast)] ease-standard hover:bg-ivory">
                                See what we never use
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            @if ($productCount > 0)
                <div class="mt-12 flex justify-center">
                    <a href="/shop"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-sm border border-transparent
                            bg-luxe-black px-8 text-body font-semibold text-ivory no-underline
                            transition-[background-color] duration-[var(--motion-fast)] ease-standard hover:bg-charcoal">
                        View all products
                        <x-ui.icon name="arrow-right" :size="18" />
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ── Trust band — two rows, matched to the mockup (honest wording) ──── --}}
    {{-- Row 1 replaces the mockup's "Halal & Safe / Dermatologically Tested"
         row (unverifiable claims) with facts about this site. Row 2 is the
         mockup's service row, all true and operational. --}}
    {{-- Trust rail row 1 — facts about this site, on ivory with champagne icons.
         "What we never use" links to the exclusion page. Body text is charcoal
         (PASS AAA); champagne is used only on the icons and their rings. --}}
    <section aria-labelledby="why-heading" class="section-y bg-ivory">
        <div class="container-page">
            <h2 id="why-heading" class="sr-only">Why Glow Halal</h2>

            <ul class="grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'document', 'title' => 'Every ingredient published', 'body' => 'The full INCI list on every product page — nothing left off.', 'href' => '/halal-ingredients'],
                    ['icon' => 'cross', 'title' => 'What we never use', 'body' => 'The complete list of the '.$neverUseTotal.' ingredients we won\'t formulate with.', 'href' => '/what-we-never-use'],
                    ['icon' => 'check', 'title' => 'Skin-loving formulas', 'body' => 'Gentle, considered ingredients — and we tell you exactly what is in them.', 'href' => null],
                    ['icon' => 'home', 'title' => 'Made in Karachi', 'body' => 'Formulated in Pakistan for Pakistani skin, delivered across the country.', 'href' => null],
                ] as $feature)
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-champagne/40 bg-cream text-champagne">
                            <x-ui.icon :name="$feature['icon']" :size="22" />
                        </span>
                        <div>
                            <h3 class="text-title-sm text-charcoal">
                                @if ($feature['href'])
                                    <a href="{{ $feature['href'] }}"
                                        class="no-underline hover:underline hover:decoration-champagne hover:underline-offset-[3px]">{{ $feature['title'] }}</a>
                                @else
                                    {{ $feature['title'] }}
                                @endif
                            </h3>
                            <p class="mt-1 text-meta text-muted-warm">{{ $feature['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Trust rail row 2 — the black service bar. Ivory text, champagne icons;
         both PASS AAA on luxe-black. --}}
    <section aria-label="Service and delivery" class="bg-luxe-black">
        <div class="container-page py-10 md:py-12">
            <ul class="grid gap-x-6 gap-y-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => 'truck', 'title' => 'Free delivery over Rs 5,000', 'body' => 'A flat Rs 300 delivery below that, nationwide.', 'href' => '/shipping-returns'],
                    ['icon' => 'cash', 'title' => 'Cash on Delivery', 'body' => 'Pay in cash when your order arrives.', 'href' => '/shipping-returns'],
                    ['icon' => 'box', 'title' => '7-day returns', 'body' => 'Unopened items, within 7 days.', 'href' => '/shipping-returns'],
                    ['icon' => 'whatsapp', 'title' => 'Customer support', 'body' => 'Answers on WhatsApp, working days.', 'href' => (($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal') ?? 'https://wa.me/923001234567'), 'external' => true],
                ] as $op)
                    <li class="flex gap-4">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-champagne/40 bg-white/5 text-champagne">
                            <x-ui.icon :name="$op['icon']" :size="22" />
                        </span>
                        <div>
                            <h3 class="text-title-sm text-ivory">
                                <a href="{{ $op['href'] }}"
                                    @if (!empty($op['external'])) target="_blank" rel="noopener" @endif
                                    class="no-underline hover:underline hover:decoration-champagne hover:underline-offset-[3px]">{{ $op['title'] }}</a>
                            </h3>
                            <p class="mt-1 text-meta text-ivory/70">{{ $op['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

</x-layouts.app>
