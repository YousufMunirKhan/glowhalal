<?php

namespace App\Services\Payments\Drivers;

use App\Contracts\Payments\CallbackResult;
use App\Contracts\Payments\PaymentCapability;
use App\Contracts\Payments\PaymentContext;
use App\Contracts\Payments\PaymentDriver;
use App\Contracts\Payments\PaymentInitiation;
use App\Contracts\Payments\PaymentOutcome;
use App\Enums\PaymentAttemptStatus;
use App\Models\BankAccount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Architecture §6.6. */
final class BankTransferDriver implements PaymentDriver
{
    public function __construct(private readonly array $config = []) {}

    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return 'Bank Transfer';
    }

    public function description(): ?string
    {
        return 'Transfer to our account and send us the receipt. We verify within one business day.';
    }

    public function iconPath(): ?string
    {
        return null;
    }

    public function capabilities(): array
    {
        return [PaymentCapability::ManualVerification, PaymentCapability::ProofUpload, PaymentCapability::Refund];
    }

    public function isAvailableFor(PaymentContext $context): bool
    {
        return BankAccount::where('is_active', true)->exists();
    }

    public function surchargeFor(PaymentContext $context): Money
    {
        return Money::zero();
    }

    public function initiate(Order $order, string $idempotencyKey): PaymentInitiation
    {
        $account = BankAccount::where('is_active', true)->orderBy('position')->first();

        if (! $account) {
            $payment = Payment::firstOrCreate(
                ['idempotency_key' => $idempotencyKey],
                [
                    'order_id' => $order->id,
                    'driver' => $this->key(),
                    'status' => PaymentAttemptStatus::Failed,
                    'currency' => $order->currency,
                    'amount' => $order->grand_total_amount->minorUnits,
                    'failure_message' => 'No active bank account is configured.',
                ],
            );

            return PaymentInitiation::failed($payment, 'Bank transfer is temporarily unavailable. Please choose Cash on Delivery.');
        }

        $hours = (int) ($this->config['payment_window_hours'] ?? 48);

        $payment = Payment::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'order_id' => $order->id,
                'driver' => $this->key(),
                'status' => PaymentAttemptStatus::AwaitingVerification,
                'currency' => $order->currency,
                'amount' => $order->grand_total_amount->minorUnits,
                'bank_account_id' => $account->id,
                'expires_at' => now()->addHours($hours),
            ],
        );

        return PaymentInitiation::instructions($payment, 'checkout.instructions.bank-transfer', [
            'account' => $account,
            'amount' => $order->grand_total_amount,
            'reference' => $order->order_number,
            'expiresAt' => $payment->expires_at,
        ]);
    }

    public function handleCallback(Request $request): CallbackResult
    {
        throw new \LogicException('Bank transfer is verified by an administrator, not by callback.');
    }

    public function verify(Payment $payment): PaymentOutcome
    {
        $approved = $payment->proofs()->where('status', 'approved')->exists();

        return new PaymentOutcome(
            successful: $approved,
            status: $approved ? PaymentAttemptStatus::Paid : PaymentAttemptStatus::AwaitingVerification,
        );
    }

    /** Called by the Filament approve action. */
    public function approveProof(PaymentProof $proof, User $admin): PaymentOutcome
    {
        return DB::transaction(function () use ($proof, $admin) {
            $proof->forceFill([
                'status' => 'approved',
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at' => now(),
            ])->save();

            $payment = $proof->payment;

            $payment->forceFill([
                'status' => PaymentAttemptStatus::Paid,
                'reference' => $proof->declared_reference,
                'verified_by_user_id' => $admin->id,
                'verified_at' => now(),
                'paid_at' => now(),
            ])->save();

            return new PaymentOutcome(true, PaymentAttemptStatus::Paid, reference: $payment->reference);
        });
    }

    public function refund(Payment $payment, Money $amount, string $reason): PaymentOutcome
    {
        $payment->forceFill([
            'refunded_amount' => ($payment->refunded_amount?->minorUnits ?? 0) + $amount->minorUnits,
            'status' => PaymentAttemptStatus::Refunded,
        ])->save();

        return new PaymentOutcome(true, PaymentAttemptStatus::Refunded,
            message: "Refund of {$amount->format()} recorded. Transfer manually and note the reference.");
    }
}
