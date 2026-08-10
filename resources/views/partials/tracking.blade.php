{{--
  Conversion tracking layer — GA4 ecommerce events, the Meta (Facebook) Pixel,
  and (optionally) a Google Ads purchase conversion.

  Gating (mirrors partials/analytics.blade.php): this file emits NOTHING unless
  the visitor has accepted cookies AND at least one tracking ID is configured.
  No consent → no pixel, no events, no cookies. Events carry product and price
  data only — never a name, phone, email or address (no PII).

  IDs are owner-managed in Admin → SEO & Integrations:
    • Google Analytics ID   → config('services.google.analytics_id')  (GA4, gtag.js
                              is loaded by partials/analytics.blade.php)
    • Meta Pixel ID         → SeoSettings::meta_pixel_id   (paste the ID, done)
    • Google Ads conversion → SeoSettings::google_ads_conversion  (AW-XXXX/label)

  The events themselves (add_to_cart, view_item, begin_checkout, purchase and the
  Meta equivalents) fire from data-* attributes rendered on the relevant pages
  and from the Livewire 'gh-add-to-cart' browser event. Each platform is guarded
  independently, so a store with only a Pixel (or only GA4) still works.
--}}
@php
    // gtag's GA4 measurement ID — already reflected from SeoSettings into config
    // by AppServiceProvider::applyIntegrationSettings().
    $gaId = config('services.google.analytics_id');

    // Meta Pixel + Google Ads come straight from SeoSettings. Resolved
    // defensively so a fresh, un-migrated install still boots.
    $seoTracking = $seo ?? rescue(fn () => app(\App\Settings\SeoSettings::class), null, false);
    $pixelId = $seoTracking?->meta_pixel_id;
    $adsConversion = $seoTracking?->google_ads_conversion;
    $adsAccountId = $adsConversion ? \Illuminate\Support\Str::before($adsConversion, '/') : null;

    $consented = request()->cookie('cookie_consent') === 'accepted';
@endphp

