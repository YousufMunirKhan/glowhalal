{{--
  Cookie consent banner. Shown until the visitor chooses. "Accept" grants the
  Google tag its storage (Consent Mode v2 'update') and loads the Meta Pixel;
  "Decline" leaves the Google tag in its cookieless denied state and keeps only
  the essential cookies needed to run the store (cart, sign-in). The choice is
  stored in a first-party cookie for a year.
--}}
@if (! request()->cookie('cookie_consent'))
    <div id="cookie-consent"
        class="fixed inset-x-0 bottom-0 z-[var(--z-toast)] border-t border-border-subtle bg-surface p-4 shadow-md
            sm:inset-x-4 sm:bottom-4 sm:mx-auto sm:max-w-xl sm:rounded-lg sm:border"
        role="dialog" aria-live="polite" aria-label="Cookie consent">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-meta text-text-secondary">
                We use essential cookies to run the store, and — with your consent — analytics cookies to improve it.
                Read our <a href="/privacy"
                    class="text-text-primary underline decoration-1 underline-offset-[3px] hover:decoration-2">Privacy Policy</a>.
            </p>
            <div class="flex shrink-0 gap-2">
                <button type="button" onclick="glowHalalConsent('declined')"
                    class="min-h-10 rounded-sm border border-border-strong px-4 text-meta font-semibold text-text-primary
                        hover:bg-surface-sunken transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    Decline
                </button>
                <button type="button" onclick="glowHalalConsent('accepted')"
                    class="min-h-10 rounded-sm bg-gold-surface px-4 text-meta font-semibold text-ink-900
                        hover:bg-gold-surface-hover transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    Accept
                </button>
            </div>
        </div>
    </div>

    <script>
        function glowHalalConsent(choice) {
            document.cookie = 'cookie_consent=' + choice + ';path=/;max-age=' + (60 * 60 * 24 * 365) + ';SameSite=Lax';
            var el = document.getElementById('cookie-consent');
            if (el) { el.remove(); }
            if (choice === 'accepted') {
                // Consent Mode v2: flip the already-running Google tag to
                // 'granted' FIRST, so the pings queued during the denied state
                // are upgraded on the spot instead of lost, then reload so the
                // consent-gated pieces (Meta Pixel) load too.
                if (typeof window.gtag === 'function') {
                    window.gtag('consent', 'update', {
                        ad_storage: 'granted',
                        ad_user_data: 'granted',
                        ad_personalization: 'granted',
                        analytics_storage: 'granted'
                    });
                }
                window.location.reload();
            }
        }
    </script>
@endif
