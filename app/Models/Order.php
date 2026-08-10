<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentStatus;
use App\Observers\OrderObserver;
use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(OrderObserver::class)]
class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal_amount' => MoneyCast::class,
            'discount_amount' => MoneyCast::class,
            'shipping_amount' => MoneyCast::class,
            'cod_fee_amount' => MoneyCast::class,
            'tax_amount' => MoneyCast::class,
            'grand_total_amount' => MoneyCast::class,
            'paid_amount' => MoneyCast::class,
            'refunded_amount' => MoneyCast::class,
            'utm' => 'array',
            'placed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'shipping');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(OrderAddress::class)->where('type', 'billing');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function reservations(): MorphMany
    {
        return $this->morphMany(StockReservation::class, 'reservable');
    }

    public function successfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->where('status', PaymentAttemptStatus::Paid)->latestOfMany();
    }

    #[Scope]
    protected function placed(Builder $q): void
    {
        $q->whereNotNull('placed_at');
    }

    #[Scope]
    protected function awaitingPayment(Builder $q): void
    {
        $q->where('payment_status', PaymentStatus::AwaitingVerification);
    }

    #[Scope]
    protected function open(Builder $q): void
    {
        $q->whereIn('status', [OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Shipped]);
    }

    #[Scope]
    protected function forGuestToken(Builder $q, string $token): void
    {
        $q->where('public_token', $token);
    }

    protected function balanceDue(): Attribute
    {
        return Attribute::get(fn (): Money => ($this->grand_total_amount ?? Money::zero())
            ->minus($this->paid_amount ?? Money::zero()));
    }

    protected function isFullyPaid(): Attribute
    {
        return Attribute::get(fn (): bool => ($this->paid_amount?->minorUnits ?? 0)
            >= ($this->grand_total_amount?->minorUnits ?? 0));
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
