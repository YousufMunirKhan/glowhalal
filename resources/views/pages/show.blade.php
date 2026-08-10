{{--
  Generic CMS page — Privacy, Terms, Shipping & Returns, FAQ, and anything else
  the owner creates in the admin.

  Body content is authored in the admin's rich editor. If it is empty this
  template says so plainly rather than padding the page with invented policy
  text. A fabricated returns policy or privacy statement is worse than an
  obviously unfinished one: customers act on these, and so do regulators.
--}}

<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current">

    <x-ui.section>
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-caption text-text-secondary">
                @foreach ($crumbs as $crumb)
                    <li class="flex items-center gap-x-2">
                        @if (! empty($crumb['url']) && ! $loop->last)
                            <a href="{{ $crumb['url'] }}" class="underline underline-offset-2 hover:text-text-primary">
                                {{ $crumb['name'] }}
                            </a>
                            <span aria-hidden="true">/</span>
                        @else
                            <span aria-current="page">{{ $crumb['name'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <header class="max-w-prose">
            <h1 class="text-display text-text-primary">{{ $page->title }}</h1>

            @if ($page->updated_at)
                <p class="mt-4 text-caption text-text-secondary">
                    Last updated
                    <time datetime="{{ $page->updated_at->toDateString() }}">
                        {{ $page->updated_at->format('j F Y') }}
                    </time>
                </p>
            @endif
        </header>

        @if (filled($page->content))
            <div class="prose-page mt-10 max-w-prose text-body text-text-primary [&_a]:underline
                [&_a]:underline-offset-2 [&_h2]:mt-10 [&_h2]:text-title [&_h3]:mt-8 [&_h3]:text-subtitle
                [&_li]:mt-2 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:ps-6 [&_p]:mt-4
                [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:ps-6">
                {!! $page->content !!}
            </div>
        @else
            {{-- Honest empty state. Do NOT replace this with generated policy copy. --}}
            <div class="mt-10 max-w-prose rounded-sm border-[1.5px] border-border-subtle bg-surface-sunken p-6">
                <p class="text-body text-text-primary">
                    This page has not been written yet.
                </p>
                <p class="mt-3 text-body-sm text-text-secondary">
                    If you need this information now, please
                    <a href="/contact" class="underline underline-offset-2 hover:text-text-primary">contact us</a>
                    and we will answer directly.
                </p>
            </div>
        @endif
    </x-ui.section>

    {{-- JSON-LD (WebPage + BreadcrumbList), built server-side by JsonLd. It was
         previously passed as a non-existent :schema prop on the layout and
         silently dropped; it is now emitted here, once, at the end of the body.
         $schema is already a complete <script type="application/ld+json"> element
         (JsonLd::render wraps it), so it is echoed raw — never double-wrapped. --}}
    {!! $schema !!}
</x-layouts.app>
