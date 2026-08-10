@props([
    'cartCount' => 0,
    'current' => null,
])

{{--
  Design system §5.1 — the header.

  Five primary nav items, and the ORDER IS THE POSITIONING:
    Shop · What We Never Use · Ingredient Index · About · Journal

  "What We Never Use" sits second because it is the asset no competitor has,
  and because — with no third-party approval to point at — it and the
  Ingredient Index ARE the trust argument. It is a top-level nav item, never a footer link.
  Contact / Track order rank below these five and live in the footer and the
  mobile drawer.

  There is deliberately no third-party-approval item in this nav and there must
  never be one: Glow Halal holds no halal accreditation from any body.

  No hide-on-scroll. Auto-hide saves 56px and costs the persistent cart badge
  plus a scroll listener's frame budget — a bad trade on a low-end device.

  LIGHT REDESIGN. The bar now follows the theme (bg-surface, semantic tokens)
  to sit above the light, product-forward homepage — a white header over a
  light hero. Everything inside speaks in semantic tokens, so it resolves in
  both the light and dark themes without a single dark: variant here.

  Current page: 2px underline in --text-primary PLUS weight 600. Never a gold
  underline — even where gold passes as text, gold as the *only* state
  indicator repeats the colour-alone mistake, and the system's rule is that
  state is carried by the neutral. Gold stays on the hover flourish, which
  carries nothing.
--}}

@php
    $nav = [
        ['label' => 'Home', 'href' => '/', 'key' => 'home'],
        ['label' => 'Shop', 'href' => '/shop', 'key' => 'shop'],
        ['label' => 'What We Never Use', 'href' => '/what-we-never-use', 'key' => 'never-use'],
        ['label' => 'Ingredient Index', 'href' => '/halal-ingredients', 'key' => 'ingredient-index'],
        ['label' => 'About', 'href' => '/about', 'key' => 'about'],
        ['label' => 'Journal', 'href' => '/blog', 'key' => 'journal'],
    ];
@endphp

<header
    class="sticky top-0 z-[var(--z-header)] border-b border-luxe-border bg-ivory/95 backdrop-blur-sm supports-[backdrop-filter]:bg-ivory/80">
    <div class="container-page flex h-14 items-center gap-2 lg:h-18">

        {{-- Mobile: menu button --}}
        <button type="button" id="menu-button" class="tap-safe -ms-3 grid place-items-center rounded-sm
                text-charcoal hover:bg-champagne/10 lg:hidden
                transition-[background-color] duration-[var(--motion-fast)] ease-standard"
            aria-label="Menu" aria-expanded="false" aria-controls="mobile-menu" data-menu-open>
            <x-ui.icon name="menu" />
        </button>

        {{-- Wordmark: centred on mobile, inline-start on desktop --}}
        <a href="/" class="flex flex-1 justify-center text-charcoal lg:flex-none lg:justify-start"
            aria-label="Glow Halal — home">
            {{-- Stacked lockup (GLOW over HALAL), 2:1 — matches the brand artwork.
                 Sized by height so the aspect ratio is never distorted. --}}
            <x-wordmark class="h-11 w-auto lg:h-12" />
        </a>

        {{-- Desktop primary navigation --}}
        <nav class="hidden lg:ms-8 lg:flex lg:flex-1 lg:items-center lg:gap-6"
            aria-label="Primary">
            @foreach ($nav as $item)
                @php $isCurrent = $current === $item['key']; @endphp
                <a href="{{ $item['href'] }}" @if ($isCurrent) aria-current="page" @endif
                    class="group relative inline-flex h-18 items-center text-body text-charcoal no-underline
                        transition-colors duration-[var(--motion-fast)] ease-standard hover:text-luxe-black
                        {{ $isCurrent ? 'font-semibold' : 'font-normal' }}">
                    {{ $item['label'] }}
                    @if ($isCurrent)
                        {{-- State indicator: the neutral, not gold. --}}
                        <span class="absolute inset-x-0 bottom-4 h-0.5 bg-charcoal" aria-hidden="true"></span>
                    @else
                        {{-- Decorative champagne hover flourish only. Gold is legal here
                             because it carries no information — the current page is marked
                             above by the neutral underline + weight. --}}
                        <span aria-hidden="true"
                            class="absolute inset-x-0 bottom-4 h-0.5 origin-center scale-x-0 bg-champagne
                                transition-transform duration-[var(--motion-fast)] ease-standard
                                group-hover:scale-x-100"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Utilities --}}
        <div class="flex items-center gap-1">
            {{-- Search intentionally omitted: there is no /search route yet, and a header
                 icon that 404s is worse than no icon. Restore this block once a search
                 page exists — the SEO spec also wants a WebSite SearchAction pointing at it. --}}

            {{-- Account — optional customer sign-in (Google). Guest checkout still
                 works with no account, so this is a convenience, never a gate. --}}
            {{-- Account: a person icon in the utility row (matches the mockup).
                 Guest → sign in; signed-in → sign out. Icon-only with a label. --}}
            @auth
                {{-- Signed in: show the person icon PLUS the first name so the
                     shopper can see they are logged in. Name hides on very small
                     screens to keep the mobile bar tidy. --}}
                <a href="{{ route('account') }}"
                    aria-label="My account — {{ auth()->user()->name }}"
                    class="tap-safe flex h-10 items-center gap-1.5 rounded-sm px-2 text-charcoal hover:bg-champagne/10
                        transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    <x-ui.icon name="user" />
                    <span class="hidden text-meta font-medium sm:inline">{{ \Illuminate\Support\Str::before(trim(auth()->user()->name), ' ') ?: 'Account' }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" aria-label="Sign in"
                    class="tap-safe grid h-10 w-10 place-items-center rounded-sm text-charcoal hover:bg-champagne/10
                        transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                    <x-ui.icon name="user" />
                </a>
            @endauth

            {{-- Live cart control. A Livewire island reads the REAL count from the
                 active cart on first paint and re-renders on 'cart-updated'; the
                 button opens the slide-in drawer rather than navigating to /cart. --}}
            <livewire:cart.cart-badge />
        </div>
    </div>
</header>