@if ($consented && ($gaId || $pixelId || $adsConversion))
    @if ($pixelId)
        {{-- Meta Pixel base code. The owner only pastes their Pixel ID in admin;
             everything below turns on the moment it is set. --}}
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($pixelId));
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1" alt=""/></noscript>
    @endif

    @if ($adsConversion)
        {{-- Google Ads conversion account. gtag.js is already loaded by the
             analytics partial when a GA4 ID is set; load it here only if it is
             not, then register the Ads conversion account so the conversion can
             fire on the purchase page. --}}
        @unless ($gaId)
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsAccountId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){ dataLayer.push(arguments); }
                gtag('js', new Date());
            </script>
        @endunless
        <script>
            window.dataLayer = window.dataLayer || [];
            window.gtag = window.gtag || function(){ dataLayer.push(arguments); };
            gtag('config', @json($adsAccountId));
        </script>
    @endif

    {{-- Ecommerce event layer. GA4 events fire only when a GA4 ID is set, Meta
         events only when the Pixel is set — guarded independently. --}}
    <script>
        (function () {
            var HAS_GA = @json((bool) $gaId);
            var HAS_PIXEL = @json((bool) $pixelId);
            var ADS_SEND_TO = @json($adsConversion);
            var CURRENCY = 'PKR';

            function ga() { if (HAS_GA && typeof window.gtag === 'function') window.gtag.apply(null, arguments); }
            function fb() { if (HAS_PIXEL && typeof window.fbq === 'function') window.fbq.apply(null, arguments); }
            function num(v) { v = Number(v); return isFinite(v) ? v : 0; }
            function items(el) { try { return JSON.parse(el.getAttribute('data-items') || '[]'); } catch (e) { return []; } }

            // ── add_to_cart / AddToCart ───────────────────────────────────────
            // Dispatched by the Livewire buy box and quick-add with the variant
            // SKU as `id` — the same id used in the product feeds, so Meta
            // dynamic/carousel ads match the catalogue.
            window.addEventListener('gh-add-to-cart', function (e) {
                var d = (e && e.detail) || {};
                var id = d.id, name = d.name, price = num(d.price), qty = num(d.quantity) || 1;
                if (!id) return;
                ga('event', 'add_to_cart', {
                    currency: CURRENCY, value: price * qty,
                    items: [{ item_id: id, item_name: name, price: price, quantity: qty }]
                });
                fb('track', 'AddToCart', {
                    value: price * qty, currency: CURRENCY,
                    content_ids: [id], content_type: 'product',
                    contents: [{ id: id, quantity: qty, item_price: price }]
                });
            });

            // ── Page-load events, read from data-* attributes ─────────────────
            // The :not([data-gh-fired]) guard + the flag stamped on fire means a
            // page reached by a full load OR by Livewire SPA navigation fires
            // exactly once, never twice.
            function firePageEvents() {
                // view_item / ViewContent (product page)
                var vc = document.querySelector('[data-gh-view-content]:not([data-gh-fired])');
                if (vc) {
                    vc.setAttribute('data-gh-fired', '1');
                    var vcId = vc.getAttribute('data-id');
                    var vcName = vc.getAttribute('data-name');
                    var vcPrice = num(vc.getAttribute('data-price'));
                    if (vcId) {
                        ga('event', 'view_item', {
                            currency: CURRENCY, value: vcPrice,
                            items: [{ item_id: vcId, item_name: vcName, price: vcPrice }]
                        });
                        fb('track', 'ViewContent', {
                            value: vcPrice, currency: CURRENCY,
                            content_ids: [vcId], content_type: 'product',
                            contents: [{ id: vcId, quantity: 1, item_price: vcPrice }]
                        });
                    }
                }

                // begin_checkout / InitiateCheckout (checkout page)
                var bc = document.querySelector('[data-gh-begin-checkout]:not([data-gh-fired])');
                if (bc) {
                    bc.setAttribute('data-gh-fired', '1');
                    var bcItems = items(bc);
                    var bcValue = num(bc.getAttribute('data-value'));
                    ga('event', 'begin_checkout', {
                        currency: CURRENCY, value: bcValue,
                        items: bcItems.map(function (i) {
                            return { item_id: i.id, item_name: i.name, price: num(i.price), quantity: num(i.quantity) };
                        })
                    });
                    fb('track', 'InitiateCheckout', {
                        value: bcValue, currency: CURRENCY,
                        num_items: bcItems.reduce(function (n, i) { return n + num(i.quantity); }, 0),
                        content_ids: bcItems.map(function (i) { return i.id; }),
                        content_type: 'product',
                        contents: bcItems.map(function (i) { return { id: i.id, quantity: num(i.quantity), item_price: num(i.price) }; })
                    });
                }

                // purchase / Purchase (+ optional Google Ads conversion)
                var pu = document.querySelector('[data-gh-purchase]:not([data-gh-fired])');
                if (pu) {
                    pu.setAttribute('data-gh-fired', '1');
                    var txn = pu.getAttribute('data-transaction-id');
                    var value = num(pu.getAttribute('data-value'));
                    var puItems = items(pu);
                    ga('event', 'purchase', {
                        transaction_id: txn, value: value, currency: CURRENCY,
                        items: puItems.map(function (i) {
                            return { item_id: i.id, item_name: i.name, price: num(i.price), quantity: num(i.quantity) };
                        })
                    });
                    fb('track', 'Purchase', {
                        value: value, currency: CURRENCY,
                        content_ids: puItems.map(function (i) { return i.id; }),
                        content_type: 'product',
                        contents: puItems.map(function (i) { return { id: i.id, quantity: num(i.quantity), item_price: num(i.price) }; })
                    });
                    if (ADS_SEND_TO && typeof window.gtag === 'function') {
                        window.gtag('event', 'conversion', {
                            send_to: ADS_SEND_TO, value: value, currency: CURRENCY, transaction_id: txn
                        });
                    }
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', firePageEvents);
            } else {
                firePageEvents();
            }
            // Livewire SPA navigation (wire:navigate) — re-run for the new page.
            document.addEventListener('livewire:navigated', firePageEvents);
        })();
    </script>
@endif
