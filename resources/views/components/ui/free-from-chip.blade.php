@props(['inci', 'href' => null])

{{--
  Design system §3.5 B — the "free from" chip.

  Competitors write "Alcohol-Free". Glow Halal writes the name printed on the
  back of the box the customer already owns. The content is ALWAYS the INCI
  name, in the mono face — never the marketing word.

  The fill and the ink are FIXED palette colours, so they cannot follow the
  dark context on their own — hence the `on-dark:` pairs. Measured:

    light band   ink-800 on ink-100          11.81:1  PASS AAA
                 glyph ink-600 on ink-100     5.83:1  PASS AA
    dark band    dark-text on #26262A         12.9:1  PASS AAA
                 glyph gold-on-dark on it      7.1:1  PASS AAA
  The glyph repeats what the chip already says in words, so it is redundant by
  design and never the only carrier of meaning.
--}}

@php
    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(
        'inline-flex items-center gap-1.5 rounded-full bg-ink-100 px-3 h-8 ' .
        'text-meta font-semibold text-ink-800 no-underline ' .
        'on-dark:bg-white/8 on-dark:text-dark-text on-dark:ring-1 on-dark:ring-white/12 ' .
        ($href
            ? 'hover:bg-ink-200 on-dark:hover:bg-white/14 ' .
              'transition-[background-color] duration-[var(--motion-fast)] ease-standard'
            : '')
    ) }}>
    <x-ui.icon name="cross" :size="14" class="text-ink-600 on-dark:text-gold-on-dark" stroke-width="2" />
    <span class="font-mono">{{ $inci }}</span>
</{{ $tag }}>
