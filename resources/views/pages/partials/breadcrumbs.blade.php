{{--
  Design system §3.9 — breadcrumbs.

  $crumbs: array<int, ['name' => string, 'url' => ?string]>. The LAST item has
  no url — it is the current page, is not a link, and carries aria-current.
  The same array builds the BreadcrumbList JSON-LD in the controller, so the
  visible trail and the markup can never disagree (SEO §4.5).

  Mobile overflow is a single scrolling line, never an ellipsis: a shortened
  trail is worse than a scrollable one.
--}}
@php
    $crumbs = array_values($crumbs ?? []);
    $last = count($crumbs) - 1;
@endphp

@if ($crumbs)
    <nav aria-label="Breadcrumb" class="container-page">
        <ol class="flex items-center gap-0 overflow-x-auto whitespace-nowrap py-1 text-meta
            [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($crumbs as $i => $crumb)
                <li class="flex items-center">
                    @if ($i > 0)
                        <span aria-hidden="true" class="mx-2 text-ink-300">&rsaquo;</span>
                    @endif

                    @if ($i === $last || empty($crumb['url']))
                        <span aria-current="page"
                            class="flex min-h-11 items-center font-semibold text-text-primary">{{ $crumb['name'] }}</span>
                    @else
                        <a href="{{ $crumb['url'] }}"
                            class="flex min-h-11 items-center text-text-muted underline decoration-1
                                underline-offset-[3px] hover:text-text-primary hover:decoration-2">{{ $crumb['name'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
