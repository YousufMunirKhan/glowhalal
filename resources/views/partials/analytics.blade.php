{{--
  The Google tag (gtag.js) with Consent Mode v2.

  Loaded whenever a GA4 ID or Google Ads account is configured — for EVERY
  visitor, consented or not. What consent gates is the STATE the tag runs in,
  not whether it loads:

    • no choice / declined → all storage signals 'denied'. The tag sets no
      cookies and stores no identifiers; it sends only anonymous, cookieless
      pings, from which Google models the conversions it cannot observe.
      ads_data_redaction additionally strips ad-click identifiers from those
      pings.
    • accepted → all signals 'granted' (the banner also flips a live page via
      gtag('consent','update') before reloading).

  The consent default MUST be queued before the config calls — Google reads it
  first or not at all. Both accounts (GA4 + Ads) share this single gtag.js
  instance; partials/tracking.blade.php pushes the ecommerce events into it.
--}}
@php
    $gaId = config('services.google.analytics_id');

    // Resolved defensively so a fresh, un-migrated install still boots.
    $seoGoogle = $seo ?? rescue(fn () => app(\App\Settings\SeoSettings::class), null, false);
    $adsAccountId = rescue(fn () => $seoGoogle?->googleAdsAccountId(), null, false);

    $consented = request()->cookie('cookie_consent') === 'accepted';
@endphp

@if ($gaId || $adsAccountId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId ?: $adsAccountId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }

        gtag('consent', 'default', {
            ad_storage: @json($consented ? 'granted' : 'denied'),
            ad_user_data: @json($consented ? 'granted' : 'denied'),
            ad_personalization: @json($consented ? 'granted' : 'denied'),
            analytics_storage: @json($consented ? 'granted' : 'denied'),
            security_storage: 'granted'
        });
        @unless ($consented)
        gtag('set', 'ads_data_redaction', true);
        @endunless

        gtag('js', new Date());
        @if ($gaId)
        gtag('config', @json($gaId), { anonymize_ip: true });
        @endif
        @if ($adsAccountId)
        gtag('config', @json($adsAccountId));
        @endif
    </script>
@endif
