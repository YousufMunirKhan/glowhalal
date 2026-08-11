{{--
  Product detail page.

  Everything a search engine needs is here in the initial HTML: the H1, the
  price, the description, the full INCI list, the exclusion list, the
  breadcrumbs, the JSON-LD. Nothing is behind an island, a lazy component, a
  tab that fetches on click, or a `wire:text`. The tabs below are <details>
  elements, which keeps every word in the DOM with no JavaScript at all
  (seo.md §8.5).

  There is no certification badge, seal, standard number or issuing body on
  this page and there must never be one. Glow Halal holds no halal
  accreditation. The honest claim — and the one no competitor makes — is the
  full ingredient list below, plus the list of what we will not formulate with.
--}}

@php
    $images = $product->images->isNotEmpty()
        ? $product->images
        : collect();

    $hero = $images->first()?->path
        ? asset('storage/' . ltrim($images->first()->path, '/'))
        : '/images/placeholder/product-face-cream.svg';

    // og:image needs a raster (jpg/png/webp) — SVG placeholders do not render on
    // most social platforms, so fall back to the layout default in that case.
    $firstPath = $images->first()?->path;
    $ogImage = $firstPath && preg_match('/\.(jpe?g|png|webp)$/i', $firstPath)
        ? asset('storage/' . ltrim($firstPath, '/'))
        : null;

    // Mobile sticky buy bar + "Order on WhatsApp" — display price for the bar,
    // read from the same variant rows the buy box and JSON-LD offer use.
    $min = $product->price_min_amount;
    $max = $product->price_max_amount;
    $hasRange = $min && $max && $min->minorUnits !== $max->minorUnits;
    $stickyPrice = $hasRange
        ? $min->format() . ' – ' . $max->format()
        : (($min ?? $max)?->format() ?? '');

    // WhatsApp order link carries the product name, price and URL as prefilled
    // text — the highest-leverage COD conversion path in this market. Built from
    // the store number (single source of truth) with the standard fallback.
    $waOrderMessage = 'Assalam o Alaikum, I want to order: ' . $product->name
        . ($stickyPrice ? ' (' . $stickyPrice . ')' : '') . '. ' . $canonical;
    $whatsappOrderHref = ($store ?? null)?->whatsappLink($waOrderMessage)
        ?? 'https://wa.me/923001234567?text=' . urlencode($waOrderMessage);
@endphp

