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
            'recovery_contacted_at' => 'datetime',
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

    /**
     * Carts worth a WhatsApp follow-up: a real phone was captured, there are
     * items, no order was placed, the owner has not already reached out, and the
     * shopper has gone quiet (older than 20 min, so we never nag someone who is
     * still typing) but not ancient (within 14 days). Powers Admin → Abandoned
     * Carts.
     */
    #[Scope]
    protected function recoverable(Builder $q): void
    {
        $q->whereIn('status', [CartStatus::Active, CartStatus::Abandoned])
            ->whereNull('converted_order_id')
            ->whereNull('recovery_contacted_at')
            ->whereNotNull('phone')
            ->where('items_count', '>', 0)
            ->whereBetween('last_activity_at', [now()->subDays(14), now()->subMinutes(20)]);
    }

    public function isEmpty(): bool
    {
        return $this->items_count === 0;
    }

    /** Digits-only E.164-ish number for a wa.me link, or null if unusable. */
    public function recoveryPhoneDigits(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $this->phone) ?? '';

        if (str_starts_with($digits, '92')) {
            // already country-coded
        } elseif (str_starts_with($digits, '0')) {
            $digits = '92'.ltrim($digits, '0');
        } elseif (str_starts_with($digits, '3')) {
            $digits = '92'.$digits;
        }

        // A Pakistani mobile in international form is 12 digits (92 + 3xxxxxxxxx).
        return strlen($digits) === 12 && str_starts_with($digits, '923') ? $digits : null;
    }

    /** Short "2× Lookman-e-Hayat 50 ml" summary of what is in the cart. */
    public function itemsSummary(): string
    {
        return $this->items
            ->map(fn (CartItem $i) => ($i->quantity > 1 ? $i->quantity.'× ' : '')
                .($i->variant?->product?->name ?? 'item'))
            ->implode(', ');
    }

    /**
     * A ready-to-send wa.me link with a friendly, honest cart-recovery message
     * pre-filled — the owner taps it (on WhatsApp Web/phone) and sends. Returns
     * null when the phone is unusable.
     */
    public function whatsappRecoveryUrl(): ?string
    {
        $phone = $this->recoveryPhoneDigits();

        if (! $phone) {
            return null;
        }

        $name = trim((string) $this->customer_name);
        $hi = $name !== '' ? "Assalam o Alaikum {$name}!" : 'Assalam o Alaikum!';
        $items = $this->itemsSummary() ?: 'your order';
        $total = $this->grand_total_amount?->format();

        $message = "{$hi} Aap ne Glow Halal par {$items}"
            .($total ? " ({$total})" : '')
            .' cart mein rakha tha lekin order mukammal nahi hua. '
            .'Cash on Delivery par ghar baithe bhijwa dein? Bas confirm kar dein. 🌿';

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }
}
