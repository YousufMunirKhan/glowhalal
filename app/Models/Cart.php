<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\CartStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => CartStatus::class,
            'subtotal_amount' => MoneyCast::class,
            'discount_amount' => MoneyCast::class,
            'shipping_amount' => MoneyCast::class,
            'tax_amount' => MoneyCast::class,
            'grand_total_amount' => MoneyCast::class,
            'last_activity_at' => 'datetime',
            'expires_at' => 'datetime',
            'abandoned_email_sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            $cart->token ??= (string) \Illuminate\Support\Str::ulid();
            $cart->expires_at ??= now()->addDays(30);
            $cart->last_activity_at ??= now();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    #[Scope]
    protected function active(Builder $q): void
    {
        $q->where('status', CartStatus::Active);
    }

    #[Scope]
    protected function abandonable(Builder $q): void
    {
        $q->where('status', CartStatus::Active)
            ->whereNotNull('email')
            ->whereNull('abandoned_email_sent_at')
            ->where('items_count', '>', 0)
            ->whereBetween('last_activity_at', [now()->subDays(7), now()->subHours(4)]);
    }

    public function isEmpty(): bool
    {
        return $this->items_count === 0;
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
