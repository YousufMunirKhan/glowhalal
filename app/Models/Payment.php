<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentAttemptStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => PaymentAttemptStatus::class,
            'amount' => MoneyCast::class,
            'refunded_amount' => MoneyCast::class,
            'gateway_request' => 'array',
            'gateway_response' => 'array',
            'verified_at' => 'datetime',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    public function latestProof(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PaymentProof::class)->latestOfMany();
    }

    /** The bank-transfer verification queue. */
    #[Scope]
    protected function awaitingVerification(Builder $q): void
    {
        $q->where('driver', 'bank_transfer')
            ->where('status', PaymentAttemptStatus::AwaitingVerification);
    }
}
