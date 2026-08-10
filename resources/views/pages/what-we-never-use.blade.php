{{--
  /what-we-never-use — design system §3.7, research §T2.

  Hard constraints, all of them load-bearing:
    1. No accordion, no "read more", no lazy loading. The whole table is in the
       initial server-rendered HTML.
    2. Authored ONCE as a semantic <table>; the `inci-table` utility restyles it
       to stacked cards below 768px via display:block and
       ::before{content:attr(data-label)}. One markup, two layouts, and the
       screen-reader experience stays tabular at both.
    3. INCI names are selectable mono text and are never inside an image.
    4. Every row carries a stable id so a "free from" chip elsewhere can
       deep-link to it.

  There is no certificate on this page, no seal, no issuing body and no
  standard number, because there is none to show. The table IS the claim.
--}}
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head')
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    {{-- ── Statement ─────────────────────────────────────────────────────── --}}
    <x-ui.section surface="default" class="!pb-8">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline class="mb-3">The exclusion list</x-ui.overline>

            <h1 class="text-display text-text-primary">What we never use</h1>

            {{-- Featured-snippet target: a direct answer, ~50 words, immediately
                 under the H1 and before any preamble (SEO §3.4). --}}
            <p class="article-answer-box mt-5 text-lead text-text-secondary">
                Glow Halal will not formulate with the ingredients below. Each one is listed under the exact name a
                manufacturer is allowed to print on the pack, with the reason it is excluded rather than just the
                fact of it. We hold no third-party halal accreditation and do not claim one — this list, and the
                full ingredient list on every product, are the whole of our argument.
            </p>

            <p class="mt-4 text-body text-text-secondary">
                <span class="tnum font-semibold text-text-primary">{{ $excluded->count() }}</span>
                excluded outright,
                <span class="tnum font-semibold text-text-primary">{{ $sourceChecked->count() }}</span>
                permitted only against a documented plant source. Read the
                <a href="/halal-ingredients"
                    class="underline decoration-1 underline-offset-[3px] hover:decoration-2">full ingredient
                    index</a>
                for everything else we have looked at.
            </p>

            {{-- §3.7 constraint 5. Progressive enhancement: the button is hidden
                 until the script confirms the clipboard API exists, so a reader
                 without JavaScript never sees a control that cannot work. --}}
            <div class="mt-6">
                <button type="button" id="copy-exclusions" hidden
                    class="inline-flex min-h-11 items-center gap-2 rounded-xs text-body font-semibold text-text-gold
                        underline decoration-1 underline-offset-[3px] hover:text-text-primary hover:decoration-2">
                    <x-ui.icon name="copy" :size="18" />
                    <span data-copy-label>Copy this list as plain text</span>
                </button>
                <span id="copy-exclusions-status" role="status" class="sr-only"></span>
            </div>
        </div>
    </x-ui.section>

    {{-- ── The table ─────────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken" class="!pt-8">
        <h2 class="text-title-lg text-text-primary">Excluded from every formula</h2>
        <p class="mt-3 max-w-[var(--container-read)] text-body text-text-secondary">
            These are not used at any concentration, in any product, for any shade.
        </p>

        @if ($excluded->isEmpty())
            @include('pages.partials.empty-state', [
                'heading' => 'The exclusion list has not been published yet',
                'body' => 'Ingredient records are added in the admin. Nothing is listed here until one exists.',
                'actionLabel' => 'Browse the ingredient index',
                'actionHref' => '/halal-ingredients',
            ])
        @else
            <div class="mt-6" id="exclusion-table">
                <table class="inci-table w-full border-collapse text-start md:border md:border-border-subtle">
                    <caption class="sr-only">
                        Ingredients Glow Halal never uses, with the names they appear under on a label, why each is
                        excluded, and what is used in their place.
                    </caption>

                    <thead>
                        {{-- "Why we don't use it" column removed: it rendered
                             halal_notes (unsourced religious reasoning). Only
                             factual columns remain. --}}
                        <tr class="bg-ink-100 text-start">
                            <th scope="col" class="w-[26%] px-4 py-3 text-start text-overline uppercase text-ink-800">
                                Ingredient</th>
                            <th scope="col" class="w-[38%] px-4 py-3 text-start text-overline uppercase text-ink-800">
                                On the label</th>
                            <th scope="col" class="w-[36%] px-4 py-3 text-start text-overline uppercase text-ink-800">
                                What we use instead</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($excluded as $item)
                            @php
                                $labelNames = collect([$item->inci_name])
                                    ->merge($item->aliases ?? [])
                                    ->filter()
                                    ->unique()
                                    ->values();
                                $hasPage =
                                    $item->has_glossary_page &&
                                    $item->status === \App\Enums\PostStatus::Published;
                            @endphp

                            <tr id="exclusion-{{ $item->slug }}"
                                class="scroll-mt-24 target:bg-surface-evidence md:border-t md:border-border-subtle">

                                <td data-label="Ingredient" class="px-0 py-0 align-top md:px-4 md:py-4">
                                    <span class="text-title-sm text-text-primary md:text-body md:font-semibold">
                                        @if ($hasPage)
                                            <a href="/halal-ingredients/{{ $item->slug }}"
                                                class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $item->name }}</a>
                                        @else
                                            {{ $item->name }}
                                        @endif
                                    </span>
                                    <span class="mt-2 block md:mt-3">
                                        @include('pages.partials.verdict', [
                                            'status' => $item->halal_status,
                                            'label' => 'Never used',
                                        ])
                                    </span>
                                </td>

                                <td data-label="On the label" class="px-0 py-0 align-top md:px-4 md:py-4">
                                    <ul class="space-y-0.5">
                                        @foreach ($labelNames as $name)
                                            <li class="select-all font-mono text-inci text-text-primary">{{ $name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>

                                <td data-label="What we use instead" class="px-0 py-0 align-top md:px-4 md:py-4">
                                    @if ($item->related->isNotEmpty())
                                        <ul class="space-y-1">
                                            @foreach ($item->related as $alt)
                                                <li class="text-body text-text-primary">
                                                    {{ $alt->name }}
                                                    @if ($alt->inci_name && $alt->inci_name !== $alt->name)
                                                        <span
                                                            class="block select-all font-mono text-inci text-text-secondary">{{ $alt->inci_name }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-body text-text-muted">
                                            <span aria-hidden="true">&mdash;</span>
                                            <span class="sr-only">No substitute recorded</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.section>

    {{-- ── Source-dependent ──────────────────────────────────────────────── --}}
    @if ($sourceChecked->isNotEmpty())
        <x-ui.section surface="default">
            <h2 class="text-title-lg text-text-primary">Used only against a documented plant source</h2>
            <p class="mt-3 max-w-[var(--container-read)] text-body text-text-secondary">
                These are chemically identical whether the feedstock was a plant or an animal, and the INCI name is
                the same either way. A label cannot settle them; supplier documentation has to. We list them
                separately rather than printing a blanket &ldquo;free from&rdquo; claim we could not defend.
            </p>

            <div class="mt-6">
                <table class="inci-table w-full border-collapse md:border md:border-border-subtle">
                    <caption class="sr-only">
                        Ingredients Glow Halal uses only when the supplier has evidenced a plant source.
                    </caption>
                    <thead>
                        {{-- "What has to be evidenced" column removed: it rendered
                             halal_notes (unsourced religious reasoning). --}}
                        <tr class="bg-ink-100">
                            <th scope="col" class="w-[32%] px-4 py-3 text-start text-overline uppercase text-ink-800">
                                Ingredient</th>
                            <th scope="col" class="w-[68%] px-4 py-3 text-start text-overline uppercase text-ink-800">
                                On the label</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sourceChecked as $item)
                            @php
                                $labelNames = collect([$item->inci_name])
                                    ->merge($item->aliases ?? [])
                                    ->filter()
                                    ->unique()
                                    ->values();
                                $hasPage =
                                    $item->has_glossary_page &&
                                    $item->status === \App\Enums\PostStatus::Published;
                            @endphp
                            <tr id="exclusion-{{ $item->slug }}"
                                class="scroll-mt-24 target:bg-surface-evidence md:border-t md:border-border-subtle">
                                <td data-label="Ingredient" class="px-0 py-0 align-top md:px-4 md:py-4">
                                    <span class="text-title-sm text-text-primary md:text-body md:font-semibold">
                                        @if ($hasPage)
                                            <a href="/halal-ingredients/{{ $item->slug }}"
                                                class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $item->name }}</a>
                                        @else
                                            {{ $item->name }}
                                        @endif
                                    </span>
                                    <span class="mt-2 block md:mt-3">
                                        @include('pages.partials.verdict', ['status' => $item->halal_status])
                                    </span>
                                </td>
                                <td data-label="On the label" class="px-0 py-0 align-top md:px-4 md:py-4">
                                    <ul class="space-y-0.5">
                                        @foreach ($labelNames as $name)
                                            <li class="select-all font-mono text-inci text-text-primary">{{ $name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.section>
    @endif

    {{-- ── The claims these exclusions add up to ─────────────────────────── --}}
    @if ($claims->isNotEmpty())
        <x-ui.section surface="sunken">
            <h2 class="text-title-lg text-text-primary">The claims this list lets us make</h2>
            <p class="mt-3 max-w-[var(--container-read)] text-body text-text-secondary">
                Each of these is a filter on the shop, and each one means something specific. The wording matters:
                &ldquo;alcohol-free&rdquo; is a claim about ethanol, not about the fatty alcohols that share the word.
            </p>

            <dl class="mt-6 grid gap-x-8 gap-y-6 md:grid-cols-2">
                @foreach ($claims as $claim)
                    <div class="border-t border-border-subtle pt-4">
                        <dt class="text-title-sm text-text-primary">{{ $claim->name }}</dt>
                        <dd class="mt-1 text-body text-text-secondary">
                            {{ $claim->short_description ?: $claim->description }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.section>
    @endif

    {{-- ── FAQ ───────────────────────────────────────────────────────────── --}}
    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">Questions we get about this list</h2>

            <dl class="mt-6 space-y-6">
                @foreach ($faqs as $faq)
                    <div class="border-t border-border-subtle pt-5">
                        <dt class="text-title-sm text-text-primary">{{ $faq['question'] }}</dt>
                        <dd class="mt-2 text-body text-text-secondary">{{ $faq['answer'] }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-10 flex flex-wrap gap-3">
                <x-ui.button href="/shop" variant="primary" size="lg">See the collection</x-ui.button>
                <x-ui.button href="/halal-ingredients" variant="secondary" size="lg">Search the ingredient
                    index</x-ui.button>
            </div>
        </div>
    </x-ui.section>

    <script>
        // Progressive enhancement only (§3.7 constraint 5). Everything this
        // button does is already readable on the page; it just makes the list
        // shareable in a WhatsApp message, which is how content actually
        // travels in this market.
        (function () {
            var button = document.getElementById('copy-exclusions');
            var table = document.getElementById('exclusion-table');
            if (!button || !table || !navigator.clipboard) return;

            button.hidden = false;

            button.addEventListener('click', function () {
                var lines = ['Ingredients Glow Halal never uses', ''];

                table.querySelectorAll('tbody tr').forEach(function (row) {
                    var cells = row.querySelectorAll('td');
                    if (cells.length < 3) return;
                    var name = cells[0].innerText.trim().split('\n')[0];
                    var inci = cells[1].innerText.trim().replace(/\s*\n\s*/g, ', ');
                    lines.push(name + ' — ' + inci);
                });

                lines.push('', document.querySelector('link[rel=canonical]').href);

                navigator.clipboard.writeText(lines.join('\n')).then(function () {
                    var label = button.querySelector('[data-copy-label]');
                    var original = label.textContent;
                    label.textContent = 'Copied';
                    document.getElementById('copy-exclusions-status').textContent = 'List copied to the clipboard';
                    setTimeout(function () { label.textContent = original; }, 2400);
                });
            });
        })();
    </script>
</x-layouts.app>
