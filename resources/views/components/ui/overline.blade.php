@props([
    'tone' => 'gold', // gold | on-inverse | muted
    'as' => 'p',
])

{{--
  Design system §2.3 — the eyebrow label. This is the ONE small-text gold use
  in the whole system, and it uses --text-gold (#6A5320), never the brand
  gold #C9A961.

  Measured:
    #6A5320 on #FFFFFF  7.31:1 PASS AAA
    #6A5320 on #FAF9F7  7.00:1 PASS AAA
    #6A5320 on #F0EFEC  6.36:1 PASS AA   <- the warm band; #8A6D28 would FAIL here
    #6A5320 on #FBF6EA  6.78:1 PASS AA
  On the ink band the gold flips to gold-surface-light:
    #E4C87F on #1A1A1A 10.66:1 PASS AAA
--}}

@php
    $tones = [
        'gold' => 'text-text-gold',
        // `gold-on-dark` (#C9A961), not `gold-surface-light`: the matrix licenses
        // #E4C87F on ink for DISPLAY type, and an overline is 14px. #C9A961 is
        // the token whose whole purpose is being ink on a dark surface, and it
        // measures 8.80:1 on #0F0F0F — PASS AAA at any size. It also keeps this
        // line clear of the §1.6 G1 grep, which flags every `text-gold-surface*`.
        'on-inverse' => 'text-gold-on-dark',
        'muted' => 'text-text-muted',
    ];
@endphp

<{{ $as }} {{ $attributes->class(['text-overline uppercase', $tones[$tone] ?? $tones['gold']]) }}>{{ $slot }}</{{ $as }}>
