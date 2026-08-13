@props([
    'company' => [],
    'products' => [],
    'founder' => [],
])

{{--
  Footer — luxe-black (#0B0B0A). Four balanced areas on a 12-col grid: brand +
  reachability, a Shop column, a Help column, and an integrated Newsletter
  signup (the only newsletter on the site, so it appears on every page). A
  bottom bar closes with copyright, courier/delivery and legal links.

  data-surface="dark" re-points the semantic layer, so text-text-secondary is a
  readable muted light on black and champagne is legal as ink for the headers.
--}}

@php
    // $errors is injected by ShareErrorsFromSession (the `web` middleware
    // group). Error pages (404/500) and other renders outside that group skip
    // it, leaving $errors undefined — and the @error('email') directive below
    // would then fatal, turning the original error into a second one and
    // hiding it. Default to an empty bag so the footer renders anywhere.
    $errors ??= new \Illuminate\Support\ViewErrorBag();

    // Single source of truth: Admin → Store settings.
    // Email renders ONLY when the owner has set one in Store settings — no
    // hardcoded fallback: an unmonitored inbox shown to customers is worse
    // than none.
    $email = ($store ?? null)?->contact_email ?: ($company['email'] ?? null);
    // Settings-only — never a fake fallback number.
    $whatsappHref = ($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal')
        ?? ($company['whatsapp'] ?? null);
@endphp

<footer data-surface="dark" class="bg-luxe-black text-text-secondary">
    <div class="container-page py-14 md:py-16">

        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8">

            {{-- Brand + reachability --}}
            <div class="lg:col-span-4">
                <a href="/" aria-label="Glow Halal — home" class="inline-block text-ivory">
                    <x-wordmark tone="dark" class="h-12 w-auto" />
                </a>
                <p class="mt-5 max-w-xs text-body text-text-secondary">
                    Karachi-based halal beauty store. The full ingredient list is published on every product, and
                    so is the list of ingredients we never stock — the two claims you can check for yourself.
                </p>
                <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3 text-meta">
                    @if (filled($email))
                        <a href="mailto:{{ $email }}"
                            class="text-ivory underline decoration-champagne decoration-1 underline-offset-[3px] hover:text-soft-gold">{{ $email }}</a>
                    @endif
                    @if (filled($whatsappHref))
                        <a href="{{ $whatsappHref }}"
                            target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-ivory hover:text-soft-gold">
                            <x-ui.icon name="whatsapp" :size="18" class="text-champagne" /> WhatsApp
                        </a>
                    @endif
                </div>
            </div>

            {{-- Shop --}}
            <nav class="lg:col-span-2" aria-label="Shop">
                <p class="text-overline uppercase tracking-caps text-champagne">Shop</p>
                <ul class="mt-4 space-y-3">
                    @foreach ($products as $p)
                        <li>
                            <a href="/products/{{ $p['slug'] }}"
                                class="text-body text-text-secondary no-underline hover:text-ivory">{{ \Illuminate\Support\Str::limit($p['name'], 30) }}</a>
                        </li>
                    @endforeach
                    <li><a href="/shop" class="text-body text-text-secondary no-underline hover:text-ivory">Shop all</a></li>
                    <li><a href="/what-we-never-use" class="text-body text-text-secondary no-underline hover:text-ivory">What we never use</a></li>
                    <li><a href="/halal-ingredients" class="text-body text-text-secondary no-underline hover:text-ivory">Ingredient index</a></li>
                </ul>
            </nav>

            {{-- Help --}}
            <nav class="lg:col-span-2" aria-label="Help">
                <p class="text-overline uppercase tracking-caps text-champagne">Help</p>
                <ul class="mt-4 space-y-3">
                    @foreach ([
                        ['About', '/about'], ['Contact', '/contact'], ['FAQ', '/faq'],
                        ['Shipping & returns', '/shipping-returns'], ['Privacy', '/privacy'],
                        ['Terms', '/terms'], ['Disclaimer', '/disclaimer'],
                    ] as [$label, $href])
                        <li><a href="{{ $href }}" class="text-body text-text-secondary no-underline hover:text-ivory">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            {{-- Newsletter --}}
            <div class="sm:col-span-2 lg:col-span-4">
                <p class="text-overline uppercase tracking-caps text-champagne">Newsletter</p>
                <p class="mt-4 text-body text-text-secondary">
                    Subscribe for new products, restocks and honest formulation notes. No spam — unsubscribe anytime.
                </p>

                @if (session('newsletter_status'))
                    <p role="status"
                        class="mt-4 rounded-sm border border-champagne/40 bg-white/5 px-4 py-3 text-meta text-ivory">
                        {{ session('newsletter_status') }}
                    </p>
                @else
                    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-4">
                        @csrf
                        <div class="flex flex-col gap-2 xs:flex-row">
                            <label for="footer-newsletter-email" class="sr-only">Your email address</label>
                            <input type="email" name="email" id="footer-newsletter-email" required autocomplete="email"
                                value="{{ old('email') }}" placeholder="Your email address"
                                class="min-h-12 flex-1 rounded-sm border border-champagne/40 bg-white/5 px-4 text-body text-ivory
                                    placeholder:text-text-secondary focus-visible:outline-2 focus-visible:outline-offset-2
                                    focus-visible:outline-champagne">
                            <button type="submit"
                                class="min-h-12 shrink-0 rounded-sm bg-champagne px-6 text-meta font-semibold uppercase
                                    tracking-caps text-luxe-black transition-[background-color] duration-[var(--motion-fast)]
                                    ease-standard hover:bg-soft-gold">
                                Subscribe
                            </button>
                        </div>
                        @error('email')
                            <p class="mt-2 text-meta text-[#F1A9A4]">{{ $message }}</p>
                        @enderror
                    </form>
                @endif
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-12 flex flex-col gap-4 border-t border-champagne/20 pt-8 md:flex-row md:items-center md:justify-between">
            <p class="text-meta text-text-secondary">
                &copy; {{ date('Y') }} {{ $company['name'] ?? 'Glow Halal' }}. All rights reserved.
            </p>
            <p class="text-meta text-text-secondary">
                Delivered by TCS · Leopards · M&amp;P
                <span class="mx-1 text-champagne" aria-hidden="true">·</span>
                Free delivery over <span class="tnum">Rs&nbsp;5,000</span>
            </p>
            <nav class="flex flex-wrap gap-x-5 gap-y-2 text-meta" aria-label="Legal">
                <a href="/privacy" class="text-text-secondary no-underline hover:text-ivory">Privacy</a>
                <a href="/terms" class="text-text-secondary no-underline hover:text-ivory">Terms</a>
                <a href="/disclaimer" class="text-text-secondary no-underline hover:text-ivory">Disclaimer</a>
            </nav>
        </div>
    </div>
</footer>
