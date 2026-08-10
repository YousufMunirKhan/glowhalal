<?php

namespace App\Contracts\Payments;

/**
 * Architecture §6.1 — the only question checkout ever asks a driver:
 * "I have placed this order. What happens next?"
 *
 * Checkout branches on this, never on the driver key. Adding JazzCash later
 * therefore changes zero lines of checkout code.
 */
enum PaymentAction
{
    case Completed;     // nothing further — go to the confirmation page
    case Redirect;      // POST/GET the customer to the gateway
    case Instructions;  // render on-site instructions (bank details, proof upload)
    case Failed;
}
