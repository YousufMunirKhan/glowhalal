<?php

namespace App\Services\Orders;

final class CheckoutValidationException extends \RuntimeException
{
    /** @param array<int, CheckoutProblem> $problems */
    public function __construct(public readonly array $problems)
    {
        parent::__construct('Some items in your bag need attention before you can order.');
    }

    /** @return array<int, string> */
    public function messages(): array
    {
        return array_map(fn (CheckoutProblem $p) => $p->message, $this->problems);
    }
}
