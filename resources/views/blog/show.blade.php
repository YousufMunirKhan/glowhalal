{{--
  /blog/{slug} — the Journal post (design system §4.9).

  Body measure is --container-read (704px). The whole article is in the initial
  HTML: no lazy island around the body, no wire:text anywhere near indexable
  copy, no reveal-on-scroll.

  The author block renders only when a real user record is attached to the post.
  A "Glow Halal Team" byline is worse than no byline on this kind of content —
  SEO §5.4 is explicit about it — so the template shows nothing rather than
  inventing a person.
--}}
@php
    // Locale-aware base for in-page links: '' at the root, '/ur-roman' under the
    // Roman-Urdu mirror. Product links intentionally stay locale-neutral.
    $bp = app()->getLocale() === 'ur-Latn' ? '/ur-roman' : '';
@endphp
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head', ['hreflang' => $hreflang ?? []])
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <article>
        <x-ui.section surface="default" class="!pb-6">
            <div class="max-w-[var(--container-read)]">
                @include('blog.partials.meta', ['post' => $post])

                <h1 class="mt-2 text-display text-text-primary">{{ $post->title }}</h1>

                @if ($post->excerpt)
                    <p class="mt-4 text-lead text-text-secondary">{{ $post->excerpt }}</p>
                @endif

                @if ($post->author)
                    <p class="mt-4 text-meta text-text-muted">By {{ $post->author->name }}</p>
                @endif

                {{-- Language switcher — offers the reader the same article in the
                     other language, built from the reciprocal hreflang cluster
                     (only present when a published translation actually exists). --}}
                @php
                    $currentLocale = app()->getLocale();
                    $switchLabels = ['en' => 'Read in English', 'ur-Latn' => 'Roman Urdu میں پڑھیں / parhein'];
                    $altLinks = collect($hreflang ?? [])
                        ->filter(fn ($a) => in_array($a['hreflang'], ['en', 'ur-Latn'], true) && $a['hreflang'] !== $currentLocale)
                        ->unique('hreflang');
                @endphp
                @if ($altLinks->isNotEmpty())
                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        @foreach ($altLinks as $alt)
                            <a href="{{ $alt['href'] }}"
                                class="inline-flex items-center rounded-full border border-border-subtle px-4 py-1.5
                                    text-meta font-semibold text-text-gold no-underline transition-colors
                                    hover:border-text-gold hover:bg-ink-50">
                                {{ $switchLabels[$alt['hreflang']] ?? $alt['hreflang'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($post->cover_image_path)
                <img src="{{ Storage::url($post->cover_image_path) }}" alt="{{ $post->cover_image_alt ?? '' }}"
                    width="1200" height="800" fetchpriority="high" decoding="async"
                    class="mt-6 aspect-3/2 w-full max-w-[var(--container-read)] rounded-sm object-cover">
            @endif
        </x-ui.section>

        <x-ui.section surface="default" class="!pt-0">
            @if (filled($post->content))
                @include('pages.partials.rich-text', ['html' => $post->content])
            @else
                <p class="max-w-[var(--container-read)] text-body text-text-secondary">
                    This entry has no body copy yet.
                </p>
            @endif

            @if ($post->tags->isNotEmpty())
                <ul class="mt-10 flex max-w-[var(--container-read)] flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <li
                            class="inline-flex h-8 items-center rounded-full bg-ink-100 px-3 text-meta
                                font-semibold text-ink-800">
                            {{ $tag->name }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Share row. WhatsApp first — it is how this content actually
                 travels in this market. Plain links, no SDK, no tracking pixel. --}}
            <div class="mt-10 max-w-[var(--container-read)] border-t border-border-subtle pt-6">
                <h2 class="text-overline uppercase text-text-muted">Share this</h2>
                <div class="mt-3 flex flex-wrap gap-3">
                    <x-ui.button variant="whatsapp" size="sm" :external="true"
                        :href="'https://wa.me/?text=' . urlencode($post->title . ' — ' . $canonical)">
                        WhatsApp
                    </x-ui.button>
                    <x-ui.button variant="secondary" size="sm" :external="true"
                        :href="'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($canonical)">
                        Facebook
                    </x-ui.button>
                </div>
            </div>
        </x-ui.section>
    </article>

    {{-- ── Products this post refers to ──────────────────────────────────── --}}
    @if ($post->products->isNotEmpty())
        <x-ui.section surface="sunken">
            <h2 class="text-title-lg text-text-primary">Products mentioned</h2>
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($post->products as $product)
                    <li class="border-t border-border-subtle pt-4">
                        <p class="text-title-sm text-text-primary">
                            <a href="/products/{{ $product->slug }}"
                                class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $product->name }}</a>
                        </p>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    @endif

    {{-- ── Related ───────────────────────────────────────────────────────── --}}
    @if ($related->isNotEmpty())
        <x-ui.section surface="default">
            <h2 class="text-title-lg text-text-primary">Read next</h2>
            <ul class="mt-6 grid gap-8 md:grid-cols-2">
                @foreach ($related as $other)
                    <li>
                        <article class="relative border-t border-border-subtle pt-4">
                            @include('blog.partials.meta', ['post' => $other])
                            <h3 class="mt-2 text-title-sm text-text-primary">
                                <a href="{{ $bp }}/blog/{{ $other->slug }}"
                                    class="after:absolute after:inset-0 after:content-[''] hover:underline
                                        hover:decoration-1 hover:underline-offset-[3px]">{{ $other->title }}</a>
                            </h3>
                        </article>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    @endif
</x-layouts.app>
