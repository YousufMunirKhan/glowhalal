{{--
  /about — research §7.1, design system §4.6, SEO §6.1.

  Not a brand essay. On a zero-authority domain this is the primary evidence
  page and, for the cautious buyer, often the deciding one.

  The body comes from the `pages` row with slug `about` as soon as one is
  written in the admin. Until then this template renders the parts that are
  already true and already in the database — what we publish, and the exclusion
  list with every row linking into the ingredient cluster.

  The founder block, the address and the company registration block render as
  explicit "not published yet" notices. They are NOT filled with plausible
  values. A previous pass invented a founder and a Gulberg address; both were
  false and both had to be removed. The empty state is the honest output, and it
  is also the thing that gets the owner to go and fill the field in.
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
            <x-ui.overline class="mb-3">About</x-ui.overline>

            <h1 class="text-display text-text-primary">About Glow Halal</h1>

            <p class="article-answer-box mt-5 text-lead text-text-secondary">
                Glow Halal is a cosmetics brand in Pakistan built on one commitment: every ingredient in every
                product is published in full, under the name printed on the pack, and so is the list of ingredients
                we refuse to formulate with. We hold no third-party halal accreditation and we do not claim one.
            </p>

            @if ($page && filled($page->content))
                <p class="mt-4 text-meta">
                    <a href="#hamari-kahani" lang="ur-Latn"
                        class="text-text-gold underline decoration-1 underline-offset-4">Roman Urdu mein parhein ↓</a>
                </p>
            @endif
        </div>
    </x-ui.section>

    {{-- ── Editor-written body (Roman Urdu narrative) ─────────────────────
         lang="ur-Latn" makes this a correctly-tagged Urdu-Latin block inside
         the lang="en" document — the fix for mixed-language ambiguity is
         tagging, not a second URL (SEO consult, Aug 2026). ur-Latn is Latin
         script: LTR, never dir="rtl". --}}
    @if ($page && filled($page->content))
        <x-ui.section surface="default" class="!pt-0">
            <section lang="ur-Latn" id="hamari-kahani">
                @include('pages.partials.rich-text', ['html' => $page->content])
            </section>
        </x-ui.section>
    @endif

    {{-- ── What we publish ───────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <h2 class="text-title-lg text-text-primary">What we publish</h2>
        <p class="mt-3 max-w-[var(--container-read)] text-body text-text-secondary">
            There are two claims on this site and no others. Both are checkable by you, on the page, without
            contacting us.
        </p>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="evidence-plate">
                <h3 class="text-title-sm text-text-primary">The full INCI list, on every product</h3>
                <p class="mt-2 text-body text-text-secondary">
                    Not the highlights, not the actives, not &ldquo;and other ingredients&rdquo;. The complete
                    declaration, in the order it appears on the pack, as selectable text you can search — never as a
                    photograph of a label.
                </p>
            </div>

            <div class="evidence-plate">
                <h3 class="text-title-sm text-text-primary">The full list of what we exclude</h3>
                <p class="mt-2 text-body text-text-secondary">
                    Published with the reasoning for each entry, not just the fact of it, and separated into what we
                    never use at all and what we use only against a documented plant source.
                </p>
            </div>
        </div>
    </x-ui.section>

    {{-- ── The exclusion list, as internal links ─────────────────────────── --}}
    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">Ingredients we will never use</h2>
            <p class="mt-3 text-body text-text-secondary">
                Each of these is excluded from every formula. The reasoning for each one, and the exact strings it
                hides behind on a label, are on
                <a href="/what-we-never-use"
                    class="underline decoration-1 underline-offset-[3px] hover:decoration-2">the exclusion list</a>.
            </p>

            @if ($excluded->isEmpty())
                <p class="mt-4 text-body text-text-muted">
                    No exclusions have been recorded in the admin yet.
                </p>
            @else
                <ul class="mt-5 grid gap-x-8 gap-y-1 sm:grid-cols-2">
                    @foreach ($excluded as $item)
                        @php
                            $hasPage =
                                $item->has_glossary_page && $item->status === \App\Enums\PostStatus::Published;
                        @endphp
                        <li class="border-b border-border-subtle py-2">
                            <span class="text-body text-text-primary">
                                @if ($hasPage)
                                    <a href="/halal-ingredients/{{ $item->slug }}"
                                        class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $item->name }}</a>
                                @else
                                    {{ $item->name }}
                                @endif
                            </span>
                            @if ($item->inci_name)
                                <span
                                    class="ms-2 select-all font-mono text-inci text-text-muted">{{ $item->inci_name }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-ui.section>

    {{-- ── What we're not ────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">What we&rsquo;re not</h2>
            <p class="mt-4 text-lead text-text-primary">
                We are new, we are small, and we hold no halal certificate from any body. We are not going to print a
                seal we did not earn, and we are not going to describe a supplier&rsquo;s paperwork as an approval of
                ours.
            </p>
            <p class="mt-4 text-body text-text-secondary">
                What we do instead is show our work. Every ingredient is named, every exclusion is explained, and
                where the honest answer is &ldquo;it depends on the source&rdquo; we say that rather than rounding it
                up into a claim. Set against a certificate you cannot verify, that is a fair trade — and it is the
                only one we can make truthfully today.
            </p>
        </div>
    </x-ui.section>

    {{-- ── The people — renders ONLY when a real founder is set in Store
         settings. No placeholder person and no "coming soon" apology either:
         the owner chose not to publish a founder, so the section simply does
         not exist until real data does. --}}
    @if ($store->hasFounder() || filled($store->brand_story))
        <x-ui.section surface="default">
            <div class="max-w-[var(--container-read)]">
                <h2 class="text-title-lg text-text-primary">Who is behind this</h2>

                @if ($store->hasFounder())
                    <div class="mt-6 flex flex-col gap-6 sm:flex-row sm:items-start">
                        <img src="{{ \App\Support\StorefrontChrome::founderPhoto() }}"
                            alt="{{ $store->founder_name }}, founder of Glow Halal" width="160" height="160"
                            loading="lazy" decoding="async"
                            class="h-40 w-40 shrink-0 rounded-sm bg-surface-sunken object-cover object-top">

                        <div>
                            <p class="text-display-sm text-text-primary">{{ $store->founder_name }}</p>
                            @if ($store->founder_title)
                                <p class="mt-1 text-body text-text-secondary">{{ $store->founder_title }}</p>
                            @endif
                            @if ($store->city)
                                <p class="mt-1 text-meta text-text-muted">{{ $store->city }}</p>
                            @endif
                            <p class="mt-4 text-body text-text-secondary">{{ $store->founder_bio }}</p>
                        </div>
                    </div>
                @endif

                @if (filled($store->brand_story))
                    <div class="mt-8">
                        @include('pages.partials.rich-text', ['html' => $store->brand_story])
                    </div>
                @endif
            </div>
        </x-ui.section>
    @endif

    {{-- ── Where we are ──────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">Where we are</h2>

            @if ($store->formattedAddress())
                <address class="mt-4 select-all text-body not-italic text-text-primary">
                    {{ $store->formattedAddress() }}
                </address>
                <p class="mt-2 text-meta text-text-muted">We ship across Pakistan.</p>
            @else
                {{-- TODO (owner): Store settings → address line, city, postal
                     code. An address is a trust instrument; an invented one is
                     a liability. --}}
                <p class="mt-4 text-body text-text-muted">
                    Our address has not been published yet. We ship across Pakistan; you can reach us on the
                    <a href="/contact"
                        class="text-text-primary underline decoration-1 underline-offset-[3px] hover:decoration-2">contact
                        page</a>.
                </p>
            @endif

            {{-- Company registration identifiers (registered legal name, NTN,
                 STRN, SECP number) are deliberately absent. The owner has not
                 supplied them and they must never be guessed. --}}
        </div>
    </x-ui.section>

    <x-ui.section surface="default">
        <div class="flex max-w-[var(--container-read)] flex-wrap gap-3">
            <x-ui.button href="/shop" variant="primary" size="lg">See the collection</x-ui.button>
            <x-ui.button href="/what-we-never-use" variant="secondary" size="lg">What we never use</x-ui.button>
        </div>
    </x-ui.section>
</x-layouts.app>
