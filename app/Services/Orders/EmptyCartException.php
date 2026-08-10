<?php

namespace App\Services\Orders;

final class EmptyCartException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Your bag is empty.');
    }
}
