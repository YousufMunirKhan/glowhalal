@props([
    'width' => 168,
    'height' => 84,
    'tone' => 'auto',   // auto | light | dark  — controls the HALAL lockup colour
])

{{--
  Glow Halal wordmark.

  Rebuilt to match the supplied brand artwork: a high-contrast serif "GLOW" in
  metallic gold with a sparkle burst over the O, and "HALAL" beneath in a clean
  letter-spaced sans.

  Still SVG <text> rather than outlined paths, because no vector source file
  has been supplied. Save the original .svg/.ai to public/images/ and this
  becomes a one-component swap — nothing else in the site has to change.

  Colour rules (design system §1.3):
  - The gold gradient is a DECORATIVE FILL, which is the licensed role for
    #C9A961. It is never used for body text on light surfaces.
  - "HALAL" uses currentColor so it inherits ink-900 on light surfaces and
    dark-text on inverse bands. Never hardcode it.
--}}

@php
    $uid = 'wm-' . str()->random(6);
    $halalClass = match ($tone) {
        'light' => 'fill-black',
        'dark'  => 'fill-white',
        default => 'fill-current',
    };
@endphp

<svg {{ $attributes->merge(['class' => 'block']) }}
    width="{{ $width }}" height="{{ $height }}"
    viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg"
    role="img" aria-labelledby="{{ $uid }}-title">

    <title id="{{ $uid }}-title">Glow Halal</title>

    <defs>
        {{-- Metallic gold: highlight through the middle, deeper at both ends. --}}
        <linearGradient id="{{ $uid }}-gold" x1="0" y1="0" x2="1" y2="0.35">
            <stop offset="0%"   stop-color="#B8974E" />
            <stop offset="22%"  stop-color="#D9BC72" />
            <stop offset="45%"  stop-color="#EFD89A" />
            <stop offset="62%"  stop-color="#D2B266" />
            <stop offset="82%"  stop-color="#E0C67F" />
            <stop offset="100%" stop-color="#A8863F" />
        </linearGradient>
    </defs>

    {{-- GLOW --}}
    <text x="100" y="55"
        text-anchor="middle"
        font-family="'Playfair Display', Georgia, 'Times New Roman', serif"
        font-size="54" font-weight="600" letter-spacing="1.5"
        fill="url(#{{ $uid }}-gold)">GLOW</text>

    {{-- Sparkle burst above and inside the O. Decorative only — aria-hidden so
         a screen reader hears "Glow Halal" and nothing else. --}}
    <g stroke="url(#{{ $uid }}-gold)" stroke-width="2.1" stroke-linecap="round" aria-hidden="true">
        {{-- upper burst, angled away from the O --}}
        <line x1="126" y1="12" x2="123.5" y2="21" />
        <line x1="134" y1="9"  x2="131"   y2="19" />
        <line x1="141" y1="13" x2="136.5" y2="21" />
        <line x1="146" y1="19" x2="140"   y2="24" />
        {{-- inner burst, sitting in the counter of the O --}}
        <line x1="113" y1="31" x2="119"   y2="36" />
        <line x1="112" y1="40" x2="119.5" y2="39" />
        <line x1="115" y1="47" x2="120"   y2="42" />
        <line x1="123" y1="30" x2="123"   y2="37" />
    </g>

    {{-- HALAL --}}
    <text x="100" y="88"
        text-anchor="middle"
        font-family="Inter, 'Helvetica Neue', Arial, sans-serif"
        font-size="23" font-weight="400" letter-spacing="7"
        class="{{ $halalClass }}">HALAL</text>
</svg>
