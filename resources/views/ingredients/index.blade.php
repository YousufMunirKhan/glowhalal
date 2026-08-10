{{--
  /halal-ingredients — the Ingredient Index (design system §4.10, SEO §5.3).

  Search-first: the field is the hero. But the field is a plain GET form and the
  filters are plain links, so the whole page — results, count, letter jump,
  pagination — exists in the initial server-rendered HTML. This is the SEO moat;
  it has to work for a crawler and on a dropped connection. A Livewire
  `wire:model.live` layer may be added on top later, and if it is, this form
  must keep working underneath it.

  Not every entry has a page. An ingredient becomes a link only once it has a
  published glossary page — a stub with three fields on it is thin content, not
  a destination.
--}}
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :robots="$robots ?? null" :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head', [
            'prev' => $ingredients->previousPageUrl(),
            'next' => $ingredients->nextPageUrl(),
        ])
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <x-ui.section surface="default" class="!pb-6">
        <x-ui.overline class="mb-3">Reference</x-ui.overline>

        {{-- The H1 is identical on every page of the set — "Page 2" belongs in
             the <title>, never in the heading (SEO §2.3). --}}
        <h1 class="text-display text-text-primary">Halal Ingredient Index</h1>

        <p class="mt-4 max-w-[var(--container-read)] text-lead text-text-secondary">
            Type any ingredient from the back of a pack. We publish what it is made from and whether we will
            formulate with it — including the answers that are inconvenient for us.
        </p>

        {{-- ── Search. A real GET form. ──────────────────────────────────── --}}
        <form method="GET" action="/halal-ingredients" role="search"
            class="mt-6 flex max-w-[var(--container-form)] flex-col gap-3 xs:flex-row">
            <div class="relative flex-1">
                <label for="ingredient-search" class="sr-only">Search ingredients by name or INCI name</label>

                <span class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-ink-500">
                    <x-ui.icon name="search" :size="20" />
                </span>

                <input id="ingredient-search" type="search" name="q" value="{{ $q }}" enterkeyhint="search"
                    autocomplete="off" placeholder="Try &quot;Alcohol Denat.&quot; or &quot;CI 75470&quot;"
                    class="h-13 w-full rounded-sm border-[1.5px] border-border-strong bg-surface ps-11 pe-4
                        text-body text-text-primary placeholder:text-ink-500 focus:border-ink-900">
            </div>

            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <x-ui.button type="submit" variant="primary" size="md">Search</x-ui.button>
        </form>

        {{-- Verdict (halal/haram) filters withheld for now: the index does not
             sort ingredients by a religious ruling until each ruling can cite a
             fatwa or a recognised standard (owner decision). Search + A–Z
             browsing remain. The controller still accepts ?status= if ever
             re-enabled. --}}

        <p class="mt-5 text-meta text-text-muted" role="status">
            <span class="tnum font-semibold text-text-primary">{{ number_format($ingredients->total()) }}</span>
            @if ($isFiltered)
                of <span class="tnum">{{ number_format($totalCount) }}</span> ingredients match
            @else
                {{ Str::plural('ingredient', $ingredients->total()) }} published
            @endif
        </p>
    </x-ui.section>

    {{-- ── Results ───────────────────────────────────────────────────────── --}}
    <x-ui.section surface="default" class="!pt-0">
        @if ($ingredients->isEmpty())
            @include('pages.partials.empty-state', [
                'announce' => true,
                'icon' => 'search',
                'heading' => $q !== ''
                    ? 'We have not reviewed “' . $q . '” yet'
                    : 'Nothing matches those filters yet',
                'body' =>
                    'Tell us and we will research it — we publish what it is made from and whether we will formulate with it, including the answers that are inconvenient for us.',
                'actionLabel' => 'Ask us about it',
                'actionHref' => '/contact?subject=ingredient',
                'secondaryLabel' => 'Clear the filters',
                'secondaryHref' => '/halal-ingredients',
            ])
        @else
            <ul class="border-t border-border-subtle">
                @foreach ($ingredients as $item)
                    @php
                        $hasPage =
                            $item->has_glossary_page && $item->status === \App\Enums\PostStatus::Published;
                        $aliasLine = collect($item->aliases ?? [])->filter()->take(3)->implode(' · ');
                    @endphp

                    <li class="border-b border-border-subtle">
                        <div class="relative flex items-start gap-4 py-4">
                            <div class="min-w-0 flex-1">
                                <p class="font-mono text-inci text-text-primary">
                                    @if ($hasPage)
                                        {{-- Whole-row link: the <a> is stretched over the row by the
                                             pseudo-element, so the tap target is the full 92px card
                                             without nesting interactive elements. --}}
                                        <a href="/halal-ingredients/{{ $item->slug }}"
                                            class="font-semibold underline decoration-1 underline-offset-[3px]
                                                hover:decoration-2 after:absolute after:inset-0 after:content-['']">
                                            {{ $item->inci_name ?: $item->name }}
                                        </a>
                                    @else
                                        <span class="font-semibold">{{ $item->inci_name ?: $item->name }}</span>
                                    @endif
                                </p>

                                <p class="mt-1 text-meta text-text-muted">
                                    {{ $item->name }}@if ($aliasLine)
                                        · {{ $aliasLine }}
                                    @endif
                                </p>

                                @if ($item->description)
                                    <p class="clamp-2 mt-1 text-body text-ink-700">{{ $item->description }}</p>
                                @endif
                            </div>

                            {{-- Per-row verdict chip withheld for now (see verdict partial). --}}
                        </div>
                    </li>
                @endforeach
            </ul>

            {{ $ingredients->links('pages.partials.pagination') }}
        @endif

        {{-- ── A–Z jump row. Crawlable ?letter= URLs. ────────────────────── --}}
        @if ($activeLetters)
            <nav aria-label="Jump to a letter" class="mt-12 border-t border-border-subtle pt-6">
                <h2 class="text-overline uppercase text-text-muted">Browse A&ndash;Z</h2>
                <ul class="mt-3 flex flex-wrap gap-1">
                    @foreach (range('A', 'Z') as $l)
                        <li>
                            @if (in_array($l, $activeLetters, true))
                                <a href="/halal-ingredients?letter={{ $l }}" rel="nofollow"
                                    @if ($letter === $l) aria-current="true" @endif
                                    class="grid h-11 w-11 place-items-center rounded-xs text-body font-semibold
                                        {{ $letter === $l
                                            ? 'bg-ink-900 text-white'
                                            : 'text-text-primary hover:bg-surface-sunken' }}">{{ $l }}</a>
                            @else
                                <span aria-disabled="true"
                                    class="grid h-11 w-11 place-items-center rounded-xs text-body text-ink-300">{{ $l }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>
        @endif
    </x-ui.section>

    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">Why this index exists</h2>
            <p class="mt-3 text-body text-text-secondary">
                An INCI list is a legal document written in Latin and colour-index numbers, and it is the only
                honest way to check a cosmetic. It is also unreadable unless someone tells you what the names mean.
                This index is that translation, published in full, whether or not it flatters us — the
                <a href="/what-we-never-use"
                    class="underline decoration-1 underline-offset-[3px] hover:decoration-2">list of what we never
                    use</a>
                is the part of it that costs us something.
            </p>
        </div>
    </x-ui.section>
</x-layouts.app>
