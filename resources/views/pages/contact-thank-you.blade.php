{{--
  /contact/thank-you — the 303 target of a successful POST.

  The success state deliberately does not render at /contact: a second document
  at the form's canonical breaks measurement and creates a duplicate. This page
  is noindex,follow (set in the controller).

  It states what happened and where the reply is going. It does NOT promise a
  response time — no SLA has been configured by the owner, and inventing "within
  two hours" here would be a commitment the business has not made.
--}}
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical" :current="$current"
    :robots="$robots ?? null" :products="$products" :company="$company" :founder="$founder">

    <x-slot:head>
        @include('pages.partials.head')
    </x-slot:head>

    <div class="pt-2">
        @include('pages.partials.breadcrumbs')
    </div>

    <x-ui.section surface="default">
        <div class="max-w-[var(--container-read)]">
            <div class="text-success-600">
                <x-ui.icon name="check" :size="40" stroke-width="2" />
            </div>

            <h1 class="mt-4 text-display text-text-primary">Message sent</h1>

            <p class="mt-4 text-lead text-text-secondary" role="status">
                @if ($sentTo)
                    Thank you &mdash; we have your message and we will reply to
                    <span class="select-all font-semibold text-text-primary">{{ $sentTo }}</span>.
                @else
                    Thank you &mdash; we have your message and we will reply to the address you gave us.
                @endif
            </p>

            @if ($store->opening_hours)
                <p class="mt-3 text-body text-text-secondary">{{ $store->opening_hours }}</p>
            @endif

            @if ($store->whatsappLink())
                <p class="mt-3 text-body text-text-secondary">
                    If it is urgent, WhatsApp is faster than email.
                </p>
                <div class="mt-4">
                    <x-ui.button variant="whatsapp" size="md" :href="$store->whatsappLink()" :external="true">
                        Message us on WhatsApp
                    </x-ui.button>
                </div>
            @endif

            <div class="mt-10 border-t border-border-subtle pt-6">
                <h2 class="text-title-lg text-text-primary">While you wait</h2>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-ui.button href="/halal-ingredients" variant="secondary" size="md">Search the ingredient
                        index</x-ui.button>
                    <x-ui.button href="/what-we-never-use" variant="secondary" size="md">What we never
                        use</x-ui.button>
                    <x-ui.button href="/shop" variant="tertiary">See the collection</x-ui.button>
                </div>
            </div>
        </div>
    </x-ui.section>
</x-layouts.app>
