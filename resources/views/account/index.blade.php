<x-layouts.app title="My account" current="account" :bottom-nav="false">
    <x-slot:head>
        <meta name="robots" content="noindex,nofollow">
    </x-slot:head>


    <x-ui.section>
        <div class="mx-auto max-w-2xl">

            {{-- Header --}}
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <x-ui.overline>My account</x-ui.overline>
                    <h1 class="mt-2 font-display text-display-sm tracking-display text-text-primary">
                        Hello, {{ \Illuminate\Support\Str::of($user->name)->before(' ')->limit(20) }}
                    </h1>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex min-h-11 items-center gap-2 rounded-sm border border-border-strong px-4
                            text-meta font-semibold text-text-primary hover:bg-surface-sunken
                            transition-[background-color] duration-[var(--motion-fast)] ease-standard">
                        Sign out
                    </button>
                </form>
            </div>

            @if (session('account_status'))
                <p role="status"
                    class="mt-6 rounded-sm border border-success-600 bg-success-50 px-4 py-3 text-body text-success-700">
                    {{ session('account_status') }}
                </p>
            @endif

            {{-- Profile --}}
            <div class="mt-8 rounded-lg border border-border-subtle bg-surface-raised p-6">
                <h2 class="text-title-sm text-text-primary">Your details</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-3 text-body sm:grid-cols-2">
                    <div>
                        <dt class="text-meta font-semibold text-text-primary">Name</dt>
                        <dd class="text-text-secondary">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-meta font-semibold text-text-primary">Email</dt>
                        <dd class="select-all text-text-secondary">{{ $user->email }}</dd>
                    </div>
                    @if ($user->phone)
                        <div>
                            <dt class="text-meta font-semibold text-text-primary">Phone</dt>
                            <dd class="select-all text-text-secondary">{{ $user->phone }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-meta font-semibold text-text-primary">Member since</dt>
                        <dd class="text-text-secondary">{{ $user->created_at?->format('j F Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Marketing preference — the consented opt-in --}}
            <form method="POST" action="{{ route('account.marketing') }}"
                class="mt-6 rounded-lg border border-border-subtle bg-surface-raised p-6">
                @csrf
                <h2 class="text-title-sm text-text-primary">Email preferences</h2>
                <label class="mt-4 flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="accepts_marketing" value="1" @checked($user->accepts_marketing)
                        class="mt-1 h-5 w-5 shrink-0 rounded-xs border-border-strong text-gold-surface
                            focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-info-600">
                    <span class="text-body text-text-secondary">
                        Email me about new products and offers. You can unsubscribe at any time.
                    </span>
                </label>
                <div class="mt-4">
                    <x-ui.button type="submit" variant="secondary" size="sm">Save preferences</x-ui.button>
                </div>
            </form>

            {{-- Orders --}}
            <div class="mt-6 rounded-lg border border-border-subtle bg-surface-raised p-6">
                <h2 class="text-title-sm text-text-primary">Your orders</h2>

                @if ($orders->isEmpty())
                    <p class="mt-3 text-body text-text-secondary">
                        You have not placed any orders yet.
                        <a href="/shop" class="text-text-primary underline decoration-1 underline-offset-[3px] hover:decoration-2">Start shopping →</a>
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-border-subtle border-t border-border-subtle">
                        @foreach ($orders as $order)
                            @php
                                $s = $order->status;
                                $statusLabel = \Illuminate\Support\Str::headline($s instanceof \BackedEnum ? $s->value : (string) $s);
                            @endphp
                            <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                                <div>
                                    <p class="text-body font-semibold text-text-primary">#{{ $order->order_number }}</p>
                                    <p class="text-meta text-text-muted">
                                        {{ $order->created_at?->format('j M Y') }}
                                        <span class="mx-1" aria-hidden="true">·</span>{{ $statusLabel }}
                                    </p>
                                </div>
                                @if ($order->public_token)
                                    <a href="{{ route('orders.confirmation', $order->public_token) }}"
                                        class="text-meta font-semibold text-text-primary underline decoration-1 underline-offset-[3px] hover:decoration-2">
                                        View order
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

        </div>
    </x-ui.section>

</x-layouts.app>
