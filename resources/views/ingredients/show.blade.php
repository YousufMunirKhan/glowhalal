{{--
  /halal-ingredients/{slug} — the ingredient page template (SEO §5.4).

  Only rows with a published glossary page reach this view; everything else
  404s in the controller.

  The structure is fixed on purpose. Consistency across the set is what makes it
  read as one authoritative reference rather than as N thin posts, and it is
  what lets the answer box, the DefinedTerm node and the FAQ block be generated
  from the same fields the reader can see.

  What is deliberately absent: a "reviewed by" line. The template renders one
  only when a real reviewer is recorded against the row. Inventing a credentialed
  scholar to sign off a religious ruling would be the worst thing this site
  could do.
--}}
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head')
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <x-ui.section surface="default" class="!pb-8">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline class="mb-3">Ingredient index</x-ui.overline>

            <h1 class="text-display text-text-primary">Is {{ $ingredient->name }} halal?</h1>

            @if ($ingredient->inci_name)
                <p class="mt-3 select-all font-mono text-inci text-text-secondary">
                    <span class="sr-only">INCI name:</span>{{ $ingredient->inci_name }}
                </p>
            @endif

            {{-- Verdict chip and verdict_summary withheld for now (owner
                 decision): no halal/haram ruling is shown until it can cite a
                 fatwa or a recognised standard. The entry still describes what
                 the ingredient is and how certifying bodies generally treat it. --}}
            @if ($ingredient->description)
                <p class="article-answer-box mt-5 text-lead text-text-primary">{{ $ingredient->description }}</p>
            @endif

            @if ($aliases->isNotEmpty())
                <div class="mt-6">
                    <h2 class="text-overline uppercase text-text-muted">Also printed as</h2>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($aliases as $alias)
                            <li
                                class="inline-flex h-8 select-all items-center rounded-full bg-ink-100 px-3
                                    font-mono text-meta font-semibold text-ink-800">
                                {{ $alias }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </x-ui.section>

    {{-- ── Facts ─────────────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken" class="!py-8">
        {{-- "Status" (the halal_status ruling) withheld for now — see the
             verdict partial. Only descriptive, non-ruling facts remain. --}}
        <dl class="grid max-w-[var(--container-read)] gap-x-8 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-overline uppercase text-text-muted">Origin</dt>
                <dd class="mt-1 text-body text-text-primary">
                    {{ $ingredient->origin?->getLabel() ?? 'Not recorded' }}</dd>
            </div>
            <div>
                <dt class="text-overline uppercase text-text-muted">Function</dt>
                <dd class="mt-1 text-body text-text-primary">{{ $ingredient->function ?? 'Not recorded' }}</dd>
            </div>
        </dl>
    </x-ui.section>

    {{-- ── What it is ────────────────────────────────────────────────────── --}}
    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">What {{ $ingredient->name }} is</h2>
            <p class="mt-3 text-body text-text-secondary">
                {{ $ingredient->description ?: 'A short description has not been written for this entry yet.' }}
            </p>

            {{-- The "why it is treated the way it is" notes (halal_notes) are
                 withheld for now: that is religious reasoning, and nothing that
                 could be read as a ruling is published without a cited fatwa or
                 recognised standard. Restore this gated on a per-entry source. --}}
        </div>
    </x-ui.section>

    {{-- ── Long-form body ────────────────────────────────────────────────── --}}
    @if (filled($ingredient->content))
        <x-ui.section surface="default" class="!pt-0">
            @include('pages.partials.rich-text', ['html' => $ingredient->content])
        </x-ui.section>
    @endif

    {{-- ── How to spot it ────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">How to spot it on a label</h2>
            <p class="mt-3 text-body text-text-secondary">
                An INCI panel lists ingredients by descending concentration down to 1%, then in any order. These are
                the exact strings a manufacturer is permitted to print for this material:
            </p>

            <ul class="mt-4 space-y-2">
                @foreach (collect([$ingredient->inci_name])->merge($aliases)->filter()->unique() as $name)
                    <li class="select-all border-b border-border-subtle pb-2 font-mono text-inci text-text-primary">
                        {{ $name }}</li>
                @endforeach
            </ul>
        </div>
    </x-ui.section>

    {{-- ── Alternatives ──────────────────────────────────────────────────── --}}
    @if ($ingredient->related->isNotEmpty())
        <x-ui.section surface="default">
            <div class="max-w-[var(--container-read)]">
                <h2 class="text-title-lg text-text-primary">See also</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($ingredient->related as $relation)
                        @php
                            $relationHasPage =
                                $relation->has_glossary_page &&
                                $relation->status === \App\Enums\PostStatus::Published;
                        @endphp
                        <li class="border-t border-border-subtle pt-3">
                            <p class="text-title-sm text-text-primary">
                                @if ($relationHasPage)
                                    <a href="/halal-ingredients/{{ $relation->slug }}"
                                        class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $relation->name }}</a>
                                @else
                                    {{ $relation->name }}
                                @endif
                            </p>
                            @if ($relation->description)
                                <p class="clamp-2 mt-1 text-body text-text-secondary">{{ $relation->description }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-ui.section>
    @endif

    {{-- ── Commercial handoff ────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <h2 class="text-title-lg text-text-primary">Glow Halal products made without {{ $ingredient->name }}</h2>

        @if ($productsWithout->isEmpty())
            <p class="mt-3 max-w-[var(--container-read)] text-body text-text-secondary">
                No products are published yet. The full ingredient list for every product appears on its page the
                moment one is.
            </p>
        @else
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($productsWithout as $product)
                    <li class="border-t border-border-subtle pt-4">
                        <p class="text-title-sm text-text-primary">
                            <a href="/products/{{ $product->slug }}"
                                class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $product->name }}</a>
                        </p>
                        @if ($product->short_description)
                            <p class="clamp-2 mt-1 text-body text-text-secondary">{{ $product->short_description }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.section>

    {{-- ── FAQ ───────────────────────────────────────────────────────────── --}}
    @if ($faqs)
        <x-ui.section surface="default">
            <div class="max-w-[var(--container-read)]">
                <h2 class="text-title-lg text-text-primary">Frequently asked questions</h2>

                <dl class="mt-6 space-y-6">
                    @foreach ($faqs as $faq)
                        <div class="border-t border-border-subtle pt-5">
                            <dt class="text-title-sm text-text-primary">{{ $faq['question'] }}</dt>
                            <dd class="mt-2 text-body text-text-secondary">{{ $faq['answer'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </x-ui.section>
    @endif

    {{-- ── Provenance of the entry itself ────────────────────────────────── --}}
    <x-ui.section surface="default" class="!pt-0">
        <div class="max-w-[var(--container-read)] border-t border-border-subtle pt-6 text-meta text-text-muted">
            @if ($ingredient->reviewer && $ingredient->reviewed_at)
                <p>Reviewed by {{ $ingredient->reviewer->name }} on
                    <time
                        datetime="{{ $ingredient->reviewed_at->toDateString() }}">{{ $ingredient->reviewed_at->format('j F Y') }}</time>.
                </p>
            @endif

            <p class="mt-1">
                Last updated
                <time
                    datetime="{{ $ingredient->updated_at?->toDateString() }}">{{ $ingredient->updated_at?->format('j F Y') }}</time>.
                Glow Halal holds no third-party halal accreditation. This entry describes what the ingredient is made
                from and how certifying bodies generally treat it; it is not a ruling of our own.
            </p>

            <p class="mt-4">
                <a href="/what-we-never-use"
                    class="text-text-primary underline decoration-1 underline-offset-[3px] hover:decoration-2">
                    See the full list of ingredients we never use
                </a>
            </p>
        </div>
    </x-ui.section>
</x-layouts.app>
