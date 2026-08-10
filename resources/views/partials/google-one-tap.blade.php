{{--
  Google One Tap — the auto sign-in prompt (Google Identity Services).

  Shown ONLY to guests. If the visitor is already logged into Google, Google
  shows its own one-tap prompt; tapping it posts a signed ID token to
  /auth/google/one-tap, which we verify server-side and log them in. It is a
  dismissible Google-provided prompt, never a wall, and it does not subscribe
  anyone to marketing.
--}}
@guest
    @if (config('services.google.client_id'))
        <script src="https://accounts.google.com/gsi/client" async></script>

        {{-- data-use_fedcm_for_prompt: required by current Chrome, which serves
             One Tap through the browser's FedCM API. --}}
        <div id="g_id_onload"
            data-client_id="{{ config('services.google.client_id') }}"
            data-callback="glowHalalOneTap"
            data-context="signin"
            data-auto_prompt="true"
            data-use_fedcm_for_prompt="true"
            data-cancel_on_tap_outside="false"></div>

        <script>
            function glowHalalOneTap(response) {
                fetch(@json(route('auth.google.onetap')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ credential: response.credential }),
                })
                .then(function (r) { return r.json(); })
                .then(function (d) { if (d && d.ok) { window.location.reload(); } })
                .catch(function () { /* silently ignore — the button flow still works */ });
            }
        </script>
    @endif
@endguest
