{{--
  Order confirmation, reachable by the order's opaque public token — no
  account, no login. noindex,nofollow: this page is unique to one customer.
--}}

<x-layouts.app
    title="Order {{ $order->order_number }} | Glow Halal"
    description="Your Glow Halal order confirmation."
    current="cart"
    :robots="'noindex,follow'"
    :bottom-nav="false">

    {{-- Consent-gated analytics reads this (partials/tracking.blade.php) to fire
         the GA4 purchase event, Meta Purchase, and (if configured) the Google Ads
         conversion — exactly once, on load. THIS is the sale Google Ads / Meta
         count. `transaction_id` is the order number; item `id` is the variant SKU
         (same id as the product feeds). Nothing fires without a tracking ID AND
         cookie consent. No PII — no name, phone, email or address is sent. --}}
    @php
        $ghPurchaseItems = $order->items->map(fn ($item) => [
            'id' => $item->sku,
            'name' => $item->product_name,
            'price' => (float) ($item->unit_price_amount?->toRupees() ?? 0),
            'quantity' => (int) $item->quantity,
        ])->values()->all();
    @endphp
    <div data-gh-purchase
        data-transaction-id="{{ $order->order_number }}"
        data-value="{{ number_format((float) ($order->grand_total_amount?->toRupees() ?? 0), 2, '.', '') }}"
        data-items="{{ json_encode($ghPurchaseItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
        hidden></div>

    <x-ui.section>
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline>Order placed</x-ui.overline>

            <h1 class="mt-3 font-display text-display-sm">Thank you, {{ \Illuminate\Support\Str::before($order->customer_name, ' ') }}.</h1>

            <p class="mt-4 text-lead text-text-secondary">
                Your order is <strong class="font-mono text-inci text-text-primary">{{ $order->order_number }}</strong>.
                We will confirm it by SMS to <span class="tabular-nums">{{ $order->phone }}</span> within two hours on working days.
            </p>

            @if ($order->payment_method === 'cod')
                <div class="mt-8 evidence-plate">
                    <h2 class="text-title">Have this ready for the courier</h2>
                    <p class="mt-2 text-price tabular-nums text-text-primary">{{ $order->grand_total_amount->format() }}</p>
                    <p class="mt-2 text-meta text-text-secondary">
                        Cash on Delivery. Nothing has been charged. The confirmation SMS repeats this exact amount,
                        so you can check it against anything the courier says.
                    </p>
                </div>
            @endif

            @if ($instructions)
                <div class="mt-8">
                    @include('checkout.instructions.bank-transfer', $instructions)
                </div>
            @endif

            {{-- Items ------------------------------------------------------- --}}
            <h2 class="mt-12 text-title">What is coming</h2>

            <ul class="mt-4 grid gap-4">
                @foreach ($order->items as $item)
                    <li class="flex flex-wrap items-baseline justify-between gap-4 border-b border-border-subtle pb-4">
                        <div>
                            <p class="text-title-sm">
                                @if ($item->product_slug)
                                    <a href="{{ route('products.show', $item->product_slug) }}"
                                        class="no-underline text-text-primary hover:underline decoration-1 underline-offset-4">{{ $item->product_name }}</a>
                                @else
                                    {{ $item->product_name }}
                                @endif
                            </p>
                            @if ($item->variant_name)
                                <p class="mt-1 text-meta text-text-secondary">{{ $item->variant_name }}</p>
                            @endif
                            <p class="mt-1 text-meta text-text-muted">
                                {{ $item->unit_price_amount->format() }} × {{ $item->quantity }}
                            </p>
                        </div>
                        <p class="text-price-sm tabular-nums text-text-primary">{{ $item->line_total_amount->format() }}</p>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-6 grid gap-3 text-body">
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-text-secondary">Subtotal</dt>
                    <dd class="tabular-nums text-text-primary">{{ $order->subtotal_amount->format() }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-text-secondary">Delivery{{ $order->shipping_method_name ? ' — '.$order->shipping_method_name : '' }}</dt>
                    <dd class="tabular-nums text-text-primary">
                        {{ $order->shipping_amount->isZero() ? 'Free' : $order->shipping_amount->format() }}
                    </dd>
                </div>
                @if (! $order->cod_fee_amount->isZero())
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-text-secondary">Cash on Delivery fee</dt>
                        <dd class="tabular-nums text-text-primary">{{ $order->cod_fee_amount->format() }}</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-4 border-t border-border-subtle pt-4">
                    <dt class="text-title-sm">Total</dt>
                    <dd class="text-price tabular-nums text-text-primary">{{ $order->grand_total_amount->format() }}</dd>
                </div>
            </dl>

            {{-- Address ----------------------------------------------------- --}}
            @if ($order->shippingAddress)
                <h2 class="mt-12 text-title">Delivering to</h2>
                <address class="mt-3 not-italic text-body text-text-secondary">
                    {{ $order->shippingAddress->fullName() }}<br>
                    {{ $order->shippingAddress->singleLine() }}<br>
                    <span class="tabular-nums">{{ $order->shippingAddress->phone }}</span>
                </address>
            @endif

            <div class="mt-12 flex flex-wrap gap-4">
                <x-ui.button href="{{ route('shop.index') }}">Keep shopping</x-ui.button>
                <x-ui.button href="{{ ($store ?? null)?->whatsappLink('Hi, about order ' . $order->order_number) ?? ('https://wa.me/923001234567?text=Hi%2C%20about%20order%20' . $order->order_number) }}"
                    variant="whatsapp" external>Message us about this order</x-ui.button>
            </div>

            <p class="mt-8 text-meta text-text-muted">
                Keep this page bookmarked — it is your order record, and it works without an account.
            </p>
        </div>
    </x-ui.section>
</x-layouts.app>
