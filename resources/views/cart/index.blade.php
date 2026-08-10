{{--
  /cart — noindex, nofollow and robots-Disallowed (seo.md §2.2, §8.8).
  A cart page has nothing to rank for and every reason not to be crawled.
--}}

<x-layouts.app
    title="Your bag | Glow Halal"
    description="Review your bag before checkout."
    current="cart"
    :cart-count="$cartCount"
    :bottom-nav="false">

    <x-slot:head>
        <meta name="robots" content="noindex,nofollow">
    </x-slot:head>

    <x-ui.section>
        <livewire:cart.page />
    </x-ui.section>
</x-layouts.app>