<x-layouts.app
    :title="$metaTitle . ' | Glow Halal'"
    :description="$metaDescription"
    :canonical="$canonical"
    :og-image="$ogImage"
    :robots="$robots"
    og-type="product"
    current="shop"
    :bottom-nav="false">

    <x-ui.section>
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-2 text-meta text-text-secondary">
                @foreach ($trail as $i => $crumb)
                    <li class="flex items-center gap-2">
                        @if ($i === array_key_last($trail))
                            <span aria-current="page" class="text-text-primary">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="no-underline hover:underline decoration-1 underline-offset-4">{{ $crumb['name'] }}</a>
                            <x-ui.icon name="chevron-right" :size="14" class="text-text-muted" />
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">

            {{-- Gallery ------------------------------------------------------ --}}
            <div class="flex flex-col gap-4">
                {{-- object-contain on a white plate so the WHOLE bottle shows at a
                     consistent square size, whatever the photo's aspect ratio —
                     never a cropped close-up. --}}
                <div class="product-plate overflow-hidden rounded-lg bg-white">
                    <img src="{{ $hero }}"
                        alt="{{ $images->first()?->alt_text ?? $product->name . ', on a white background' }}"
                        width="900" height="900" fetchpriority="high" decoding="async"
                        sizes="(min-width: 1024px) 45vw, 100vw"
                        class="aspect-square w-full bg-white object-contain p-6 md:p-8">
                </div>

                @if ($images->count() > 1)
                    <ul class="grid grid-cols-4 gap-3" aria-label="More views">
                        @foreach ($images->skip(1)->take(4) as $image)
                            <li class="product-plate overflow-hidden rounded-sm bg-white">
                                <img src="{{ asset('storage/' . ltrim($image->path, '/')) }}"
                                    alt="{{ $image->alt_text ?? $product->name }}"
                                    width="220" height="220" loading="lazy" decoding="async"
                                    class="aspect-square w-full bg-white object-contain p-2">
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Buy box ------------------------------------------------------ --}}
            <div class="flex flex-col gap-6">
                <div>
                    @if ($category)
                        <x-ui.overline>{{ $category->name }}</x-ui.overline>
                    @endif

                    <h1 class="mt-3 font-display text-display-sm">{{ $product->name }}</h1>

                    @if ($product->short_description)
                        <p class="mt-4 text-lead text-text-secondary">{{ $product->short_description }}</p>
                    @endif
                </div>

                @if ($product->verifiedFreeFromAttributes->isNotEmpty())
                    <div>
                        <h2 class="text-overline uppercase text-text-gold">Formulated without</h2>
                        <ul class="mt-3 flex flex-wrap gap-2">
                            @foreach ($product->verifiedFreeFromAttributes as $attribute)
                                <li><x-ui.free-from-chip :inci="$attribute->name" /></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- #pdp-buy-box is the IntersectionObserver target for the
                     mobile sticky buy bar below: the bar appears only while this
                     real buy box is out of view. --}}
                <div id="pdp-buy-box">
                    <livewire:cart.add-to-cart :product="$product" />
                </div>

                <dl class="grid gap-3 border-t border-border-subtle pt-6 text-meta text-text-secondary">
                    <div class="flex gap-3">
                        <dt class="font-semibold text-text-primary">Delivery</dt>
                        <dd>2–4 working days to Karachi, Lahore, Islamabad, Rawalpindi and Faisalabad. 4–7 days elsewhere in Pakistan.</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="font-semibold text-text-primary">Payment</dt>
                        <dd>Cash on Delivery, or bank transfer. No account needed either way.</dd>
                    </div>
                    @if ($product->return_window_days)
                        <div class="flex gap-3">
                            <dt class="font-semibold text-text-primary">Returns</dt>
                            <dd>{{ $product->return_window_days }} days from delivery, unopened.</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </x-ui.section>

    {{-- Ingredients — the reason this store exists ------------------------- --}}
    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline>Every ingredient, named</x-ui.overline>
            <h2 class="mt-3 font-display text-display-sm">What is in it</h2>

            @if ($product->ingredients_note)
                <p class="mt-6 font-mono text-inci text-text-primary">{{ $product->ingredients_note }}</p>
                <p class="mt-3 text-meta text-text-muted">
                    INCI order — listed by descending concentration down to 1%, then in any order.
                    This is the same list printed on the box.
                </p>
            @else
                <p class="mt-6 text-body text-text-secondary">
                    The full INCI list for this formulation is not published yet. We will not
                    guess at it here — ask us and we will send the supplier documentation.
                </p>
            @endif

            @if ($product->ingredients->isNotEmpty())
                <h3 class="mt-10 text-title">Key ingredients, and why they are in here</h3>

                <ul class="mt-4 grid gap-4">
                    @foreach ($product->ingredients as $ingredient)
                        <li class="border-t border-border-subtle pt-4">
                            <p class="font-mono text-inci text-text-primary">{{ $ingredient->inci_name ?? $ingredient->name }}</p>
                            @if ($ingredient->function)
                                <p class="mt-1 text-meta text-text-secondary">{{ $ingredient->function }}</p>
                            @endif
                            @if ($ingredient->pivot?->source_note)
                                <p class="mt-1 text-meta text-text-muted">{{ $ingredient->pivot->source_note }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.section>

    @if ($product->description || $product->how_to_use)
        <x-ui.section>
            <div class="max-w-[var(--container-read)] grid gap-4">
                @if ($product->description)
                    {{-- <details open>: every word is in the DOM whether or not the
                         panel is expanded, and it needs no JavaScript. --}}
                    <details open class="border-t border-border-subtle pt-4">
                        <summary class="cursor-pointer list-none">
                            <h2 class="inline text-title">About this formula</h2>
                        </summary>
                        <div class="prose mt-4 text-body text-text-secondary [&_p]:mb-4">{!! $product->description !!}</div>
                    </details>
                @endif

                @if ($product->how_to_use)
                    <details open class="border-t border-border-subtle pt-4">
                        <summary class="cursor-pointer list-none">
                            <h2 class="inline text-title">How to use it</h2>
                        </summary>
                        <div class="mt-4 text-body text-text-secondary [&_ul]:list-disc [&_ul]:ps-5 [&_li]:mb-2">{!! $product->how_to_use !!}</div>
                    </details>
                @endif
            </div>
        </x-ui.section>
    @endif

    {{-- FAQ — dedicated section. All answers are in the DOM (details keeps them
         there), so a crawler and the PAA box can read them without JS. --}}
    @if (!empty($product->faqs))
        <x-ui.section surface="sunken" aria-labelledby="faq-heading">
            <div class="max-w-[var(--container-read)]">
                <x-ui.overline>Questions &amp; answers</x-ui.overline>
                <h2 id="faq-heading" class="mt-3 font-display text-display-sm">Frequently asked questions</h2>

                <div class="mt-6 border-t border-border-subtle">
                    @foreach ($product->faqs as $faq)
                        <details class="group border-b border-border-subtle">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4">
                                <h3 class="text-title-sm text-text-primary">{{ $faq['q'] }}</h3>
                                <x-ui.icon name="chevron-down" :size="20"
                                    class="shrink-0 text-text-muted transition-transform duration-[var(--motion-fast)] ease-standard group-open:rotate-180" />
                            </summary>
                            <p class="pb-4 text-body text-text-secondary">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </x-ui.section>
    @endif

    {{-- Reviews — the REAL review system (App\Models\ProductReview). No
         fabricated reviews, and none copied from anywhere else. Honest empty
         state until a real, approved customer review exists. --}}
    @php
        $approvedReviews = $product->reviews()->approved()->latest()->take(20)->get();
        $showAverage = $product->reviews_count >= 5 && $product->reviews_average;
    @endphp
    <x-ui.section aria-labelledby="reviews-heading">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline>Reviews</x-ui.overline>
            <h2 id="reviews-heading" class="mt-3 font-display text-display-sm">
                {{ $approvedReviews->isNotEmpty() ? 'What customers say' : 'No reviews yet' }}
            </h2>

            @if ($showAverage)
                <p class="mt-3 text-body text-text-primary">
                    <span class="tnum font-semibold">{{ number_format((float) $product->reviews_average, 1) }}</span>
                    out of 5 · {{ $product->reviews_count }} {{ \Illuminate\Support\Str::plural('review', $product->reviews_count) }}
                </p>
            @endif

            @if ($approvedReviews->isNotEmpty())
                <ul class="mt-6 grid gap-6">
                    @foreach ($approvedReviews as $review)
                        <li class="border-t border-border-subtle pt-5">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="text-title-sm text-text-primary">{{ $review->author_name }}</p>
                                @if ($review->order_id)
                                    <span class="inline-flex items-center gap-1 text-meta font-semibold text-success-700">
                                        <x-ui.icon name="check" :size="14" stroke-width="2.5" /> Verified purchase
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 text-body text-text-gold"
                                aria-label="{{ $review->rating }} out of 5 stars">
                                {{ str_repeat('★', (int) $review->rating).str_repeat('☆', 5 - (int) $review->rating) }}
                            </p>
                            @if ($review->title)
                                <p class="mt-2 text-body font-semibold text-text-primary">{{ $review->title }}</p>
                            @endif
                            @if ($review->body)
                                <p class="mt-1 text-body text-text-secondary">{{ $review->body }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-3 max-w-read text-body text-text-secondary">
                    This product has no reviews yet. We only publish reviews from real customers who have bought
                    this product, so this space stays empty until the first one arrives — we never copy reviews from
                    anywhere else. Bought it? Message us on WhatsApp to leave an honest review.
                </p>
            @endif
        </div>
    </x-ui.section>

    {{-- ── Mobile sticky buy bar (design system §4.3) ─────────────────────
         The persistent buy action on mobile. It slides up once the real buy box
         (#pdp-buy-box) scrolls out of view and hides again when the box is back
         in view OR the footer is reached, so it never buries the footer. It
         REUSES the real Livewire Add-to-bag control (it programmatically clicks
         the buy box's own wire:click="add" button) rather than mounting a second
         cart component — so there is no duplicate quantity-input id and the
         drawer still opens on 'added-to-bag'. Mobile only; on lg+ the buy block's
         own stickiness covers this, so it is hidden. --}}
    <div
        x-data="{
            shown: false,
            boxVisible: true,
            footerVisible: false,
            refresh() { this.shown = !this.boxVisible && !this.footerVisible; },
            init() {
                if (!('IntersectionObserver' in window)) return;
                const box = document.getElementById('pdp-buy-box');
                const footer = document.querySelector('footer');
                if (box) {
                    new IntersectionObserver((e) => { this.boxVisible = e[0].isIntersecting; this.refresh(); },
                        { rootMargin: '0px 0px -25% 0px' }).observe(box);
                    this.boxVisible = false;
                }
                if (footer) {
                    new IntersectionObserver((e) => { this.footerVisible = e[0].isIntersecting; this.refresh(); }).observe(footer);
                }
                this.refresh();
            },
            addToBag() {
                const box = document.getElementById('pdp-buy-box');
                const btn = box && Array.from(box.querySelectorAll('button')).find(b => b.getAttribute('wire:click') === 'add');
                if (btn) { btn.click(); }
                else if (box) { box.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            },
        }"
        x-show="shown"
        x-cloak
        x-transition:enter="transition-transform ease-standard duration-[var(--motion-base)]"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition-transform ease-standard duration-[var(--motion-exit)]"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        role="region"
        aria-label="Buy {{ $product->name }}"
        class="fixed inset-x-0 bottom-0 z-[var(--z-sticky-cta)] border-t border-border-subtle bg-surface
            pb-[var(--safe-bottom)] shadow-bar lg:hidden">
        <div class="container-page flex h-[var(--h-buy-bar)] items-center gap-3">
            <div class="min-w-0">
                <p class="truncate text-body font-semibold tabular-nums text-text-primary">{{ $stickyPrice ?: $product->name }}</p>
                <p class="text-meta text-text-muted">Cash on Delivery</p>
            </div>

            <div class="ms-auto flex items-center gap-2">
                <a href="{{ $whatsappOrderHref }}" target="_blank" rel="noopener"
                    class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-sm bg-whatsapp px-3
                        text-meta font-semibold text-white transition-[background-color]
                        duration-[var(--motion-fast)] ease-standard hover:bg-whatsapp-hover"
                    aria-label="Order {{ $product->name }} on WhatsApp (opens in a new tab)">
                    <x-ui.icon name="whatsapp" :size="18" />
                    <span class="hidden xs:inline">WhatsApp</span>
                </a>

                <button type="button" @click="addToBag()"
                    class="inline-flex min-h-11 min-w-[7.5rem] items-center justify-center rounded-sm bg-gold-surface
                        px-5 text-body font-semibold text-ink-900 transition-[background-color]
                        duration-[var(--motion-fast)] ease-standard hover:bg-gold-surface-hover active:bg-gold-surface-active">
                    Add to bag
                </button>
            </div>
        </div>
    </div>

    {{-- Consent-gated analytics reads this (partials/tracking.blade.php) to fire
         GA4 view_item + Meta ViewContent. `data-id` is the default variant SKU —
         the same id used in the product feeds — so nothing fires without a
         tracking ID AND cookie consent. No PII. --}}
    <div data-gh-view-content
        data-id="{{ $offer->sku }}"
        data-name="{{ $product->name }}"
        data-price="{{ $offer->structuredValue() }}"
        hidden></div>

    {{-- Exactly one JSON-LD script per document, at the end of the body,
         outside every island and every re-rendering component. --}}
    <script type="application/ld+json">{!! $schema !!}</script>
</x-layouts.app>
