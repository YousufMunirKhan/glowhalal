<?php

namespace App\Contracts\Payments;

use App\Enums\PaymentAttemptStatus;

/** Architecture §6.3. */
final readonly class PaymentOutcome
{
    public function __construct(
        public bool $successful,
        public PaymentAttemptStatus $status,
        public ?string $reference = null,
        public ?string $message = null,
        /** @var array<string, mixed> */
        public array $raw = [],
    ) {}
}
