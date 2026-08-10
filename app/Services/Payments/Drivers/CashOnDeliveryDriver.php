<?php

namespace App\Services\Payments\Drivers;

use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\PaymentCapability;
use App\Contracts\Payments\PaymentContext;
use App\Contracts\Payments\PaymentDriver;
use App\Contracts\Payments\PaymentInitiation;
use App\Contracts\Payments\PaymentOutcome;
use App\Enums\PaymentAttemptStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Illuminate\Http\Request;

/** Architecture §6.5. */
final class CashOnDeliveryDriver implements PaymentDriver
{
    public function __construct(private readonly array $config = []) {}

    public function key(): string
    {
        return 'cod';
    }

    public function label(): string
    {
        return 'Cash on Delivery';
    }

    public function description(): ?string
    {
        return 'Pay the courier in cash when your order arrives. No card, no account.';
    }

    public function iconPath(): ?string
    {
        return null;
    }

    public function capabilities(): array
    {
        return [PaymentCapability::CollectOnDelivery, PaymentCapability::ManualVerification];
    }

    public function isAvailableFor(PaymentContext $context): bool
    {
        $max = (int) ($this->config['max_order_amount'] ?? PHP_INT_MAX);

        if ($context->grandTotal->minorUnits > $max) {
            return false;
        }

        $cities = $this->config['serviceable_cities'] ?? null;

        return $cities === null
            || in_array(mb_strtolower((string) $context->city), $cities, strict: true);
    }

    /**
     * The real COD fee is carried by the matched shipping rate
     * (shipping_rates.cod_surcharge_amount) and applied by CartCalculator, which
     * is the only place that knows the destination. This is the fallback for
     * baskets with no matched rate.
     */
    public function surchargeFor(PaymentContext $context): Money
    {
        return new Money((int) ($this->config['fee_amount'] ?? 0));
    }

    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation
    {
        $payment = Payment::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'order_id' => $order->id,
                'driver' => $this->key(),
                'status' => PaymentAttemptStatus::Pending,
                'currency' => $order->currency,
                'amount' => $order->grand_total_amount->minorUnits,
            ],
        );

        // Nothing to collect now — the courier collects at the door.
        return PaymentInitiation::completed($payment);
    }

    public function handleCallback(Request $request): CallbackResult
    {
        throw new \LogicException('COD has no callback surface.');
    }

    /** Settled by the Delivered transition, which calls markCollected(). */
    public function verify(Payment $payment): PaymentOutcome
    {
        return new PaymentOutcome(
            successful: $payment->status === PaymentAttemptStatus::Paid,
            status: $payment->status,
        );
    }

    public function markCollected(Payment $payment, ?int $collectedMinorUnits = null): PaymentOutcome
    {
        $payment->forceFill([
            'status' => PaymentAttemptStatus::Paid,
            'amount' => $collectedMinorUnits ?? $payment->amount->minorUnits,
            'paid_at' => now(),
        ])->save();

        return new PaymentOutcome(true, PaymentAttemptStatus::Paid, message: 'Cash collected on delivery.');
    }

    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome
    {
        // Cash refunds happen off-system; record the intent for reconciliation.
        $payment->forceFill([
            'refunded_amount' => ($payment->refunded_amount?->minorUnits ?? 0) + $amount->minorUnits,
            'status' => PaymentAttemptStatus::Refunded,
        ])->save();

        return new PaymentOutcome(true, PaymentAttemptStatus::Refunded,
            message: "Manual cash refund of {$amount->format()} recorded. Settle offline.");
    }
}
