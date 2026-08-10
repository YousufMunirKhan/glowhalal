<?php

namespace App\Services\Orders;

use App\Models\Cart;

/**
 * Thrown when a double-submitted checkout reaches a cart that the first
 * submission already converted. Carries the order so the caller can send the
 * customer to the confirmation page rather than showing an error for an order
 * that was, in fact, placed successfully.
 */
final class CartAlreadyConvertedException extends \RuntimeException
{
    public function __construct(public readonly Cart $cart)
    {
        parent::__construct('This bag has already been turned into an order.');
    }
}
