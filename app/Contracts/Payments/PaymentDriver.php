<?php

namespace App\Contracts\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;

/**
 * Architecture §6.2 — the payment contract.
 *
 * Checkout never learns what a payment method *is*. It asks `initiate()` and
 * branches on the returned PaymentAction. A local gateway (JazzCash, Easypaisa)
 * is added by writing a class against this interface and adding one entry to
 * config/payments.php — no route change, no controller change, no checkout change.
 */
interface PaymentDriver
{
    /** Stable machine key. Persisted in orders.payment_method and payments.driver. Never rename. */
    public function key(): string;

    /** Customer-facing label at checkout. */
    public function label(): string;

    public function description(): ?string;

    public function iconPath(): ?string;

    /** @return array<int, PaymentCapability> */
    public function capabilities(): array;

    /**
     * Is this method offerable for this basket right now?
     * COD returns false above a value ceiling or outside serviceable cities;
     * a gateway returns false when its credentials are unconfigured.
     */
    public function isAvailableFor(PaymentContext $context): bool;

    /** Any surcharge this method adds — the COD collection fee. Money::zero() for most. */
    public function surchargeFor(PaymentContext $context): Money;

    /**
     * Begin payment for a placed order. Creates the Payment row.
     * MUST be idempotent on $idempotencyKey: calling twice returns the same
     * outcome and never creates a second charge.
     */
    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation;

    /**
     * Handle an inbound gateway callback/webhook. Implementations MUST verify
     * authenticity (HMAC/signature) before trusting any field.
     */
    public function handleCallback(Request $request): CallbackResult;

    /** Re-check a payment's state. Manual drivers consult the admin decision; gateways poll. */
    public function verify(Payment $payment): PaymentOutcome;

    /** Full or partial refund. Manual drivers record intent; gateways call the API. */
    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome;
}
