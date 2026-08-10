{{--
  Design system §3.10 — numbered pagination, and SEO §2.3.

  Every control is a real <a href="?page=n">. Never a wire:click, never an
  island append, never infinite scroll. The Ingredient Index and the Journal
  are the crawlable moat for this site; a page number that only exists after a
  JavaScript event does not exist at all as far as a crawler is concerned.

  Shape: [‹] 1 … 4 [5] 6 … 12 [›] — first, last, current ±1, ellipses as inert
  <span>. Disabled prev/next stay focusable so the sequence is not silently
  broken for a keyboard user.
--}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // first, last, current ±1 — deduplicated and sorted.
        $window = collect([1, $lastPage, $current - 1, $current, $current + 1])
            ->filter(fn ($p) => $p >= 1 && $p <= $lastPage)
            ->unique()
            ->sort()
            ->values();

        $base = 'inline-flex h-12 min-w-12 items-center justify-center rounded-sm px-3 text-body tnum';
        $idle = $base . ' border border-border-strong text-text-primary hover:bg-surface-sunken '
            . 'transition-[background-color] duration-[var(--motion-fast)] ease-standard';
        $off = $base . ' border border-border-subtle text-ink-300';
    @endphp

    <nav aria-label="Pagination" class="mt-10 flex flex-wrap items-center gap-2">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="{{ $off }}" aria-disabled="true" tabindex="0" aria-label="Previous page, unavailable">
                <x-ui.icon name="chevron-right" :size="20" class="rotate-180" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}" aria-label="Previous page">
                <x-ui.icon name="chevron-right" :size="20" class="rotate-180" />
            </a>
        @endif

        @php $previous = 0; @endphp
        @foreach ($window as $page)
            @if ($page - $previous > 1)
                <span class="inline-flex h-12 min-w-8 items-center justify-center text-body text-text-muted"
                    aria-hidden="true">&hellip;</span>
            @endif

            @if ($page === $current)
                <a href="{{ $paginator->url($page) }}" aria-current="page"
                    class="{{ $base }} border border-ink-900 bg-ink-900 font-semibold text-white">{{ $page }}</a>
            @else
                <a href="{{ $paginator->url($page) }}" class="{{ $idle }}"
                    aria-label="Page {{ $page }}">{{ $page }}</a>
            @endif

            @php $previous = $page; @endphp
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}" aria-label="Next page">
                <x-ui.icon name="chevron-right" :size="20" />
            </a>
        @else
            <span class="{{ $off }}" aria-disabled="true" tabindex="0" aria-label="Next page, unavailable">
                <x-ui.icon name="chevron-right" :size="20" />
            </span>
        @endif

        <p class="ms-auto hidden text-meta text-text-muted md:block">
            Page <span class="tnum">{{ $current }}</span> of <span class="tnum">{{ $lastPage }}</span>
        </p>
    </nav>
@endif
