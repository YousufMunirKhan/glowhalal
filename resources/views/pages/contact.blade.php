{{--
  /contact — research §7.2, design system §4.7, SEO §6.2.

  The purpose of this page is proof of reachability, and its most important
  design constraint is that the FORM IS THE LEAST IMPORTANT ELEMENT ON IT. Live
  channels come first; the form is last.

  Channels render only when the owner has entered them in Store settings.
  Publishing a phone number that rings out does more damage than publishing
  none, so an unset channel shows a short "not published yet" line rather than a
  fabricated number. Nothing on this page invents a number, an address or a
  response-time promise.

  The form is a plain POST to /contact with a real server-side handler: it works
  with JavaScript off, and everything a crawler needs is in the initial HTML.
--}}
@php
    $whatsapp = $store->whatsappLink('Hi, I have a question about Glow Halal');
    $selectedSubject = old('subject', request()->query('subject'));
    $inputBase =
        'h-13 w-full rounded-sm border-[1.5px] bg-surface px-4 text-body text-text-primary ' .
        'placeholder:text-ink-500 hover:border-ink-600 focus:border-ink-900';
@endphp

<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :robots="$robots ?? null" :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head')
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <x-ui.section surface="default" class="!pb-8">
        <div class="max-w-[var(--container-read)]">
            <x-ui.overline class="mb-3">Contact</x-ui.overline>

            <h1 class="text-display text-text-primary">Contact Glow Halal</h1>

            <p class="article-answer-box mt-5 text-lead text-text-secondary">
                Message us on WhatsApp, call, or email &mdash; whichever is easiest. Questions about a specific
                ingredient are welcome even if it is in something we do not sell: we publish what we find, and those
                questions are where most of the ingredient index came from.
            </p>
        </div>
    </x-ui.section>

    {{-- ── Live channels ─────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken" class="!py-8">
        <h2 class="text-title-lg text-text-primary">Contact details</h2>

        <div class="mt-6 grid gap-4 md:grid-cols-3">

            {{-- WhatsApp, first and most prominent — it is the dominant support
                 channel in this market, not a nicety. --}}
            <div class="evidence-plate">
                <div class="flex items-center gap-2 text-whatsapp">
                    <x-ui.icon name="whatsapp" :size="24" />
                    <h3 class="text-title-sm text-text-primary">WhatsApp</h3>
                </div>

                @if ($store->whatsapp_number)
                    <p class="mt-3 select-all font-mono text-inci text-text-primary">{{ $store->whatsapp_number }}</p>
                    <div class="mt-4">
                        <x-ui.button variant="whatsapp" size="md" :href="$whatsapp" :external="true">Message
                            us</x-ui.button>
                    </div>
                @else
                    {{-- TODO (owner): Store settings → WhatsApp number. --}}
                    <p class="mt-3 text-body text-text-muted">Not published yet.</p>
                @endif

                @if ($store->opening_hours)
                    <p class="mt-3 text-meta text-text-muted">{{ $store->opening_hours }}</p>
                @endif
            </div>

            {{-- Phone --}}
            <div class="evidence-plate">
                <h3 class="text-title-sm text-text-primary">Phone</h3>

                @if ($store->contact_phone)
                    <p class="mt-3 select-all font-mono text-inci text-text-primary">{{ $store->contact_phone }}</p>
                    <div class="mt-4">
                        <x-ui.button variant="secondary" size="md"
                            :href="'tel:' . preg_replace('/[^0-9+]/', '', $store->contact_phone)">Call us</x-ui.button>
                    </div>
                @else
                    {{-- TODO (owner): Store settings → contact phone. --}}
                    <p class="mt-3 text-body text-text-muted">Not published yet.</p>
                @endif
            </div>

            {{-- Email --}}
            <div class="evidence-plate">
                <h3 class="text-title-sm text-text-primary">Email</h3>

                @if ($store->contact_email)
                    <p class="mt-3 select-all break-all text-body text-text-primary">{{ $store->contact_email }}</p>
                    <div class="mt-4">
                        <x-ui.button variant="secondary" size="md"
                            :href="'mailto:' . $store->contact_email">Email us</x-ui.button>
                    </div>
                @else
                    {{-- TODO (owner): Store settings → contact email (brand
                         domain, never a free webmail address). --}}
                    <p class="mt-3 text-body text-text-muted">Not published yet. Use the form below and we will reply
                        to you.</p>
                @endif
            </div>
        </div>

        @if ($store->formattedAddress())
            <div class="mt-6 max-w-[var(--container-read)]">
                <h3 class="text-overline uppercase text-text-muted">Address</h3>
                <address class="mt-2 select-all text-body not-italic text-text-primary">
                    {{ $store->formattedAddress() }}</address>
            </div>
        @endif
    </x-ui.section>

    {{-- ── Routing the common intents ────────────────────────────────────── --}}
    <x-ui.section surface="default">
        <div class="grid gap-8 md:grid-cols-2">
            <div>
                <h2 class="text-title-lg text-text-primary">Order support</h2>
                <p class="mt-3 text-body text-text-secondary">
                    For anything about an order that has already been placed &mdash; where it is, changing the
                    address, or sending it back &mdash; start here.
                </p>
                <ul class="mt-4">
                    @foreach ([['Shipping and returns', '/shipping-returns'], ['Frequently asked questions', '/faq']] as [$label, $href])
                        <li class="border-b border-border-subtle">
                            <a href="{{ $href }}"
                                class="flex min-h-14 items-center justify-between text-body text-text-primary hover:underline">
                                {{ $label }}
                                <x-ui.icon name="chevron-right" :size="18" class="text-text-muted" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="text-title-lg text-text-primary">Ask about an ingredient</h2>
                <p class="mt-3 text-body text-text-secondary">
                    Send the name exactly as it is printed on the pack &mdash; the INCI string, including any CI
                    number. We will tell you what it is made from and where it comes from, and if it is not in the
                    index yet we will add it.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-ui.button href="/halal-ingredients" variant="secondary" size="md">Search the
                        index</x-ui.button>
                    <x-ui.button href="#contact-form" variant="tertiary">Ask us directly</x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.section>

    {{-- ── The form, last ────────────────────────────────────────────────── --}}
    <x-ui.section surface="sunken">
        <div class="max-w-[var(--container-form)]">
            <h2 id="contact-form" class="scroll-mt-24 text-title-lg text-text-primary">Send us a message</h2>

            @if ($errors->any())
                <div role="alert"
                    class="mt-4 rounded-sm border-2 border-danger-600 bg-surface p-4 text-body font-semibold text-danger-700">
                    <p>We could not send that. Please check the fields marked below.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}" class="mt-6 space-y-5" novalidate>
                @csrf

                {{-- Honeypot. Off-screen rather than display:none — some bots
                     skip hidden inputs but fill positioned ones. Never
                     announced, never focusable. --}}
                <div class="absolute -start-[9999px]" aria-hidden="true">
                    <label for="website">Do not fill this in</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <input type="hidden" name="rendered_at" value="{{ time() }}">

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-[0.9375rem] font-semibold text-ink-800">Your name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                        autocapitalize="words" required aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
                        @if ($errors->has('name')) aria-invalid="true" @endif
                        class="mt-1.5 {{ $inputBase }} {{ $errors->has('name') ? 'border-2 border-danger-600' : 'border-border-strong' }}">
                    @error('name')
                        <p id="name-error" role="alert"
                            class="mt-1.5 flex items-center gap-1.5 text-meta font-semibold text-danger-700">
                            <x-ui.icon name="cross" :size="16" stroke-width="2.25" />{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-[0.9375rem] font-semibold text-ink-800">Email</label>
                    <input id="email" name="email" type="email" inputmode="email" value="{{ old('email') }}"
                        autocomplete="email" required
                        aria-describedby="email-help{{ $errors->has('email') ? ' email-error' : '' }}"
                        @if ($errors->has('email')) aria-invalid="true" @endif
                        class="mt-1.5 {{ $inputBase }} {{ $errors->has('email') ? 'border-2 border-danger-600' : 'border-border-strong' }}">
                    <p id="email-help" class="mt-1.5 text-meta text-text-muted">This is where the reply goes. We do
                        not send marketing unless you ask.</p>
                    @error('email')
                        <p id="email-error" role="alert"
                            class="mt-1.5 flex items-center gap-1.5 text-meta font-semibold text-danger-700">
                            <x-ui.icon name="cross" :size="16" stroke-width="2.25" />{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label for="subject" class="block text-[0.9375rem] font-semibold text-ink-800">What is this
                        about?</label>
                    <select id="subject" name="subject" required
                        aria-describedby="{{ $errors->has('subject') ? 'subject-error' : '' }}"
                        @if ($errors->has('subject')) aria-invalid="true" @endif
                        class="mt-1.5 {{ $inputBase }} {{ $errors->has('subject') ? 'border-2 border-danger-600' : 'border-border-strong' }}">
                        <option value="">Choose one</option>
                        @foreach ($subjects as $value => $label)
                            <option value="{{ $value }}" @selected($selectedSubject === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('subject')
                        <p id="subject-error" role="alert"
                            class="mt-1.5 flex items-center gap-1.5 text-meta font-semibold text-danger-700">
                            <x-ui.icon name="cross" :size="16" stroke-width="2.25" />{{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Message --}}
                <div>
                    <label for="message" class="block text-[0.9375rem] font-semibold text-ink-800">Your
                        message</label>
                    <textarea id="message" name="message" rows="5" required
                        aria-describedby="{{ $errors->has('message') ? 'message-error' : '' }}"
                        @if ($errors->has('message')) aria-invalid="true" @endif
                        class="mt-1.5 min-h-32 w-full rounded-sm border-[1.5px] bg-surface p-4 text-body text-text-primary
                            placeholder:text-ink-500 hover:border-ink-600 focus:border-ink-900
                            {{ $errors->has('message') ? 'border-2 border-danger-600' : 'border-border-strong' }}">{{ old('message') }}</textarea>
                    @error('message')
                        <p id="message-error" role="alert"
                            class="mt-1.5 flex items-center gap-1.5 text-meta font-semibold text-danger-700">
                            <x-ui.icon name="cross" :size="16" stroke-width="2.25" />{{ $message }}
                        </p>
                    @enderror
                </div>

                <x-ui.button type="submit" variant="primary" size="lg" width="full-mobile">Send message</x-ui.button>
            </form>
        </div>
    </x-ui.section>

    {{-- ── FAQ ───────────────────────────────────────────────────────────── --}}
    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <h2 class="text-title-lg text-text-primary">Frequently asked questions</h2>

            <dl class="mt-6 space-y-6">
                @foreach ($faqs as $faq)
                    <div class="border-t border-border-subtle pt-5">
                        <dt class="text-title-sm text-text-primary">{{ $faq['question'] }}</dt>
                        <dd class="mt-2 text-body text-text-secondary">{{ $faq['answer'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </x-ui.section>
</x-layouts.app>
