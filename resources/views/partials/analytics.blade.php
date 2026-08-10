{{--
  Google Analytics 4 — loaded ONLY when (a) a Measurement ID is configured and
  (b) the visitor has accepted analytics cookies. No consent → no tracking
  script, no analytics cookies. IP anonymisation on.
--}}
@php
    $gaId = config('services.google.analytics_id');
    $consented = request()->cookie('cookie_consent') === 'accepted';
@endphp

@if ($gaId && $consented)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){ dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', @json($gaId), { anonymize_ip: true });
    </script>
@endif
