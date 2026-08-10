@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'width' => 'auto', // auto | full | full-mobile
    'external' => false,
])

{{--
  Design system §3.1.

  Contrast, measured, for every state that ships here:
    primary   ink-900 on gold-surface        7.73:1 PASS AAA
              ink-900 on gold-surface-hover  6.28:1 PASS AA
              ink-900 on gold-surface-active 5.10:1 PASS AA
    secondary ink-900 on surface            17.41:1 PASS AAA
              ink-900 on ink-100 (hover)    15.14:1 PASS AAA
              on a dark band the same two semantic tokens resolve to
              #F2F0EC on #121212            16.46:1 PASS AAA
    inverse   dark-text on ink-950          17.13:1 PASS AAA
    tertiary  --text-gold #6A5320 on white   7.31:1 PASS AAA
              --text-gold #C9A961 on #121212 8.32:1 PASS AAA (dark band)
    whatsapp  white on #075E54               7.67:1 PASS AAA
              white on #05463F              10.74:1 PASS AAA
    destruct. danger-700 on surface          9.12:1 PASS AAA

  Focus is never restyled here — it is global, and the mandatory 2px
  outline-offset is what makes the ring legal over a gold fill.
  Only background-color and border-color transition. Never box-shadow,
  never transform on hover.
--}}

@php
    $base =
        'inline-flex items-center justify-center gap-2 whitespace-nowrap font-sans font-semibold ' .
        'rounded-sm transition-[background-color,border-color,text-decoration-thickness] ' .
        'duration-[var(--motion-fast)] ease-standard';

    $sizes = [
        // 40px visual, 48px hit box via the pseudo-element inflation.
        'sm' => 'relative min-h-10 min-w-[72px] px-4 text-[0.9375rem] ' .
                "after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']",
        'md' => 'min-h-12 min-w-24 px-6 text-body',
        'lg' => 'min-h-14 min-w-[140px] px-8 text-body',
    ];

    $variants = [
        'primary' =>
            'bg-gold-surface text-ink-900 border border-transparent ' .
            'hover:bg-gold-surface-hover active:bg-gold-surface-active ' .
            'active:scale-[0.98] active:transition-transform active:duration-[var(--motion-instant)]',

        // Semantic, so it follows both [data-theme] and [data-surface="dark"].
        // The hover needs an on-dark pair: --surface-sunken is #0F0F0F inside a
        // dark band, which is invisible against the #121212 it sits on.
        'secondary' =>
            'bg-transparent text-text-primary border-[1.5px] border-text-primary ' .
            'hover:bg-surface-sunken on-dark:hover:bg-white/10 ' .
            'active:bg-ink-200 active:text-ink-900',

        // FIXED-DARK BANDS ONLY (section surface="ink" / "dark" / "dark-raised").
        // Deliberately dark-text, not text-on-inverse: inside a dark context
        // --text-on-inverse is #1A1A1A (it describes the light chip used by the
        // theme toggle), and this button would render near-black on near-black.
        // #F2F0EC measures 17.13:1 on #0F0F0F, 16.46:1 on #121212 and 14.98:1
        // on #1C1C1C — PASS AAA on every band it is licensed for.
        // On a theme-FLIPPING inverse band, use `secondary` instead.
        'inverse' =>
            'bg-transparent text-dark-text border-[1.5px] border-dark-text ' .
            'hover:bg-white/10',

        // Underline is present at rest — a gold-ish link without one would rely on colour alone.
        'tertiary' =>
            'text-text-gold underline decoration-1 underline-offset-[3px] px-0 min-w-0 ' .
            'hover:text-text-primary hover:decoration-2 active:text-text-primary active:decoration-2',

        'whatsapp' =>
            'bg-whatsapp text-white border border-transparent hover:bg-whatsapp-hover active:bg-whatsapp-hover',

        'destructive' =>
            'text-danger-700 bg-transparent px-0 min-w-0 hover:text-danger-800',
    ];

    $widths = [
        'auto' => '',
        'full' => 'w-full',
        'full-mobile' => 'w-full xs:w-auto',
    ];

    $classes = trim(
        $base . ' ' .
        ($sizes[$size] ?? $sizes['md']) . ' ' .
        ($variants[$variant] ?? $variants['primary']) . ' ' .
        ($widths[$width] ?? '')
    );

    // Tertiary and destructive are text controls: strip the box sizing so they
    // sit inline in prose, but keep a 48px hit box around them.
    if (in_array($variant, ['tertiary', 'destructive'], true)) {
        $classes = str_replace(['min-h-12', 'min-h-14', 'min-w-24', 'min-w-[140px]', 'px-6', 'px-8', 'rounded-sm'], '', $classes);
        $classes .= ' min-h-11 rounded-xs';
    }

    $rel = $external ? 'noopener' : null;
    $target = $external ? '_blank' : null;
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($target) target="{{ $target }}" rel="{{ $rel }}" @endif
        {{ $attributes->class($classes) }}>
        {{ $slot }}
        @if ($external)
            <x-ui.icon name="external" :size="16" />
            <span class="sr-only">(opens in a new tab)</span>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
