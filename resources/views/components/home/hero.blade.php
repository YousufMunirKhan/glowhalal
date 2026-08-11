@props([
    'products' => [],
])

{{--
  HOMEPAGE HERO — a product SLIDER (Alpine). Each slide is ABOUT a product
  (name + one product line + price + shop link) — not the brand — and the
  "Full INCI list" corner badge has been removed on request.

  Extensible: one slide per product, so adding products (or, later, dedicated
  banner slides) just adds slides. Auto-advances every 6s, pauses on hover, has
  prev/next + dots, keyboard arrows. Mobile: image on top, text below, dots
  under.

  SEO: a single visually-hidden <h1> names the page; each slide's product name
  is an <h2>, so there is exactly one h1. The first slide is in the initial HTML
  (no-JS users see it), so the content is crawlable.
--}}

@php
    $slides = [];
    foreach ($products as $p) {
        $slides[] = [
            'name' => $p['name'],
            'line' => $p['descriptor'] ?: 'A traditional herbal oil — every ingredient published, down to the last one.',
            'price' => $p['price'] ?? null,
            'compare_at' => $p['compare_at'] ?? null,
            'image' => $p['image'] ?? '/images/hero-scene.svg',
            'image_alt' => $p['image_alt'] ?? ($p['name'] ?? 'Glow Halal product'),
            'slug' => $p['slug'] ?? null,
        ];
    }

    if (empty($slides)) {
        $slides[] = [
            'name' => 'Halal skincare, nothing hidden',
            'line' => 'Every ingredient published on the page — see exactly what is in it.',
            'price' => null,
            'image' => '/images/hero-scene.svg',
            'image_alt' => 'Glow Halal',
            'slug' => null,
        ];
    }
    $slideCount = count($slides);
@endphp

