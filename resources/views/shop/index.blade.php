{{--
  Catalogue — /shop and /shop/{category}.

  Everything indexable on this page is plain server-rendered Blade: product
  names, prices, links, category links, pagination. Nothing is behind an
  island, a lazy component, or a client-side fetch. Verify with curl, not with
  a browser (seo.md §8.1).
--}}

<x-layouts.app
    :title="$metaTitle . ' | Glow Halal'"
    :description="$metaDescription"
    :canonical="$canonical"
    :robots="$robots"
    current="shop"
    :cart-count="$cartCount ?? 0">

    <x-slot:head>
        @if ($products->currentPage() > 1)
            <link rel="prev" href="{{ $products->previousPageUrl() }}">
        @endif
        @if ($products->hasMorePages())
            <link rel="next" href="{{ $products->nextPageUrl() }}">
        @endif
    </x-slot:head>

    <x-ui.section>
        <nav aria-label="Breadcrumb" class="mb-6">
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

        <x-ui.overline>{{ $category ? 'Category' : 'The catalogue' }}</x-ui.overline>

        {{-- One H1 per document, identical on every page of a paginated set.
             Appending "Page 2" to an H1 is a duplicate-content own-goal. --}}
        <h1 class="mt-3 font-display text-display">{{ $heading }}</h1>

        @if ($intro)
            <p class="mt-4 max-w-[var(--container-read)] text-lead text-text-secondary">{{ $intro }}</p>
        @endif

        @if ($categories->isNotEmpty())
            <nav aria-label="Product categories" class="mt-8 flex flex-wrap gap-2">
                <a href="{{ route('shop.index') }}"
                    @class([
                        'inline-flex h-11 items-center rounded-full px-4 text-meta font-semibold no-underline',
                        'bg-surface-inverse text-text-on-inverse' => ! $category,
                        'bg-ink-100 text-ink-800 hover:bg-ink-200' => (bool) $category,
                    ])
                    @if (! $category) aria-current="page" @endif>All</a>

                @foreach ($categories as $item)
                    <a href="{{ route('shop.category', $item->slug) }}"
                        @class([
                            'inline-flex h-11 items-center rounded-full px-4 text-meta font-semibold no-underline',
                            'bg-surface-inverse text-text-on-inverse' => $category?->id === $item->id,
                            'bg-ink-100 text-ink-800 hover:bg-ink-200' => $category?->id !== $item->id,
                        ])
                        @if ($category?->id === $item->id) aria-current="page" @endif>{{ $item->name }}</a>
                @endforeach
            </nav>
        @endif

        @if ($products->isEmpty())
            <p class="mt-12 text-lead text-text-secondary">
                Nothing here yet. <a href="{{ route('shop.index') }}" class="text-text-gold underline decoration-1 underline-offset-4">See everything we make</a>.
            </p>
        @else
            <p class="mt-8 text-meta text-text-muted" role="status">
                Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
            </p>

            <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:gap-x-8">
                @foreach ($products as $product)
                    @include('shop.partials.product-card', ['product' => $product])
                @endforeach
            </div>

            @include('shop.partials.pagination', ['paginator' => $products])
        @endif
    </x-ui.section>

    {{-- JSON-LD lives at the end of the body, outside every island and every
         component that re-renders (seo.md §8.6 rule 2, §8.13). Exactly one
         script per document. --}}
    <script type="application/ld+json">{!! $schema !!}</script>
</x-layouts.app>
