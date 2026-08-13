{{--
  Catalogue card.

  Navigation is an <a href>; the add-to-bag control is a <button> inside a
  Livewire component. Never the other way round — an <a> that mutates a cart
  invites a crawler to fill baskets, and a <button> that navigates is
  uncrawlable (seo.md §8.11).

  The price is plain server-rendered text. It is the same value the product
  page and its JSON-LD offer emit, because all three read the variant rows.
--}}

@php
    $variants = $product->variants->where('is_active', true);
    $min = $product->price_min_amount;
    $max = $product->price_max_amount;
    $hasRange = $min && $max && $min->minorUnits !== $max->minorUnits;
    $inStock = (int) $product->total_stock > 0;
    $image = $product->primaryImage?->path
        ? asset('storage/' . ltrim($product->primaryImage->path, '/'))
        : '/images/placeholder/product-face-cream.svg';

    // "Order on WhatsApp" — product-context prefilled message (name, price, URL),
    // built from the store number with the standard fallback.
    $priceLabel = $hasRange
        ? $min->format() . ' – ' . $max->format()
        : (($min ?? $max)?->format() ?? '');
    $productUrl = route('products.show', $product->slug);
    $waCardMessage = 'Assalam o Alaikum, I want to order: ' . $product->name
        . ($priceLabel ? ' (' . $priceLabel . ')' : '') . '. ' . $productUrl;
    $waCardHref = ($store ?? null)?->whatsappLink($waCardMessage)
        ?? 'https://wa.me/923012973886?text=' . urlencode($waCardMessage);
@endphp

<article class="flex h-full flex-col">
    <a href="{{ route('products.show', $product->slug) }}"
        class="group block product-plate overflow-hidden focus-visible:outline-2 focus-visible:outline-offset-2">
        <img src="{{ $image }}"
            alt="{{ $product->primaryImage?->alt_text ?? $product->name . ' — Glow Halal' }}"
            width="600" height="600" loading="lazy" decoding="async"
            sizes="(min-width: 768px) 30vw, 50vw"
            class="aspect-square w-full bg-white object-contain p-4 transition-transform duration-[var(--motion-slow)] ease-standard group-hover:scale-[1.02]">
    </a>

    <div class="flex flex-1 flex-col gap-2 pt-4">
        <h2 class="text-title-sm">
            <a href="{{ route('products.show', $product->slug) }}"
                class="no-underline text-text-primary hover:underline decoration-1 underline-offset-4">
                {{ $product->name }}
            </a>
        </h2>

        @if ($product->short_description)
            <p class="clamp-2 text-meta text-text-secondary">{{ $product->short_description }}</p>
        @endif

        @if ($product->verifiedFreeFromAttributes->isNotEmpty())
            <ul class="flex flex-wrap gap-1.5 pt-1" aria-label="Formulated without">
                @foreach ($product->verifiedFreeFromAttributes->take(2) as $attribute)
                    <li><x-ui.free-from-chip :inci="$attribute->name" /></li>
                @endforeach
            </ul>
        @endif

        @php
            // Discount display: default (or only) variant's compare-at price,
            // shown struck-through when it beats the selling price.
            $cardVariant = $variants->firstWhere('is_default', true) ?? $variants->first();
            $cardCompareAt = ! $hasRange
                && $min
                && $cardVariant?->compare_at_amount
                && $cardVariant->compare_at_amount->minorUnits > $min->minorUnits
                    ? $cardVariant->compare_at_amount
                    : null;
        @endphp
        <p class="mt-auto pt-3 text-price-sm tabular-nums text-text-primary">
            @if ($hasRange)
                {{ $min->format() }} – {{ $max->format() }}
            @else
                @if ($cardCompareAt)
                    <s class="me-1 text-meta font-normal text-text-muted">{{ $cardCompareAt->format() }}</s>
                @endif
                {{ ($min ?? $max)?->format() ?? '—' }}
            @endif

            @unless ($inStock)
                <span class="ms-2 text-meta font-normal text-text-muted">Out of stock</span>
            @endunless
        </p>

        <div class="pt-2">
            <livewire:cart.quick-add :product="$product" :wire:key="'quick-add-' . $product->id" />
        </div>

        {{-- Order on WhatsApp — the top COD conversion lever. A plain link (not a
             cart mutation), so it never invites a crawler into a cart URL. --}}
        <a href="{{ $waCardHref }}" target="_blank" rel="noopener"
            class="mt-2 inline-flex min-h-11 items-center gap-1.5 text-meta font-semibold text-text-secondary
                transition-colors hover:text-text-primary"
            aria-label="Order {{ $product->name }} on WhatsApp (opens in a new tab)">
            <x-ui.icon name="whatsapp" :size="16" class="text-whatsapp" />
            Order on WhatsApp
        </a>
    </div>
</article>
