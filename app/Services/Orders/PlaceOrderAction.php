<?php

namespace App\Services\Orders;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\ReservationStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\Cart\CartCalculator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Architecture §5.7 — the one place an order comes into existence.
 *
 * Two customers buying the last unit simultaneously must not both succeed, and
 * the browser's idea of the total is never an input to what gets charged.
 *
 * Stock is reserved here rather than at add-to-cart: reserving when an item
 * enters a bag lets one abandoned tab hold the last unit hostage. The
 * reservation raises `quantity_reserved`, which drops the generated
 * `quantity_available` immediately — so the second shopper is refused — while
 * `quantity_on_hand` only moves when an admin confirms the order.
 */
final class PlaceOrderAction
{
    public function __construct(private readonly CartCalculator $calculator) {}

    public function execute(Cart $cart, CheckoutData $data): Order
    {
        return DB::transaction(function () use ($cart, $data) {

            // 1. Lock the cart itself — a double-clicked "Place order" blocks here,
            //    then finds the cart already converted and throws.
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

            if ($cart->status !== CartStatus::Active) {
                throw new CartAlreadyConvertedException($cart);
            }

            $items = $cart->items()->with(['variant.product.halalProfile'])->get();

            if ($items->isEmpty()) {
                throw new EmptyCartException;
            }

            // 2. Lock every inventory row, ordered by id — a consistent lock order
            //    is what prevents the classic two-variant deadlock.
            $variantIds = $items->pluck('product_variant_id')->unique()->sort()->values();

            $inventories = InventoryItem::whereIn('product_variant_id', $variantIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_variant_id');

            // 3. Validate availability AND price against live data. Collect every
            //    problem — telling a customer about one of three is three trips.
            $problems = [];

            foreach ($items as $item) {
                $variant = $item->variant;

                if (! $variant?->is_active || $variant->product?->status !== ProductStatus::Active) {
                    $problems[] = CheckoutProblem::unavailable($item);

                    continue;
                }

                if ($variant->track_inventory && ! $variant->allow_backorder) {
                    $available = (int) ($inventories[$variant->id]->quantity_available ?? 0);

                    if ($available < $item->quantity) {
                        $problems[] = CheckoutProblem::insufficientStock($item, max(0, $available));
                    }
                }

                if ($variant->price_amount?->minorUnits !== $item->unit_price_amount?->minorUnits) {
                    $problems[] = CheckoutProblem::priceChanged($item, $variant->price_amount);
                }
            }

            if ($problems !== []) {
                throw new CheckoutValidationException($problems);   // rolls the transaction back
            }

            // 4. Recalculate totals server-side, from the destination the customer
            //    actually typed. Never trust a total from the browser.
            $totals = $this->calculator->preview(
                $cart,
                $data->city,
                $data->province->value,
                $data->paymentMethod,
            );

            // 5. Create the order and snapshot everything that must survive a
            //    later catalogue edit.
            $order = $this->buildOrder($cart, $data, $totals, $items);

            // 5b. Cash on Delivery writes its Payment row INSIDE this transaction.
            //     Checkout::placeOrder calls $driver->initiate() only AFTER this
            //     transaction commits, so a crash in that window used to leave a
            //     placed COD order with no Payment row. Creating it here makes the
            //     Payment atomic with the order. The idempotency key matches the
            //     one Checkout later passes ("order-{id}-cod"), so the driver's
            //     firstOrCreate() becomes a harmless no-op instead of a duplicate.
            //     Gateways (e.g. bank_transfer) are untouched — they still create
            //     their Payment row in initiate() post-commit, as before.
            if ($data->paymentMethod === 'cod') {
                $order->payments()->firstOrCreate(
                    ['idempotency_key' => "order-{$order->id}-cod"],
                    [
                        'driver' => 'cod',
                        'status' => PaymentAttemptStatus::Pending,
                        'currency' => $order->currency,
                        'amount' => $order->grand_total_amount->minorUnits,
                    ],
                );
            }

            // 6. Reserve stock and write the ledger, still inside the lock.
            foreach ($items as $item) {
                $this->reserve($order, $item, $inventories[$item->product_variant_id] ?? null);
            }

            // 7. Close the cart.
            $cart->forceFill([
                'status' => CartStatus::Converted,
                'converted_order_id' => $order->id,
                'email' => $data->email,
            ])->saveQuietly();

            return $order;
        }, attempts: 3);
    }

    /** @param \Illuminate\Support\Collection<int, CartItem> $items */
    private function buildOrder(Cart $cart, CheckoutData $data, \App\Services\Cart\CartTotals $totals, $items): Order
    {
        $quote = $totals->shippingQuote;

        $order = $this->insertWithUniqueOrderNumber([
            'user_id' => auth()->id(),
            'cart_id' => $cart->id,
            // orders.email is NOT NULL at the schema level; a customer who skips
            // the (now optional) email field is stored as an empty string rather
            // than a fabricated address. carts.email below stays truly nullable.
            'email' => $data->email ?? '',
            'phone' => $data->phone,
            'customer_name' => $data->customerName,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'currency' => 'PKR',
            'subtotal_amount' => $totals->subtotal,
            'discount_amount' => $totals->discount,
            'shipping_amount' => $totals->shipping,
            'cod_fee_amount' => $totals->codFee,
            'tax_amount' => $totals->tax,
            'grand_total_amount' => $totals->grandTotal,
            'paid_amount' => 0,
            'refunded_amount' => 0,
            'coupon_id' => $cart->coupon_id,
            'coupon_code' => $cart->coupon_code,
            'payment_method' => $data->paymentMethod,
            'shipping_rate_id' => $quote?->rate?->id,
            'shipping_method_name' => $quote?->label,
            'customer_note' => $data->customerNote,
            'placed_at' => now(),
            'ip_address' => $data->ipAddress,
            'user_agent' => $data->userAgent ? substr($data->userAgent, 0, 500) : null,
        ]);

        foreach ($items as $item) {
            $variant = $item->variant;
            $product = $variant?->product;
            $line = $item->unit_price_amount->times($item->quantity);

            $order->items()->create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'sku' => $variant?->sku ?? 'UNKNOWN',
                'product_name' => $product?->name ?? $item->name_snapshot,
                'variant_name' => $variant?->name,
                'product_slug' => $product?->slug,
                'options_snapshot' => $item->options_snapshot,
                'image_path_snapshot' => $item->image_path_snapshot,
                'halal_snapshot' => $this->halalSnapshot($product),
                'quantity' => $item->quantity,
                'unit_price_amount' => $item->unit_price_amount,
                'line_subtotal_amount' => $line,
                'line_discount_amount' => 0,
                'line_tax_amount' => 0,
                'line_total_amount' => $line,
                'unit_cost_amount' => $variant?->cost_amount,
            ]);
        }

