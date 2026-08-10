{{--
  Everything that has to be in the <head> of a content page and is not already
  handled by <x-layouts.app>.

  $schema  — one <script type="application/ld+json"> holding one @graph,
             pre-encoded by App\Support\JsonLd. Blade never builds JSON here.
  $robots  — page-level override, forwarded to <x-layouts.app :robots>. The
             site-wide robots meta is emitted ONCE by the layout (which reads
             the SeoSettings master switch), never here, so it can never be
             emitted twice or bypassed.
  $prev/$next — emitted for Bing, which still reads them.
  $hreflang — reciprocal alternate cluster for bilingual pages, built by the
              controller as [['hreflang' => 'en'|'ur-Latn'|'x-default', 'href' => ...]].
              Emitted ONLY when the page has a published translation sibling; an
              only-child post passes an empty array and gets a self-canonical
              (already in the layout <head>) with no dangling hreflang.
--}}
@isset($prev)
    <link rel="prev" href="{{ $prev }}">
@endisset

@isset($next)
    <link rel="next" href="{{ $next }}">
@endisset

@if (!empty($hreflang ?? []))
    @foreach ($hreflang as $alt)
        <link rel="alternate" hreflang="{{ $alt['hreflang'] }}" href="{{ $alt['href'] }}">
    @endforeach
@endif

{!! $schema !!}
