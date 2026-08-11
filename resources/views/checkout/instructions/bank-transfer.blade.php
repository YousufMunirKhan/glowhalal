{{--
  Bank-transfer instructions. Rendered by the driver's PaymentInitiation, so
  checkout itself never learns what a bank transfer is.
--}}

<div class="rounded-lg border border-border-subtle bg-surface-raised p-6">
    <h2 class="text-title">Transfer {{ $amount->format() }} to complete your order</h2>

    <p class="mt-2 text-meta text-text-secondary">
        Use the order number as the payment reference so we can match it without asking you.
        @if ($expiresAt)
            We hold your stock until {{ $expiresAt->format('j F, g:ia') }}.
        @endif
    </p>

    <dl class="mt-6 grid gap-3 text-body">
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border-subtle pb-3">
            <dt class="text-text-secondary">Bank</dt>
            <dd class="text-text-primary">{{ $account->bank_name }}</dd>
        </div>
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border-subtle pb-3">
            <dt class="text-text-secondary">Account title</dt>
            <dd class="text-text-primary">{{ $account->account_title }}</dd>
        </div>
        @if ($account->account_number)
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border-subtle pb-3">
                <dt class="text-text-secondary">Account number</dt>
                <dd class="select-all font-mono text-inci text-text-primary">{{ $account->account_number }}</dd>
            </div>
        @endif
        @if ($account->iban)
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border-subtle pb-3">
                <dt class="text-text-secondary">IBAN</dt>
                <dd class="select-all font-mono text-inci text-text-primary">{{ $account->iban }}</dd>
            </div>
        @endif
        <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border-subtle pb-3">
            <dt class="text-text-secondary">Reference</dt>
            <dd class="select-all font-mono text-inci text-text-primary">{{ $reference }}</dd>
        </div>
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <dt class="text-text-secondary">Amount</dt>
            <dd class="text-price tabular-nums text-text-primary">{{ $amount->format() }}</dd>
        </div>
    </dl>

    @if ($account->instructions)
        <p class="mt-4 text-meta text-text-secondary">{{ $account->instructions }}</p>
    @endif

    <p class="mt-6 text-meta text-text-secondary">
        Send the receipt on WhatsApp to
        <a href="{{ ($store ?? null)?->whatsappLink() ?? '/contact' }}" class="text-text-gold underline decoration-1 underline-offset-4">{{ ($store ?? null)?->contact_phone ?? 'our contact page' }}</a>
        and we will verify it within one business day.
    </p>
</div>
