@props(['paginator'])

{{--
  Hand-rolled so every control is a real <a href> with a real ?page=n URL.

  seo.md §8.11: Livewire's default pagination renders <button wire:click>
  controls. Buttons are not crawlable, and if pagination is not a link then
  every product past page 1 is orphaned — no URL, no internal link, no
  indexation. §8.7 forbids island append/prepend here for the same reason.

  Works with JavaScript disabled. That is the acceptance test.
--}}

@if ($paginator->hasPages())
    <nav class="mt-12 flex items-center justify-between gap-4 border-t border-border-subtle pt-6"
        aria-label="Catalogue pagination">

        <div>
            @if ($paginator->onFirstPage())
                <span class="text-meta text-text-muted">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="inline-flex min-h-11 items-center text-meta font-semibold text-text-primary underline decoration-1 underline-offset-4">
                    Previous
                </a>
            @endif
        </div>

        <ol class="flex items-center gap-1">
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                <li>
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            class="grid h-11 min-w-11 place-items-center rounded-sm bg-surface-sunken px-3 text-meta font-semibold tabular-nums text-text-primary">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="grid h-11 min-w-11 place-items-center rounded-sm px-3 text-meta tabular-nums text-text-secondary no-underline hover:bg-surface-sunken hover:text-text-primary">
                            <span class="sr-only">Page </span>{{ $page }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>

        <div>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                    class="inline-flex min-h-11 items-center text-meta font-semibold text-text-primary underline decoration-1 underline-offset-4">
                    Next
                </a>
            @else
                <span class="text-meta text-text-muted">Next</span>
            @endif
        </div>
    </nav>
@endif