        $order->addresses()->create([
            'type' => 'shipping',
            'first_name' => $data->firstName(),
            'last_name' => $data->lastName(),
            'phone' => $data->phone,
            'line_1' => $data->addressLine1,
            'city' => $data->city,
            'province' => $data->province,
            'country_code' => 'PK',
        ]);

        return $order->refresh();
    }

    /**
     * Insert the order header, defending against the order-number race.
     *
     * OrderObserver::nextOrderNumber() derives the next suffix from
     * MAX(order_number), so two checkouts of different carts that resolve the
     * same maximum generate the same number and the second INSERT trips
     * `orders_order_number_unique`. That surfaces as a
     * UniqueConstraintViolationException, which DB::transaction(attempts: 3)
     * does NOT retry (it only retries deadlocks) — the customer would get a 500
     * at "Place order".
     *
     * On MySQL/InnoDB a duplicate-key error rolls back only the failed
     * statement, not the surrounding transaction, so we can catch it, recompute
     * a fresh number and retry the INSERT while keeping the cart and inventory
     * locks acquired earlier. The loser of a collision blocks on the unique
     * index until the winner commits, so by the time we recompute, MAX() already
     * reflects the committed number and the next value is free — the two
     * colliding orders resolve to distinct, correctly-priced rows.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function insertWithUniqueOrderNumber(array $attributes): Order
    {
        $maxAttempts = 5;

        for ($attempt = 1; ; $attempt++) {
            // Regenerate every attempt so a collision never retries the same
            // number. Setting it explicitly wins over the observer's `??=`.
            $attributes['order_number'] = OrderObserver::nextOrderNumber();

            try {
                return Order::create($attributes);
            } catch (UniqueConstraintViolationException $e) {
                // Only the order-number clash is recoverable here. Any other
                // unique violation is a real fault and must surface untouched.
                if ($attempt >= $maxAttempts || ! $this->isOrderNumberCollision($e)) {
                    throw $e;
                }
            }
        }
    }

    private function isOrderNumberCollision(UniqueConstraintViolationException $e): bool
    {
        return str_contains($e->getMessage(), 'orders_order_number_unique')
            || str_contains($e->getMessage(), 'order_number');
    }

    /**
     * A snapshot of what we believed about this formulation at the moment of
     * sale. Deliberately records no third-party approval: Glow Halal holds no
     * accreditation, and `is_self_declared` says exactly that.
     */
    private function halalSnapshot(?\App\Models\Product $product): ?array
    {
        $profile = $product?->halalProfile;

        if (! $profile) {
            return null;
        }

        return [
            'captured_at' => now()->toIso8601String(),
            'overall_status' => $profile->overall_status?->value,
            'alcohol_status' => $profile->alcohol_status?->value,
            'is_self_declared' => true,
        ];
    }

    private function reserve(Order $order, CartItem $item, ?InventoryItem $inventory): void
    {
        if (! $inventory) {
            return;
        }

        $inventory->forceFill([
            'quantity_reserved' => $inventory->quantity_reserved + $item->quantity,
        ])->save();

        $order->reservations()->create([
            'product_variant_id' => $item->product_variant_id,
            'inventory_item_id' => $inventory->id,
            'quantity' => $item->quantity,
            'status' => ReservationStatus::Held,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