<section aria-label="Featured products" class="bg-ivory">
    <h1 class="sr-only">Glow Halal — Halal Beauty & Cosmetics in Pakistan</h1>

    <div class="container-page"
        x-data="{
            i: 0,
            n: {{ $slideCount }},
            timer: null,
            /* Auto-advance ONLY when the user has not asked for reduced motion,
               and never while the tab is hidden — both are CWV / a11y wins. */
            start() {
                this.stop();
                if (this.n <= 1) return;
                if (!window.matchMedia('(prefers-reduced-motion: no-preference)').matches) return;
                this.timer = setInterval(() => { if (!document.hidden) this.next(); }, 6000);
            },
            stop() { clearInterval(this.timer); this.timer = null; },
            next() { this.i = (this.i + 1) % this.n; },
            prev() { this.i = (this.i - 1 + this.n) % this.n; },
            go(k) { this.i = k; },
        }"
        x-init="start()" @mouseenter="stop()" @mouseleave="start()"
        @visibilitychange.document="document.hidden ? stop() : start()"
        @keydown.left.window="prev()" @keydown.right.window="next()">

        {{-- CLS FIX (CWV): every slide occupies the SAME css-grid cell
             (grid-area:1/1), so the stage height is always the tallest slide and
             NEVER reflows when slides change (was 0.799 with x-show/display).
             Slides cross-fade via opacity/visibility — not display — and the
             non-active ones are `invisible`, so they leave the tab order and the
             a11y tree. Slide 0 carries the visible/opacity-100 defaults in its
             STATIC classes, so with JavaScript off the first slide still shows. --}}
        <div class="grid">
        @foreach ($slides as $k => $slide)
            <div style="grid-area: 1 / 1;"
                :class="{
                    'opacity-100 visible': i === {{ $k }},
                    'opacity-0 invisible': i !== {{ $k }}
                }"
                @class([
                    'grid items-center gap-8 py-10 md:py-14 lg:grid-cols-2 lg:gap-12 lg:py-16',
                    'transition-opacity duration-500 ease-standard motion-reduce:transition-none',
                    'opacity-100 visible' => $k === 0,
                    'opacity-0 invisible' => $k !== 0,
                ])>

                {{-- Image (top on mobile, right on desktop). No badge. --}}
                <div class="lg:order-2">
                    <div class="mx-auto max-w-sm overflow-hidden rounded-lg bg-white ring-1 ring-champagne/25 lg:max-w-none">
                        <img src="{{ $slide['image'] }}" alt="{{ $slide['image_alt'] }}"
                            width="800" height="800"
                            sizes="(min-width: 1024px) 45vw, (min-width: 640px) 24rem, 100vw"
                            @if ($k === 0) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
                            class="block aspect-square w-full bg-white object-contain p-5 md:p-8">
                    </div>
                </div>

                {{-- Text — about the PRODUCT. --}}
                <div class="lg:order-1">
                    <p class="text-overline uppercase tracking-caps text-champagne">Featured</p>
                    <h2 class="mt-3 font-display text-display tracking-display text-charcoal md:text-display-lg">
                        {{ $slide['name'] }}
                    </h2>
                    <p class="mt-4 max-w-read text-lead text-muted-warm">{{ $slide['line'] }}</p>

                    @if ($slide['price'])
                        <p class="tnum mt-4 text-price text-charcoal">
                            @if (!empty($slide['compare_at']))
                                <s class="me-2 text-title-sm font-normal text-muted-warm">PKR&nbsp;{{ number_format($slide['compare_at']) }}</s>
                            @endif
                            PKR&nbsp;{{ number_format($slide['price']) }}
                            @if (!empty($slide['compare_at']))
                                <span class="ms-2 align-middle rounded-full bg-luxe-black px-3 py-1 text-meta font-semibold text-ivory">
                                    Save PKR {{ number_format($slide['compare_at'] - $slide['price']) }}
                                </span>
                            @endif
                        </p>
                    @endif

                    <div class="mt-6 flex flex-col gap-3 xs:flex-row">
                        @if ($slide['slug'])
                            <a href="/products/{{ $slide['slug'] }}"
                                class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-sm bg-luxe-black
                                    px-8 text-body font-semibold text-ivory no-underline transition-[background-color]
                                    duration-[var(--motion-fast)] ease-standard hover:bg-charcoal xs:w-auto">
                                Shop now
                                <x-ui.icon name="arrow-right" :size="18" />
                            </a>
                        @endif
                        <a href="/shop"
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-sm border-[1.5px]
                                border-charcoal px-8 text-body font-semibold text-charcoal no-underline
                                transition-[background-color] duration-[var(--motion-fast)] ease-standard hover:bg-cream xs:w-auto">
                            View all products
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
        </div>{{-- /stacking grid --}}

        {{-- Controls — only when there is more than one slide. --}}
        @if ($slideCount > 1)
            <div class="flex items-center justify-center gap-4 pb-8">
                <button type="button" @click="prev()" aria-label="Previous product"
                    class="tap-safe grid h-9 w-9 place-items-center rounded-full border border-luxe-border text-charcoal
                        hover:bg-cream transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    <x-ui.icon name="chevron-right" :size="18" class="rotate-180" />
                </button>

                <div class="flex items-center gap-2">
                    @foreach ($slides as $k => $slide)
                        <button type="button" @click="go({{ $k }})" aria-label="Go to product {{ $k + 1 }}"
                            class="h-2.5 rounded-full transition-all duration-[var(--motion-fast)]"
                            :class="i === {{ $k }} ? 'w-6 bg-champagne' : 'w-2.5 bg-luxe-border hover:bg-champagne/50'"></button>
                    @endforeach
                </div>

                <button type="button" @click="next()" aria-label="Next product"
                    class="tap-safe grid h-9 w-9 place-items-center rounded-full border border-luxe-border text-charcoal
                        hover:bg-cream transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    <x-ui.icon name="chevron-right" :size="18" />
                </button>
            </div>
        @endif
    </div>
</section>
