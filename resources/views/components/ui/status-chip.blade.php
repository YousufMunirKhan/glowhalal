@props([
    'tone' => 'info', // success | info | warning | neutral
    'icon' => null,
])

{{--
  Design system §3.5 C/D and §3.6 — the status / verdict chip chassis.

  Always icon + text, never colour alone: these carry rulings and process
  states, not decoration.

  Every colour here is a FIXED palette value — the light tints are ink-weight
  on paper and illegible on #121212 (success-700 measures 2.4:1 there) — so
  each tone carries an explicit `on-dark:` pair. Measured:

    light band                        dark band (on #121212 / #1C1C1C)
      success  #14603A on #EAF5EF  6.80:1   #7CD9A4 on white/8   ~10.4:1
      info     #1D5FA8 on #EAF1F9  5.66:1   #7FB2E8 on white/8    ~8.0:1
      warning  #8F5A00 on #FDF3E3  5.26:1   #E4C87F on white/8   ~10.9:1
      neutral  #2E2E2E on #F0EFEC 11.81:1   #F2F0EC on white/8   ~14.2:1
    All PASS AA; the dark pairs PASS AAA. The 8% white fill sits between
    #121212 and #1C1C1C in luminance, so the ratios above hold on both.
--}}

@php
    $tones = [
        'success' => 'bg-success-50 text-success-700 on-dark:bg-white/8 on-dark:text-success-on-dark',
        'info' => 'bg-info-50 text-info-600 on-dark:bg-white/8 on-dark:text-info-on-dark',
        'warning' => 'bg-warning-50 text-warning-600 on-dark:bg-white/8 on-dark:text-gold-surface-light',
        'neutral' => 'bg-ink-100 text-ink-800 on-dark:bg-white/8 on-dark:text-dark-text',
    ];
    $defaultIcons = [
        'success' => 'check',
        'info' => 'clock',
        'warning' => 'clock',
        'neutral' => 'check',
    ];
    $glyph = $icon ?? $defaultIcons[$tone] ?? 'check';
@endphp

<span {{ $attributes->class(
    'inline-flex items-center gap-1.5 rounded-xs px-2 h-7 text-meta font-semibold ' .
    ($tones[$tone] ?? $tones['info'])
) }}>
    <x-ui.icon :name="$glyph" :size="16" stroke-width="2.25" />
    <span>{{ $slot }}</span>
</span>
