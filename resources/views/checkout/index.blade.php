{{-- /checkout — noindex, nofollow and robots-Disallowed (seo.md §2.2). --}}

<x-layouts.app
    title="Checkout | Glow Halal"
    description="Cash on Delivery checkout."
    current="cart"
    :bottom-nav="false">

    <x-slot:head>
        <meta name="robots" content="noindex,nofollow">
    </x-slot:head>

    <x-ui.section>
        <livewire:checkout :direct="$direct" />
    </x-ui.section>
</x-layouts.app>
