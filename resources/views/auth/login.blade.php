<x-layouts.app
    title="Sign in to your account | Glow Halal"
    description="Sign in to Glow Halal with Google to track orders and check out faster — or continue as a guest with Cash on Delivery across Pakistan."
    :bottom-nav="false">
    <x-slot:head>
        <meta name="robots" content="noindex,nofollow">
    </x-slot:head>


    <x-ui.section>
        <div class="mx-auto max-w-sm text-center">
            <x-ui.overline class="!text-center">Account</x-ui.overline>
            <h1 class="mt-3 font-display text-display-sm tracking-display text-text-primary">Sign in</h1>
            <p class="mt-3 text-body text-text-secondary">
                Sign in for faster checkout and to see your orders. You never need an account to shop —
                Cash on Delivery works as a guest too.
            </p>

            @if (session('auth_error'))
                <p role="alert"
                    class="mx-auto mt-6 rounded-sm border border-danger-600 bg-danger-50 px-4 py-3 text-body text-danger-700">
                    {{ session('auth_error') }}
                </p>
            @endif

            <div class="mt-8">
                {{-- GET redirect to Google. Optional and user-initiated — never forced. --}}
                <a href="{{ route('auth.google') }}"
                    class="inline-flex min-h-12 w-full items-center justify-center gap-3 rounded-sm border border-border-strong
                        bg-surface px-6 text-body font-semibold text-text-primary
                        transition-[background-color] duration-[var(--motion-fast)] ease-standard hover:bg-surface-sunken
                        focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-info-600">
                    <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true" class="shrink-0">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    Continue with Google
                </a>
            </div>

            <p class="mt-6 text-meta text-text-muted">
                By continuing you agree to our
                <a href="/terms" class="underline decoration-1 underline-offset-[3px] hover:decoration-2">Terms</a>
                and
                <a href="/privacy" class="underline decoration-1 underline-offset-[3px] hover:decoration-2">Privacy Policy</a>.
                We will not email you marketing unless you opt in.
            </p>
        </div>
    </x-ui.section>

</x-layouts.app>
