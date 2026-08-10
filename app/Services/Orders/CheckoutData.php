<?php

namespace App\Services\Orders;

use App\Enums\PakistanProvince;

/**
 * The six-field checkout payload, validated before it gets here.
 *
 * Deliberately absent, per the UX research for the Pakistani COD market:
 * CNIC (a hard stop for most shoppers, and PII we have no lawful need for),
 * postal code (unreliable and unused by every local courier), and any account
 * or password field.
 */
final readonly class CheckoutData
{
    public function __construct(
        public string $customerName,
        public string $phone,
        // Optional on a COD store: confirmation is by SMS, so email is only a
        // receipt. Null when the shopper leaves it blank (never a fake address).
        public ?string $email,
        public string $addressLine1,
        public string $city,
        public PakistanProvince $province,
        public string $paymentMethod = 'cod',
        public ?string $customerNote = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public function firstName(): string
    {
        return trim(explode(' ', trim($this->customerName), 2)[0]);
    }

    public function lastName(): ?string
    {
        $parts = explode(' ', trim($this->customerName), 2);

        return isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : null;
    }
}
