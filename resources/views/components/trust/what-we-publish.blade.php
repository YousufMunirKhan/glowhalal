@props([
    'neverUseTotal' => 8,
    'products' => [],
    'headingId' => 'what-we-publish',
])

{{--
  Slot 4 of the homepage — the third-party evidence slot.

  Glow Halal holds NO halal accreditation and claims none. There is no issuing
  body, reference number, standard, seal, lab, audit or "approval pending"
  status to display, and none may ever be added to this component. A halal
  claim is a religious compliance claim; inventing one is a false statement,
  not a placeholder.

  So this slot keeps its rank (second thing on the page) and its
  `evidence-plate` chassis, and does the only honest version of the job: it
  states what we publish, and it states plainly what we do not have. A brand
  that volunteers an inconvenient truth earns more credibility than one that
  manufactures a convenient one.

  Nothing here is baked into an image — every claim is crawlable, selectable
  and translatable text.

  The 3px gold inline-start rule (from the `evidence-plate` utility) is the
  only gold in the block and is purely decorative — 2.08:1 on the tint, which
  is fine because decoration is contrast-exempt. It never indicates anything.
--}}

@php
    $ingredientTotal = collect($products)->sum(fn ($p) => (int) ($p['ingredient_count'] ?? 0));

    $rows = [
        [
            'label' => 'Every ingredient',
            'body' => 'All ' . $ingredientTotal . ' ingredients across the ' . count($products) . ' products we sell '
                . 'are published by INCI name on the product page, in descending order of concentration. '
                . 'Not summarised, not cropped into a photograph of the box.',
            'href' => '/halal-ingredients',
            'cta' => 'Ingredient index',
        ],
        [
            'label' => 'Everything we exclude',
            'body' => 'The ' . $neverUseTotal . ' ingredients we will not formulate with, listed under the exact '
                . 'names that appear on a label — so you can check ours against whatever is already in your bathroom.',
            'href' => '/what-we-never-use',
            'cta' => 'What we never use',
        ],
        [
            'label' => 'What we do not have',
            'body' => 'We hold no halal accreditation from any body, and we do not display a seal, a badge or '
                . 'a standard number. What we can show you is the ingredient list, in full, every time.',
            'href' => null,
            'cta' => null,
        ],
    ];
@endphp

<section aria-labelledby="{{ $headingId }}" {{ $attributes->class('evidence-plate') }}>

    <h2 id="{{ $headingId }}" class="font-display text-display-sm tracking-display text-text-primary">
        What we publish
    </h2>

    <p class="mt-3 max-w-read text-body text-text-secondary">
        Halal cosmetics are usually sold on a badge. We do not have one, so we are selling on the
        thing a badge is supposed to stand for: the list.
    </p>

    <dl class="mt-6">
        @foreach ($rows as $row)
            <div class="border-t border-border-subtle py-4 md:flex md:gap-8">
                {{-- Semantic gold, not ink-800: this plate now sits on a dark
                     band, where --text-gold resolves to #C9A961 (7.57:1 on
                     #1C1C1C, PASS AAA) and ink-800 would measure 1.25:1. --}}
                <dt class="text-meta font-semibold uppercase tracking-caps text-text-gold
                    md:w-44 md:shrink-0">{{ $row['label'] }}</dt>
                <dd class="mt-1 max-w-read text-body text-text-primary md:mt-0">
                    {{ $row['body'] }}
                    @if ($row['href'])
                        <a href="{{ $row['href'] }}"
                            class="mt-1 inline-flex items-center gap-1 text-text-primary underline
                                decoration-1 underline-offset-[3px] hover:decoration-2">
                            {{ $row['cta'] }}
                            <x-ui.icon name="arrow-right" :size="16" />
                        </a>
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
</section>
