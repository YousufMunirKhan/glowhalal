{{--
  The 404 page, in the site's own design (SEO §7.8).

  Returns a real HTTP 404 — Laravel's error renderer handles the status, and it
  must never become a 200 "not found" page, which Google reads as a soft 404 and
  which teaches it to distrust the rest of the site's status codes.

  It is a routing surface, not an apology: a search box, and the three
  destinations that actually answer why someone is here. `noindex, follow` —
  the links out still pass.

  The chrome data is resolved defensively. This view has no controller, and an
  exception thrown while rendering an error page turns a clean 404 into a 500.
--}}
@php
    try {
        $chrome = \App\Support\StorefrontChrome::layout();
    } catch (\Throwable $e) {
        $chrome = [
            'current' => null,
            'products' => [],
            'company' => ['name' => 'Glow Halal', 'address' => '', 'landline' => '', 'mobile' => '', 'email' => ''],
            'founder' => [
                'photo' => '/images/placeholder/founder.svg',
                'footer_quote' => 'Every ingredient we use is named on the page.',
                'name' => '',
                'city' => '',
            ],
        ];
    }
@endphp

<x-layouts.app title="Page not found | Glow Halal" :products="$chrome['products']" :company="$chrome['company']"
    :founder="$chrome['founder']">

    <x-slot:head>
        <meta name="robots" content="noindex, follow">
    </x-slot:head>

    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline class="mb-3">404</x-ui.overline>

            <h1 class="text-display text-text-primary">That page isn&rsquo;t here</h1>

            <p class="mt-4 text-lead text-text-secondary">
                The link may be out of date, or the address may have a typo in it. Everything below still works.
            </p>

            {{-- Search first: on this site the most common reason to land on a
                 dead URL is looking up an ingredient off the back of a pack. --}}
            <form method="GET" action="/halal-ingredients" role="search"
                class="mt-8 flex max-w-[var(--container-form)] flex-col gap-3 xs:flex-row">
                <div class="relative flex-1">
                    <label for="notfound-search" class="sr-only">Search ingredients</label>
                    <span
                        class="pointer-events-none absolute inset-y-0 start-0 grid w-11 place-items-center text-ink-500">
                        <x-ui.icon name="search" :size="20" />
                    </span>
                    <input id="notfound-search" type="search" name="q" enterkeyhint="search"
                        placeholder="Search an ingredient"
                        class="h-13 w-full rounded-sm border-[1.5px] border-border-strong bg-surface ps-11 pe-4
                            text-body text-text-primary placeholder:text-ink-500 focus:border-ink-900">
                </div>
                <x-ui.button type="submit" variant="primary" size="md">Search</x-ui.button>
            </form>

            <h2 class="mt-10 text-title-lg text-text-primary">Try one of these</h2>

            <ul class="mt-4">
                @foreach ([['Shop the collection', '/shop'], ['What we never use', '/what-we-never-use'], ['The ingredient index', '/halal-ingredients'], ['The Journal', '/blog'], ['Contact us', '/contact']] as [$label, $href])
                    <li class="border-b border-border-subtle">
                        <a href="{{ $href }}"
                            class="flex min-h-14 items-center justify-between text-body text-text-primary hover:underline">
                            {{ $label }}
                            <x-ui.icon name="chevron-right" :size="18" class="text-text-muted" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-ui.section>
</x-layouts.app>
