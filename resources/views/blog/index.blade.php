{{--
  /blog — the Journal index (design system §4.8).

  Featured post full-width, then 1-up mobile / 2-up ≥768 / 3-up ≥1024, then
  numbered pagination. Never infinite scroll: every post needs a crawlable URL
  and page 2 has to exist in the HTML, not after a JavaScript event.

  This template is also used for /blog/category/{slug}, which passes its own
  heading, intro and crumb trail and suppresses the featured treatment.

  There are no posts in the database yet, so what normally renders here is the
  empty state below. That is the correct output — placeholder articles on a
  brand whose entire position is "we publish what is true" would be
  self-defeating, and the owner's real posts will fill this with no code change.
--}}
@php
    // Locale-aware base for every in-page link: '' at the root, '/ur-roman' under
    // the Roman-Urdu mirror, so navigation never crosses locales.
    $bp = app()->getLocale() === 'ur-Latn' ? '/ur-roman' : '';
@endphp
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head', [
            'prev' => $posts->previousPageUrl(),
            'next' => $posts->nextPageUrl(),
        ])
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <x-ui.section surface="default" class="!pb-6">
        <x-ui.overline class="mb-3">Journal</x-ui.overline>

        {{-- Identical H1 on every page of the set (SEO §2.3). --}}
        <h1 class="text-display text-text-primary">{{ $heading }}</h1>

        @if ($intro && $posts->currentPage() === 1)
            <p class="mt-4 max-w-[var(--container-read)] text-lead text-text-secondary">{{ $intro }}</p>
        @endif

        {{-- Cross-language entry point: from the English Journal to the Roman-Urdu
             one and back. Uses the mirror root, which always exists. --}}
        @php
            $onRoman = app()->getLocale() === 'ur-Latn';
            $otherBlogUrl = $onRoman ? url('/blog') : url('/ur-roman/blog');
            $otherBlogLabel = $onRoman ? 'Read in English' : 'Roman Urdu میں پڑھیں / parhein';
        @endphp
        <a href="{{ $otherBlogUrl }}"
            class="mt-5 inline-flex items-center rounded-full border border-border-subtle px-4 py-1.5 text-meta
                font-semibold text-text-gold no-underline transition-colors hover:border-text-gold hover:bg-ink-50">
            {{ $otherBlogLabel }}
        </a>
    </x-ui.section>

    <x-ui.section surface="default" class="!pt-0">
        @if ($posts->isEmpty())
            @include('pages.partials.empty-state', [
                'heading' => 'Nothing published here yet',
                'body' =>
                    'The Journal is where the reasoning behind our formulation decisions gets written down. Until the first entry is published, the ingredient index is where the detail lives.',
                'actionLabel' => 'Search the ingredient index',
                'actionHref' => '/halal-ingredients',
                'secondaryLabel' => 'See what we never use',
                'secondaryHref' => '/what-we-never-use',
            ])
        @else
            {{-- ── Featured ─────────────────────────────────────────────── --}}
            @if ($featured)
                <article class="border-b border-border-subtle pb-10">
                    @if ($featured->cover_image_path)
                        <a href="{{ $bp }}/blog/{{ $featured->slug }}" tabindex="-1" aria-hidden="true">
                            <img src="{{ Storage::url($featured->cover_image_path) }}"
                                alt="{{ $featured->cover_image_alt ?? '' }}" width="1200" height="800"
                                fetchpriority="high" decoding="async"
                                class="aspect-3/2 w-full rounded-sm object-cover">
                        </a>
                    @endif

                    <div class="mt-4 max-w-[var(--container-read)]">
                        @include('blog.partials.meta', ['post' => $featured])

                        <h2 class="mt-2 text-display-sm text-text-primary">
                            <a href="{{ $bp }}/blog/{{ $featured->slug }}"
                                class="underline decoration-1 underline-offset-[3px] hover:decoration-2">{{ $featured->title }}</a>
                        </h2>

                        @if ($featured->excerpt)
                            <p class="mt-3 text-lead text-text-secondary">{{ $featured->excerpt }}</p>
                        @endif
                    </div>
                </article>
            @endif

            {{-- ── The rest ─────────────────────────────────────────────── --}}
            @if ($rest->isNotEmpty())
                <ul class="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($rest as $post)
                        <li>
                            <article class="relative">
                                @if ($post->cover_image_path)
                                    <img src="{{ Storage::url($post->cover_image_path) }}"
                                        alt="{{ $post->cover_image_alt ?? '' }}" width="600" height="400"
                                        loading="lazy" decoding="async"
                                        class="aspect-3/2 w-full rounded-sm object-cover">
                                @endif

                                <div class="{{ $post->cover_image_path ? 'mt-3' : '' }}">
                                    @include('blog.partials.meta', ['post' => $post])

                                    <h2 class="mt-2 text-title-sm text-text-primary">
                                        <a href="{{ $bp }}/blog/{{ $post->slug }}"
                                            class="clamp-2 after:absolute after:inset-0 after:content-['']
                                                hover:underline hover:decoration-1 hover:underline-offset-[3px]">{{ $post->title }}</a>
                                    </h2>

                                    @if ($post->excerpt)
                                        <p class="clamp-2 mt-2 text-body text-text-secondary">{{ $post->excerpt }}</p>
                                    @endif
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{ $posts->links('pages.partials.pagination') }}
        @endif
    </x-ui.section>

    {{-- ── Browse by topic ───────────────────────────────────────────────── --}}
    @if ($categories->isNotEmpty())
        <x-ui.section surface="sunken">
            <h2 class="text-title-lg text-text-primary">Browse by topic</h2>
            <ul class="mt-4 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <li>
                        <a href="{{ $bp }}/blog/category/{{ $category->slug }}"
                            class="inline-flex min-h-11 items-center rounded-full border border-border-strong px-4
                                text-meta font-semibold text-ink-800 hover:bg-surface
                                transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                            {{ $category->name }}
                            <span class="ms-2 tnum text-text-muted">{{ $category->posts_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </x-ui.section>
    @endif
</x-layouts.app>
