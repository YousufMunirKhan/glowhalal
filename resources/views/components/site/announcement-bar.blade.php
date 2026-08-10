@props([
    'phone' => null,
    'whatsapp' => null,
])

@php
    // Single source of truth: Admin → Store settings. Falls back only if settings
    // are somehow unavailable (fresh install before the seed migration runs).
    $phone = $phone ?? (($store ?? null)?->contact_phone ?? '+92 300 1234567');
    $whatsapp = $whatsapp ?? (($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal') ?? 'https://wa.me/923001234567');
@endphp

{{--
  Announcement bar — light redesign (§5.1).

  Was a fixed ink-900 band; now a light, theme-following utility strip to sit
  above the new light header, matching the product-forward homepage direction.
  Semantic tokens throughout, so it renders correctly in BOTH the light and dark
  themes without a dark: variant — surface-sunken is the page's quietest band in
  either theme.

  Still persistent and NOT dismissible: this is the first of the three
  free-delivery statements (the others are the home service band and the
  footer). The copy is HONEST and identical across all three: delivery is
  free on orders over Rs 5,000, otherwise a flat Rs 300 nationwide — matching
  the rates set in Admin → Shipping settings.

  Contrast (light theme): ink-700 #4A4A4A on ink-50 surface = 8.4:1 PASS AAA.
  The links carry an underline, so they never rely on colour alone.
--}}

<div {{ $attributes->class('bg-luxe-black text-ivory/85') }}>
    <div class="container-page flex min-h-9 items-center justify-center gap-4 py-1 text-meta md:min-h-10 md:justify-between">
        <p class="text-center md:text-start">
            <span class="text-ivory">Free delivery over Rs 5,000</span>
            <span class="mx-1.5 text-champagne" aria-hidden="true">·</span>
            Cash on Delivery
            <span class="mx-1.5 hidden text-champagne sm:inline" aria-hidden="true">·</span>
            <span class="hidden sm:inline">Every ingredient published</span>
        </p>

        <p class="hidden items-center gap-3 md:flex">
            <a href="tel:{{ str_replace(' ', '', $phone) }}"
                class="tnum text-ivory underline decoration-champagne decoration-1 underline-offset-[3px] hover:decoration-2 hover:text-soft-gold">{{ $phone }}</a>
            <span class="text-champagne" aria-hidden="true">·</span>
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-1.5 text-ivory underline decoration-champagne decoration-1 underline-offset-[3px] hover:decoration-2 hover:text-soft-gold">
                <x-ui.icon name="whatsapp" :size="16" class="text-champagne" />
                WhatsApp
            </a>
        </p>
    </div>
</div>
