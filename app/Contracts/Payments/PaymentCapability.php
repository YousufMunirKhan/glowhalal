<?php

namespace App\Contracts\Payments;

/** Architecture §6.2. What a driver is able to do, declared rather than inferred. */
enum PaymentCapability
{
    case Redirect;              // sends the customer off-site
    case Webhook;               // receives asynchronous callbacks
    case ManualVerification;    // a human approves it
    case ProofUpload;           // customer uploads evidence
    case Refund;                // programmatic refunds
    case CollectOnDelivery;     // money arrives at the door, not at checkout
}
