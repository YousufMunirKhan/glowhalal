@props([
    'title' => 'Glow Halal',
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    // Open Graph object type — pages override (e.g. the PDP passes 'product')
    // so the document never carries two conflicting og:type tags.
    'ogType' => 'website',
    'current' => null,
    'cartCount' => 0,
    'products' => [],
    'company' => [],
    'founder' => [],
    'bottomNav' => true,
    // Page-level robots override. Null lets the site-wide SeoSettings master
    // switch decide. This is the SINGLE place robots is emitted for the whole
    // storefront — home, shop, product and CMS pages all route through here, so
    // the launch-time noindex actually covers every page.
    'robots' => null,
])

@php
    // Content locale is pinned by App\Http\Middleware\SetLocale on /ur-roman/*;
    // English routes inherit the app default ('en'). Roman Urdu ('ur-Latn') is
    // written in LATIN script, so it is left-to-right — dir is 'ltr' for BOTH
    // locales and must never be 'rtl' here.
    $htmlLang = app()->getLocale() === 'ur-Latn' ? 'ur-Latn' : 'en';
    $htmlDir = 'ltr';

    // Social share image. A page may pass its own (a product photo); otherwise
    // every page falls back to the flagship product shot, which is a real
    // raster asset that exists in storage. Relative paths are resolved to an
    // absolute URL because og:image must be absolute to render on any platform.
    $resolvedOgImage = $ogImage
        ? (\Illuminate\Support\Str::startsWith($ogImage, ['http://', 'https://']) ? $ogImage : asset(ltrim($ogImage, '/')))
        : asset('storage/products/lookman-e-hayat-100ml.jpg');

    // WhatsApp link — single source of truth: Admin → Store settings.
    // Settings-only — a fake fallback number must never reach a customer.
    $whatsappHref = ($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal');
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}" dir="{{ $htmlDir }}" class="antialiased">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    {{-- Design system §1.5. FIRST element in <head>, inline, not deferred, not
         bundled. Resolves the stored preference to a concrete light|dark value
         and writes it to <html> before the stylesheet loads, so the CSS never
         needs a prefers-color-scheme branch and there is no flash of wrong
         theme. ~380 bytes. --}}
    <script>
        (function () {
            var stored = null;
            try { stored = localStorage.getItem('glowhalal:theme'); } catch (e) {}
            var pref = stored || 'system';
            var dark = pref === 'dark' || (pref === 'system' &&
                window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme-pref', pref);
        })();
    </script>

    <title>{{ $title }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    {{-- SEO master switch — emitted exactly ONCE for the whole storefront.
         While SeoSettings::is_indexable is false the entire site is
         noindex,nofollow (correct until launch); a page may pass its own
         :robots (e.g. a filtered listing → noindex,follow) which is honoured
         only once the switch is on. Resolved defensively so a fresh install
         with no settings row still boots and stays safely noindex. --}}
    @php
        $siteIndexable = rescue(fn () => app(\App\Settings\SeoSettings::class)->is_indexable, false, false);
        $effectiveRobots = $siteIndexable ? ($robots ?? 'index,follow') : 'noindex,nofollow';
    @endphp
    <meta name="robots" content="{{ $effectiveRobots }}">

    {{-- Light to match the redesigned chrome (light announcement bar + header).
         The dark theme overrides this at runtime via the head script's data-theme. --}}
    <meta name="theme-color" content="#faf9f7">

    {{-- CSRF token for fetch()-based posts (Google One Tap). --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Site ownership proofs, managed in Admin → SEO & Integrations.
         Google Search Console + Bing Webmaster Tools (Bing also verifies Yahoo)
         + Meta Business Suite domain verification.
         Resolved defensively so a fresh, un-migrated install still boots. --}}
    @php $seoVerify = rescue(fn () => app(\App\Settings\SeoSettings::class), null, false); @endphp
    @if ($seoVerify?->google_site_verification)
        <meta name="google-site-verification" content="{{ $seoVerify->google_site_verification }}">
    @endif
    @if ($seoVerify?->bing_site_verification)
        <meta name="msvalidate.01" content="{{ $seoVerify->bing_site_verification }}">
    @endif
    @if ($seoVerify?->facebook_domain_verification)
        <meta name="facebook-domain-verification" content="{{ $seoVerify->facebook_domain_verification }}">
    @endif

    {{-- Exactly two preloads (§2.1). Playfair is deliberately NOT preloaded:
         it is only needed below the first heading and preloading it would
         compete with the LCP hero image for bandwidth on a congested cell. --}}
    <link rel="preload" href="/fonts/inter-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/inter-600.woff2" as="font" type="font/woff2" crossorigin>

    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="Glow Halal">
    <meta property="og:title" content="{{ $title }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:image" content="{{ $resolvedOgImage }}">
    <meta property="og:image:alt" content="{{ $title }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $resolvedOgImage }}">

    {{-- SVG first for modern browsers (crisp at every tab size, matches the
         gold wordmark); .ico kept as the legacy fallback. --}}
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="mask-icon" href="/favicon.svg" color="#c9a961">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- The Google tag (GA4 + Google Ads) with Consent Mode v2 — always loads,
         runs cookieless-denied until the visitor accepts. --}}
    @include('partials.analytics')

    {{-- Conversion tracking — GA4 ecommerce events, Meta Pixel, Google Ads
         conversion. Google events honour Consent Mode; the Meta Pixel stays
         hard-gated on consent. --}}
    @include('partials.tracking')

    {{ $head ?? '' }}
</head>

{{-- The bottom padding clears the fixed bottom nav. It lives on <body>, not on
     <main>, because the footer is a sibling of <main> and would otherwise have
     its last line covered by the bar. --}}
<body @class([
    'min-h-dvh bg-surface text-text-primary',
    'pb-[calc(var(--h-bottom-nav)+var(--safe-bottom))] lg:pb-0' => $bottomNav,
])>

    <a href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[var(--z-skiplink)]
            focus:rounded-sm focus:bg-surface focus:px-4 focus:py-3 focus:text-body focus:font-semibold
            focus:text-text-primary focus:shadow-md">
        Skip to content
    </a>

    <x-site.announcement-bar />

    <x-site.header :current="$current" :cart-count="$cartCount" />

    <main id="main">
        {{ $slot }}
    </main>

    <x-site.footer :company="$company" :products="$products" :founder="$founder" />

    @if ($bottomNav)
        <x-site.bottom-nav :current="$current" :cart-count="$cartCount" />
    @endif

    {{-- WhatsApp FAB — reachable on mobile on EVERY page (research: the top PK
         COD conversion lever), not just desktop. It stays clear of the mobile
         chrome that lives at the bottom: on pages with the bottom nav it floats
         above it; on the PDP / cart / checkout it floats above the 72px sticky
         buy bar. On desktop (no bottom nav) it returns to the corner. --}}
    @if (filled($whatsappHref))
        <a href="{{ $whatsappHref }}"
            target="_blank" rel="noopener"
            @class([
                'fixed end-6 z-[var(--z-fab)] grid h-14 w-14 place-items-center rounded-full lg:bottom-6',
                'bg-whatsapp text-white shadow-md hover:bg-whatsapp-hover',
                'transition-[background-color] duration-[var(--motion-fast)] ease-standard',
                'bottom-[calc(var(--h-bottom-nav)+var(--safe-bottom)+1rem)]' => $bottomNav,
                'bottom-[calc(var(--h-buy-bar)+var(--safe-bottom)+1rem)]' => ! $bottomNav,
            ])
            aria-label="Message us on WhatsApp (opens in a new tab)">
            <x-ui.icon name="whatsapp" :size="28" />
        </a>
    @endif

    <x-site.mobile-menu :products="$products" />

    {{-- Slide-in cart drawer. One instance for the whole site: it is always
         present, opens on 'added-to-bag' / 'open-cart-drawer', and refreshes on
         'cart-updated'. --}}
    <livewire:cart.drawer />

    {{-- Toast region. Empty at rest; announcements are injected here so a
         screen-reader user hears them without focus moving. --}}
    <div id="toast-region" role="status" aria-live="polite"
        class="pointer-events-none fixed inset-x-4 bottom-20 z-[var(--z-toast)] flex flex-col items-center gap-2">
    </div>

    {{-- Google One Tap auto sign-in prompt. The partial is itself @guest-gated
         (never shown to logged-in users). Shown across the storefront so it
         greets visitors, EXCEPT during the cart/checkout funnel where a prompt
         would interrupt a purchase. NOTE: for the prompt to actually appear,
         https://glowhalal.com must be listed under the OAuth client's
         "Authorised JavaScript origins" in Google Cloud Console. --}}
    @unless (request()->routeIs('checkout.*', 'cart.*', 'orders.*'))
        @include('partials.google-one-tap')
    @endunless
    @include('partials.cookie-consent')
</body>

</html>
