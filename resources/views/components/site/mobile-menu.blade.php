@props([
    'products' => [],
    'whatsapp' => null,
])

@php
    // Single source of truth: Admin → Store settings (WhatsApp number).
    $whatsapp = $whatsapp ?? (($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal') ?? 'https://wa.me/923012973886');
@endphp

{{--
  Design system §5.2 — the mobile menu is a BOTTOM SHEET, not a top-anchored
  drawer: top-left is the hardest point on the screen to reach one-handed.

  The Shop group lists individual PRODUCT NAMES, not categories. With three
  SKUs, a "Skincare" link that leads to one item is a dead end that broadcasts
  emptiness.

  This markup is server-rendered and present in the initial HTML. The sheet is
  hidden with the `hidden` attribute rather than being injected by JS, so the
  links are crawlable and the menu degrades to nothing worse than "always
  closed" if JS fails.
--}}

@php
    $groups = [
        [
            'title' => 'Shop',
            'items' => array_merge(
                // Same locale rule as the footer: on /ur-roman pages link the UR
                // PDP (with its UR name) when one exists. Keys coalesced because
                // some pages pass bare name/slug arrays.
                collect($products)->map(fn ($p) => app()->getLocale() === 'ur-Latn' && ! empty($p['slug_ur'] ?? null)
                    ? ['label' => $p['name_ur'], 'href' => '/ur-roman/products/' . $p['slug_ur']]
                    : ['label' => $p['name'], 'href' => '/products/' . $p['slug']])->all(),
                [['label' => 'Shop all', 'href' => '/shop']],
            ),
        ],
        [
            'title' => 'Why Glow Halal',
            'items' => [
                ['label' => 'What We Never Use', 'href' => '/what-we-never-use'],
                ['label' => 'Ingredient Index', 'href' => '/halal-ingredients'],
            ],
        ],
        [
            'title' => null,
            'items' => [
                ['label' => 'About the Founder', 'href' => '/about'],
                ['label' => 'Journal', 'href' => '/blog'],
                ['label' => 'Contact', 'href' => '/contact'],
            ],
        ],
    ];
@endphp

{{-- Scrim --}}
<div id="mobile-menu-overlay" hidden data-menu-close
    class="fixed inset-0 z-[var(--z-overlay)] bg-ink-950/60 lg:hidden"></div>

{{-- Fixed dark, matching the header it is opened from. A white sheet sliding up
     over a near-black page is the single most jarring thing this redesign could
     do on a phone at night, which is exactly when this persona shops. --}}
<div id="mobile-menu" hidden role="dialog" aria-modal="true" aria-labelledby="mobile-menu-title"
    data-surface="dark"
    class="fixed inset-x-0 bottom-0 z-[var(--z-sheet)] flex max-h-[92dvh] flex-col rounded-t-xl
        border-t border-border-subtle bg-dark-bg shadow-sheet lg:hidden motion-safe:animate-sheet-in">

    <div class="flex justify-center pt-2 pb-1" aria-hidden="true">
        <span class="h-1 w-9 rounded-full bg-white/25"></span>
    </div>

    <div class="flex items-center justify-between border-b border-border-subtle px-4 pb-2">
        <h2 id="mobile-menu-title" class="text-title text-text-primary">Menu</h2>
        <button type="button" data-menu-close
            class="tap-safe -me-3 grid place-items-center rounded-sm text-text-primary hover:bg-white/10"
            aria-label="Close menu">
            <x-ui.icon name="close" />
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto overscroll-contain px-4 pb-4" aria-label="Mobile">
        @foreach ($groups as $group)
            @if ($group['title'])
                <x-ui.overline as="h3" class="mt-8 mb-2 first:mt-4">{{ $group['title'] }}</x-ui.overline>
            @endif
            <ul class="{{ $group['title'] ? '' : 'mt-8 border-t border-border-subtle' }}">
                @foreach ($group['items'] as $item)
                    <li class="border-b border-border-subtle">
                        <a href="{{ $item['href'] }}"
                            class="flex min-h-14 items-center justify-between gap-3 text-body text-text-primary
                                no-underline hover:text-text-gold">
                            <span>{{ $item['label'] }}</span>
                            <x-ui.icon name="chevron-right" :size="16" class="text-text-muted" />
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach

        <div class="mt-8 pb-[var(--safe-bottom)]">
            <x-ui.button variant="whatsapp" size="md" width="full" :href="$whatsapp" :external="true">
                <x-ui.icon name="whatsapp" :size="18" />
                WhatsApp us
            </x-ui.button>
            <p class="mt-3 text-center text-meta text-text-muted">Cash on Delivery available</p>
        </div>
    </nav>
</div>
