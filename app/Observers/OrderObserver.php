<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $order->public_token ??= (string) \Illuminate\Support\Str::ulid();
        $order->order_number ??= self::nextOrderNumber();
    }

    /** Stamps the `*_at` columns so nobody has to remember to. */
    public function saving(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        $column = match ($order->status) {
            OrderStatus::Confirmed => 'confirmed_at',
            OrderStatus::Shipped => 'shipped_at',
            OrderStatus::Delivered => 'delivered_at',
            OrderStatus::Cancelled => 'cancelled_at',
            OrderStatus::Refunded => 'refunded_at',
            OrderStatus::Pending => null,
        };

        if ($column !== null && $order->{$column} === null) {
            $order->{$column} = now();
        }
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged(['status', 'payment_status'])) {
            return;
        }

        $order->statusHistories()->create([
            'from_status' => $order->getOriginal('status'),
            'to_status' => $order->status?->value,
            'from_payment_status' => $order->getOriginal('payment_status'),
            'to_payment_status' => $order->payment_status?->value,
            'user_id' => auth()->id(),
            'note' => $order->admin_note,
            'customer_notified' => false,
        ]);
    }

    public static function nextOrderNumber(): string
    {
        $prefix = 'GH-'.now()->format('ymd').'-';

        $lastSuffix = (int) str(
            Order::withTrashed()->where('order_number', 'like', $prefix.'%')->max('order_number') ?? ''
        )->afterLast('-')->toString();

        return $prefix.str_pad((string) ($lastSuffix + 1), 4, '0', STR_PAD_LEFT);
    }
}
