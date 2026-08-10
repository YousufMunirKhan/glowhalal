<?php

namespace App\Contracts\Payments;

use App\Models\Payment;

/**
 * Architecture §6.3.
 *
 * Private constructor + named factories, so the illegal states are
 * unconstructible: there is no way to produce a Redirect initiation without a
 * URL, or a Failed one without a message. Checkout can `match` on `$action`
 * exhaustively and be certain the fields it needs are populated.
 */
final readonly class PaymentInitiation
{
    private function __construct(
        public PaymentAction $action,
        public Payment $payment,
        public ?string $redirectUrl = null,
        public string $redirectMethod = 'POST',
        /** @var array<string, string> */
        public array $redirectFields = [],
        public ?string $instructionsView = null,
        /** @var array<string, mixed> */
        public array $instructionsData = [],
        public ?string $failureMessage = null,
    ) {}

    public static function completed(Payment $payment): self
    {
        return new self(PaymentAction::Completed, $payment);
    }

    /** @param array<string, string> $fields */
    public static function redirect(Payment $payment, string $url, array $fields = [], string $method = 'POST'): self
    {
        return new self(
            PaymentAction::Redirect,
            $payment,
            redirectUrl: $url,
            redirectMethod: $method,
            redirectFields: $fields,
        );
    }

    /** @param array<string, mixed> $data */
    public static function instructions(Payment $payment, string $view, array $data = []): self
    {
        return new self(PaymentAction::Instructions, $payment, instructionsView: $view, instructionsData: $data);
    }

    public static function failed(Payment $payment, string $message): self
    {
        return new self(PaymentAction::Failed, $payment, failureMessage: $message);
    }
}
