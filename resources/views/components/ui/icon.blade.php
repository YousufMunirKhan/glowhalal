@props(['name', 'size' => 24])

{{--
  Inline SVG icon set. No icon font, no sprite fetch, no JS — every glyph is
  in the server-rendered HTML. Icons are decorative by default (aria-hidden);
  meaning is always carried by adjacent text, per design-system §3.5.
--}}

@php
    $attrs = $attributes->merge([
        'width' => $size,
        'height' => $size,
        'viewBox' => '0 0 24 24',
        'fill' => 'none',
        'stroke' => 'currentColor',
        'stroke-width' => '1.75',
        'stroke-linecap' => 'round',
        'stroke-linejoin' => 'round',
        'aria-hidden' => 'true',
        'focusable' => 'false',
        'class' => 'shrink-0',
    ]);
@endphp

<svg {{ $attrs }}>
    @switch($name)
        @case('menu')
            <path d="M3 6h18M3 12h18M3 18h18" />
            @break

        @case('search')
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            @break

        @case('cart')
            <path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6" />
            <circle cx="10" cy="20" r="1.4" /><circle cx="18" cy="20" r="1.4" />
            @break

        @case('close')
            <path d="M6 6l12 12M18 6L6 18" />
            @break

        @case('chevron-right')
            <path d="m9 5 7 7-7 7" />
            @break

        @case('chevron-down')
            <path d="m5 9 7 7 7-7" />
            @break

        @case('check')
            <path d="m4 12.5 5.2 5.2L20 7" />
            @break

        @case('cross')
            <path d="M6 6l12 12M18 6L6 18" />
            @break

        @case('arrow-right')
            <path d="M4 12h15M13 6l6 6-6 6" />
            @break

        @case('external')
            <path d="M14 4h6v6" /><path d="M20 4 10 14" />
            <path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5" />
            @break

        @case('copy')
            <rect x="9" y="9" width="11" height="11" rx="2" />
            <path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5.2l3.3 2" />
            @break

        @case('document')
            <path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7z" />
            <path d="M14 3v4h4M9 13h6M9 17h4" />
            @break

        @case('home')
            <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z" />
            @break

        @case('shop')
            <path d="M4 8h16l-1 12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1z" />
            <path d="M9 8V6a3 3 0 0 1 6 0v2" />
            @break

        @case('whatsapp')
            <path fill="currentColor" stroke="none"
                d="M12.04 2c-5.5 0-9.96 4.46-9.96 9.96 0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 0 0 4.84 1.24h.01c5.5 0 9.96-4.46 9.96-9.96C22.01 6.46 17.54 2 12.04 2m0 18.15h-.01a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.11.82.83-3.03-.2-.31a8.16 8.16 0 0 1-1.26-4.34c0-4.55 3.7-8.25 8.25-8.25 2.2 0 4.28.86 5.83 2.42a8.2 8.2 0 0 1 2.42 5.84c0 4.55-3.7 8.18-8.26 8.18m4.53-6.13c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.78.97-.15.16-.29.18-.53.06-.25-.13-1.05-.39-2-1.23a7.4 7.4 0 0 1-1.38-1.72c-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.09-.16.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.84-.2-.49-.4-.42-.55-.43l-.47-.01c-.16 0-.43.06-.65.31-.22.24-.86.84-.86 2.05s.88 2.38 1 2.54c.12.17 1.73 2.65 4.2 3.71.59.26 1.04.41 1.4.52.59.19 1.13.16 1.55.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.11-.22-.17-.47-.29" />
            @break

        {{-- Social marks. Drawn as filled glyphs (fill="currentColor"
             stroke="none") like 'whatsapp' above, because these are recognised
             by their solid silhouette — a stroked outline reads as a generic
             shape at 18px. --}}
        @case('instagram')
            <path fill="currentColor" stroke="none"
                d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16m0 2.13c-3.15 0-3.52.01-4.76.07-1.15.05-1.77.24-2.19.4-.55.22-.94.47-1.35.88-.41.41-.66.8-.88 1.35-.16.42-.35 1.04-.4 2.19-.06 1.24-.07 1.61-.07 4.76s.01 3.52.07 4.76c.05 1.15.24 1.77.4 2.19.22.55.47.94.88 1.35.41.41.8.66 1.35.88.42.16 1.04.35 2.19.4 1.24.06 1.61.07 4.76.07s3.52-.01 4.76-.07c1.15-.05 1.77-.24 2.19-.4.55-.22.94-.47 1.35-.88.41-.41.66-.8.88-1.35.16-.42.35-1.04.4-2.19.06-1.24.07-1.61.07-4.76s-.01-3.52-.07-4.76c-.05-1.15-.24-1.77-.4-2.19a3.6 3.6 0 0 0-.88-1.35 3.6 3.6 0 0 0-1.35-.88c-.42-.16-1.04-.35-2.19-.4-1.24-.06-1.61-.07-4.76-.07m0 3.62a6.09 6.09 0 1 1 0 12.18 6.09 6.09 0 0 1 0-12.18m0 10.04a3.95 3.95 0 1 0 0-7.9 3.95 3.95 0 0 0 0 7.9m7.75-10.28a1.42 1.42 0 1 1-2.85 0 1.42 1.42 0 0 1 2.85 0" />
            @break

        @case('facebook')
            <path fill="currentColor" stroke="none"
                d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94" />
            @break

        @case('tiktok')
            <path fill="currentColor" stroke="none"
                d="M16.6 5.82A4.28 4.28 0 0 1 15.54 3h-3.09v12.4a2.59 2.59 0 0 1-2.59 2.5 2.59 2.59 0 1 1 .77-5.06v-3.1a5.66 5.66 0 0 0-.77-.05A5.68 5.68 0 1 0 15.54 15.4V9.01a7.35 7.35 0 0 0 4.3 1.38V7.3a4.29 4.29 0 0 1-3.24-1.48" />
            @break

        @case('youtube')
            <path fill="currentColor" stroke="none"
                d="M21.58 7.19a2.51 2.51 0 0 0-1.77-1.77C18.25 5 12 5 12 5s-6.25 0-7.81.42a2.51 2.51 0 0 0-1.77 1.77C2 8.75 2 12 2 12s0 3.25.42 4.81a2.51 2.51 0 0 0 1.77 1.77C5.75 19 12 19 12 19s6.25 0 7.81-.42a2.51 2.51 0 0 0 1.77-1.77C22 15.25 22 12 22 12s0-3.25-.42-4.81M10 15.02V8.98L15.2 12z" />
            @break

        @case('sun')
            <circle cx="12" cy="12" r="4" />
            <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
            @break

        @case('moon')
            <path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5" />
            @break

        @case('monitor')
            <rect x="3" y="4" width="18" height="12" rx="1.5" /><path d="M8 20h8M12 16v4" />
            @break

        @case('truck')
            <path d="M2 7h11v9H2zM13 10h4l3 3v3h-7z" />
            <circle cx="6" cy="18" r="1.6" /><circle cx="17" cy="18" r="1.6" />
            @break

        @case('cash')
            <rect x="2" y="6" width="20" height="12" rx="1.5" /><circle cx="12" cy="12" r="2.6" />
            @break

        @case('box')
            <path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z" /><path d="m3 7.5 9 4.5 9-4.5M12 12v9" />
            @break

        @case('quote')
            <path fill="currentColor" stroke="none"
                d="M9.5 5C6.5 6.7 5 9.4 5 13v6h6v-7H8.2c0-2.2.8-3.8 2.4-4.8zm9 0c-3 1.7-4.5 4.4-4.5 8v6h6v-7h-2.8c0-2.2.8-3.8 2.4-4.8z" />
            @break

        @case('user')
            <circle cx="12" cy="8" r="4" />
            <path d="M4 20.5C4.9 16.9 8.1 14.5 12 14.5s7.1 2.4 8 6" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
