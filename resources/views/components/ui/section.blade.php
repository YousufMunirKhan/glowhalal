@props([
    'surface' => 'default', // default | raised | sunken | inverse | dark | dark-raised | ink
    'contained' => true,
    'as' => 'section',
])

{{--
  Section wrapper. Owns the vertical rhythm (48px mobile / 80px desktop via the
  `section-y` utility) and the surface band, so no page template ever hand-rolls
  a padding value.

  THEME-FOLLOWING surfaces — default / raised / sunken — flip with [data-theme].
  Used for the transactional part of the homepage (delivery, reviews, journal),
  which is the zone where the reader's own theme preference should win.

  FIXED surfaces do not flip, and that is the point.

    inverse   the ink band (#1A1A1A). Text must use text-text-on-inverse
              (14.98:1 PASS AAA); gold inside it must use gold-surface-light
              #E4C87F (10.66:1 PASS AAA) — NOT --text-gold, which is #6A5320
              in the light theme and would measure 2.4:1 on ink.

    dark      #121212, and dark-raised #1C1C1C. These set data-surface="dark",
              which re-points the whole semantic runtime layer (see app.css) —
              so text-text-primary, text-text-secondary, text-text-gold,
              border-border-subtle and bg-surface all resolve to their dark
              values inside the band, in either theme, with no dark: variant on
              any child. This is the homepage's editorial canvas and the one
              context where the brand gold is legal as ink (8.32:1 PASS AAA).

    ink       kept for the founder band: #0F0F0F, one stop deeper than `dark`,
              which is what separates it from the bands either side of it.
--}}

@php
    $surfaces = [
        'default' => 'bg-surface text-text-primary',
        'raised' => 'bg-surface-raised text-text-primary',
        'sunken' => 'bg-surface-sunken text-text-primary',
        'inverse' => 'bg-surface-inverse text-text-on-inverse',
        'dark' => 'bg-dark-bg text-dark-text',
        'dark-raised' => 'bg-dark-surface text-dark-text',
        'ink' => 'bg-ink-950 text-dark-text',
    ];

    // Everything that is fixed-dark opts the subtree into the dark context.
    $isDark = in_array($surface, ['dark', 'dark-raised', 'ink'], true);
@endphp

<{{ $as }} @if ($isDark) data-surface="dark" @endif
    {{ $attributes->class(['section-y', $surfaces[$surface] ?? $surfaces['default']]) }}>
    @if ($contained)
        <div class="container-page">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</{{ $as }}>
