{{--
  Single-page checkout. Six fields. Cash on Delivery pre-selected.

  What is NOT here, and why:
    CNIC          — the single biggest abandonment cause on Pakistani COD
                    checkouts, and PII we have no lawful need to hold.
    Postal code   — no local courier routes on it. It is a field that can only
                    be got wrong.
    Account       — a guest must be able to buy. Requiring registration on a
                    COD store is the other half of that abandonment number.
    Billing address — there is no card, so there is nothing to match it against.

  The total shown here is display state. PlaceOrderAction recalculates it
  server-side from live prices and the destination actually typed before a
  single row is written; the browser's number never decides what is charged.
--}}

@php
    $totals = $this->totals;
    $cart = $this->cart;
    $items = $cart?->items ?? collect();
    $quote = $totals->shippingQuote;
    $drivers = $this->paymentDrivers;
@endphp

<div>
    {{-- Consent-gated analytics reads this (partials/tracking.blade.php) to fire
         GA4 begin_checkout + Meta InitiateCheckout once on load. Item `id` is the
         variant SKU — the same id used in the product feeds. No PII: names here
         are product names, never the customer's. --}}
    @php
        $ghCheckoutItems = $items->map(fn ($item) => [
            'id' => $item->variant?->sku,
            'name' => $item->name_snapshot,
            'price' => (float) ($item->unit_price_amount?->toRupees() ?? 0),
            'quantity' => (int) $item->quantity,
        ])->values()->all();
    @endphp
    <div data-gh-begin-checkout
        data-value="{{ number_format((float) $totals->subtotal->toRupees(), 2, '.', '') }}"
        data-items="{{ json_encode($ghCheckoutItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
        hidden></div>

    <h1 class="font-display text-display-sm">Checkout</h1>
    <p class="mt-2 text-meta text-text-secondary">
        Six fields. No account. Pay the courier in cash when it arrives.
    </p>

    @if ($problems)
        <div role="alert"
            class="mt-8 rounded-md border border-warning-600 bg-warning-50 p-4 text-body text-warning-600">
            <p class="font-semibold">Your bag changed while you were here</p>
            <ul class="mt-2 list-disc ps-5">
                @foreach ($problems as $problem)
                    <li>{{ $problem }}</li>
                @endforeach
            </ul>
            <p class="mt-2 text-meta">Check the summary, then place the order again.</p>
        </div>
    @endif

    <form wire:submit="placeOrder" class="mt-10 grid gap-12 lg:grid-cols-[1fr_22rem] lg:gap-16">

        {{-- The six fields ------------------------------------------------- --}}
        <div class="grid gap-6 max-w-[var(--container-form)]">

            <div>
                <label for="customerName" class="block text-meta font-semibold text-text-primary">Full name</label>
                <input id="customerName" type="text" wire:model.blur="customerName"
                    autocomplete="name" required
                    class="mt-2 h-12 w-full rounded-sm border border-border-strong bg-surface px-3 text-body text-text-primary">
                @error('customerName') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-meta font-semibold text-text-primary">Mobile number</label>
                <input id="phone" type="tel" wire:model.blur="phone"
                    autocomplete="tel" inputmode="tel" placeholder="0300 1234567" required
                    class="mt-2 h-12 w-full rounded-sm border border-border-strong bg-surface px-3 text-body tabular-nums text-text-primary">
                <p class="mt-1 text-meta text-text-muted">We confirm every order by SMS before dispatch.</p>
                @error('phone') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-meta font-semibold text-text-primary">Email <span class="font-normal text-text-muted">(optional — for a receipt)</span></label>
                <input id="email" type="email" wire:model.blur="email"
                    autocomplete="email" inputmode="email"
                    class="mt-2 h-12 w-full rounded-sm border border-border-strong bg-surface px-3 text-body text-text-primary">
                <p class="mt-1 text-meta text-text-muted">We confirm your order by SMS. Add an email only if you'd like a receipt — no marketing unless you ask.</p>
                @error('email') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="addressLine1" class="block text-meta font-semibold text-text-primary">Delivery address</label>
                <textarea id="addressLine1" wire:model.blur="addressLine1" rows="3"
                    autocomplete="street-address" required
                    placeholder="House or shop number, street, area"
                    class="mt-2 w-full rounded-sm border border-border-strong bg-surface p-3 text-body text-text-primary"></textarea>
                @error('addressLine1') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
            </div>

            {{-- Province is asked FIRST, then city. --}}
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="province" class="block text-meta font-semibold text-text-primary">Province</label>
                    <select id="province" wire:model.live="province" required
                        class="mt-2 h-12 w-full rounded-sm border border-border-strong bg-surface px-3 text-body text-text-primary">
                        <option value="">Choose…</option>
                        @foreach ($this->provinces() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('province') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="city" class="block text-meta font-semibold text-text-primary">City</label>
                    <input id="city" type="text" wire:model.live.blur="city" list="gh-cities"
                        autocomplete="address-level2" required
                        class="mt-2 h-12 w-full rounded-sm border border-border-strong bg-surface px-3 text-body text-text-primary">
                    <datalist id="gh-cities">
                        @foreach (\App\Livewire\Checkout::CITIES as $city)
                            <option value="{{ $city }}"></option>
                        @endforeach
                    </datalist>
                    @error('city') <p class="mt-1 text-meta text-danger-700">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Payment ---------------------------------------------------- --}}
            <fieldset class="mt-2">
                <legend class="text-title-sm">How you would like to pay</legend>

                <div class="mt-4 grid gap-3">
                    @foreach ($drivers as $driver)
                        <label wire:key="pay-{{ $driver->key() }}"
                            @class([
                                'flex cursor-pointer items-start gap-3 rounded-sm border p-4',
                                'border-text-primary bg-surface-sunken' => $paymentMethod === $driver->key(),
                                'border-border-subtle' => $paymentMethod !== $driver->key(),
                            ])>
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $driver->key() }}"
                                name="paymentMethod" class="mt-1 h-5 w-5 accent-[var(--color-gold-surface)]">
                            <span>
                                <span class="block text-body font-semibold text-text-primary">{{ $driver->label() }}</span>
                                @if ($driver->description())
                                    <span class="mt-1 block text-meta text-text-secondary">{{ $driver->description() }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

                @error('paymentMethod') <p class="mt-2 text-meta text-danger-700">{{ $message }}</p> @enderror
            </fieldset>

            <div>
                <button type="button" wire:click="$toggle('showNote')"
                    class="min-h-11 text-meta font-semibold text-text-gold underline decoration-1 underline-offset-4">
                    {{ $showNote ? 'Hide note' : 'Add a note for the courier (optional)' }}
                </button>

                <div wire:show="showNote" class="mt-3">
                    <textarea wire:model.blur="customerNote" rows="2"
                        placeholder="Landmark, best time to deliver, anything else"
                        class="w-full rounded-sm border border-border-strong bg-surface p-3 text-body text-text-primary"></textarea>
                </div>
            </div>
        </div>

        {{-- Summary ------------------------------------------------------- --}}
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <div class="rounded-lg border border-border-subtle bg-surface-raised p-6">
                <h2 class="text-title">Your order</h2>

                <ul class="mt-4 grid gap-3">
                    @foreach ($items as $item)
                        <li wire:key="co-{{ $item->id }}" class="flex items-baseline justify-between gap-4 text-meta">
                            <span class="text-text-secondary">
                                {{ $item->name_snapshot }}
                                @if ($item->variant?->name)
                                    <span class="text-text-muted">· {{ $item->variant->name }}</span>
                                @endif
                                <span class="text-text-muted">× {{ $item->quantity }}</span>
                            </span>
                            <span class="tabular-nums text-text-primary">
                                {{ $item->unit_price_amount->times($item->quantity)->format() }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-6 grid gap-3 border-t border-border-subtle pt-4 text-body">
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-text-secondary">Subtotal</dt>
                        <dd class="tabular-nums text-text-primary">{{ $totals->subtotal->format() }}</dd>
                    </div>

                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-text-secondary">
                            Delivery
                            @if ($quote?->deliveryWindow())
                                <span class="block text-meta text-text-muted">{{ $quote->label }} · {{ $quote->deliveryWindow() }}</span>
                            @endif
                        </dt>
                        <dd class="tabular-nums text-text-primary" data-shipping>
                            {{ $totals->shipping->isZero() ? 'Free' : $totals->shipping->format() }}
                        </dd>
                    </div>

                    @if (! $totals->codFee->isZero())
                        <div class="flex items-baseline justify-between gap-4">
                            <dt class="text-text-secondary">Cash on Delivery fee</dt>
                            <dd class="tabular-nums text-text-primary">{{ $totals->codFee->format() }}</dd>
                        </div>
                    @endif

                    <div class="mt-2 flex items-baseline justify-between gap-4 border-t border-border-subtle pt-4">
                        <dt class="text-title-sm">Total</dt>
                        <dd class="text-price tabular-nums text-text-primary" data-total>{{ $totals->grandTotal->format() }}</dd>
                    </div>
                </dl>

                @if ($paymentMethod === 'cod')
                    <p class="mt-4 rounded-sm bg-surface-sunken p-3 text-meta text-text-primary">
                        Have <strong class="tabular-nums">{{ $totals->grandTotal->format() }}</strong> ready for the courier.
                        The confirmation SMS repeats this amount.
                    </p>
                @endif

                <button type="submit" wire:loading.attr="disabled" wire:target="placeOrder"
                    class="mt-6 inline-flex min-h-14 w-full items-center justify-center rounded-sm bg-gold-surface px-8
                        text-body font-semibold text-ink-900 transition-[background-color] duration-[var(--motion-fast)]
                        ease-standard hover:bg-gold-surface-hover disabled:opacity-70">
                    <span wire:loading.remove wire:target="placeOrder">Place order</span>
                    <span wire:loading wire:target="placeOrder">Placing your order…</span>
                </button>

                <p class="mt-4 text-center text-meta text-text-secondary">
                    <a href="{{ route('cart.index') }}" class="underline decoration-1 underline-offset-4">Back to bag</a>
                </p>
            </div>
        </aside>
    </form>
</div>
