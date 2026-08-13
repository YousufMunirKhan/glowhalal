@props([
    'current' => 'home',
    'cartCount' => 0,
    'whatsapp' => null,
])

@php
    // Single source of truth: Admin → Store settings (WhatsApp number).
    $whatsapp = $whatsapp ?? (($store ?? null)?->whatsappLink('Hi, I have a question about Glow Halal') ?? 'https://wa.me/923012973886');
@endphp

{{--
  Design system §5.3.

  WhatsApp in the nav bar is unusual in Western design and exactly right here.

  Labels are ALWAYS visible — icon-only navigation fails the older, less
  confident persona.

  Fixed dark, matching the header and the sheet. Active state is
  --text-primary glyph + label at weight 600 plus a 2px rule in the same
  colour; inactive is --text-secondary. Both semantic, so the one markup serves
  the light chrome of a future light page and the dark chrome here:
    #F2F0EC on #121212  16.46:1 PASS AAA  active
    #A8A29A on #121212   7.40:1 PASS AAA  inactive
  Weight and the top rule carry the state, never colour alone.

  Suppressed on PDP (replaced by the sticky buy bar), cart, checkout and OTP.
  Where this bar is absent, the floating WhatsApp button renders instead —
  never both at once.
--}}

@php
    $items = [
        ['key' => 'home', 'label' => 'Home', 'icon' => 'home', 'href' => '/'],
        ['key' => 'shop', 'label' => 'Shop', 'icon' => 'shop', 'href' => '/shop'],
        ['key' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'whatsapp', 'href' => $whatsapp, 'external' => true],
        ['key' => 'cart', 'label' => 'Cart', 'icon' => 'cart', 'href' => '/cart'],
    ];
@endphp

<nav aria-label="Primary" data-surface="dark"
    class="fixed inset-x-0 bottom-0 z-[var(--z-bottom-nav)] border-t border-border-subtle bg-dark-bg
        pb-[var(--safe-bottom)] shadow-bar lg:hidden">
    <ul class="flex">
        @foreach ($items as $item)
            @php $isCurrent = $current === $item['key']; @endphp
            <li class="w-1/4">
                <a href="{{ $item['href'] }}"
                    @if ($item['external'] ?? false) target="_blank" rel="noopener" @endif
                    @if ($isCurrent) aria-current="page" @endif
                    class="relative flex h-14 flex-col items-center justify-center gap-0.5 no-underline
                        {{ $isCurrent ? 'font-semibold text-text-primary' : 'font-normal text-text-secondary' }}">
                    @if ($isCurrent)
                        <span class="absolute inset-x-0 top-0 h-0.5 bg-text-primary" aria-hidden="true"></span>
                    @endif
                    <x-ui.icon :name="$item['icon']" :size="22" />
                    <span class="text-meta leading-none">{{ $item['label'] }}</span>
                    @if ($item['key'] === 'cart' && $cartCount > 0)
                        <span class="sr-only">{{ $cartCount }} items</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
