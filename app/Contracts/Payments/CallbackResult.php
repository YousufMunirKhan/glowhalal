<?php

namespace App\Contracts\Payments;

use App\Models\Payment;

/** Architecture §6.3. `verified: false` MUST be treated as a hard 400 and logged. */
final readonly class CallbackResult
{
    public function __construct(
        public bool $verified,
        public ?Payment $payment,
        public PaymentOutcome $outcome,
        public ?string $redirectUrl = null,
        public string $acknowledgement = 'OK',
    ) {}
}
