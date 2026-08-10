<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Services\Shipping\ShippingCalculator;
use App\Support\Money;

/**
 * Architecture §5.6. Every mutation ends by recalculating and persisting the
 * denormalised totals, so any component can render a cart from one row without
 * a join — which is what keeps the header mini-cart cheap on every page.
 *
 * Tax is modelled but zero at launch: Pakistani retail cosmetics prices are
 * customarily quoted GST-inclusive, so a separate tax line would confuse
 * customers and inflate the visible total. The hook exists because retrofitting
 * a tax column into an orders table with history is genuinely painful.
 */
final class CartCalculator
{
    public function __construct(private readonly ShippingCalculator $shipping) {}

    public function recalculate(
        Cart $cart,
        ?string $city = null,
        ?string $province = null,
        ?string $paymentMethod = null,
    ): CartTotals {
        $cart->load('items');

        $subtotal = Money::zero();

        foreach ($cart->items as $item) {
            $line = $item->unit_price_amount->times($item->quantity);

            if ($item->line_total_amount?->minorUnits !== $line->minorUnits) {
                $item->updateQuietly(['line_total_amount' => $line->minorUnits]);
            }

            $subtotal = $subtotal->plus($line);
        }

        // Coupons are modelled in the schema but no coupon UI ships in this
        // phase; whatever an admin has already applied is respected.
        $discount = $cart->discount_amount ?? Money::zero();

        if ($discount->minorUnits > $subtotal->minorUnits) {
            $discount = $subtotal;
        }

        $net = $subtotal->minus($discount);

        $quote = $subtotal->isZero()
            ? null
            : $this->shipping->quote($net, $city, $province);

        $shipping = $quote?->amount ?? Money::zero();
        $codFee = $paymentMethod === 'cod' ? ($quote?->codSurcharge ?? Money::zero()) : Money::zero();
        $tax = Money::zero();

        $grand = $net->plus($shipping)->plus($codFee)->plus($tax);

        $cart->forceFill([
            'subtotal_amount' => $subtotal->minorUnits,
            'discount_amount' => $discount->minorUnits,
            'shipping_amount' => $shipping->minorUnits,
            'tax_amount' => $tax->minorUnits,
            'grand_total_amount' => $grand->minorUnits,
            'items_count' => $cart->items->sum('quantity'),
            'last_activity_at' => now(),
        ])->saveQuietly();

        return new CartTotals(
            subtotal: $subtotal,
            discount: $discount,
            shipping: $shipping,
            codFee: $codFee,
            tax: $tax,
            grandTotal: $grand,
            itemsCount: (int) $cart->items->sum('quantity'),
            shippingQuote: $quote,
        );
    }

    /** Derive totals without persisting — used for "what if" quotes at checkout. */
    public function preview(Cart $cart, ?string $city, ?string $province, ?string $paymentMethod): CartTotals
    {
        $cart->loadMissing('items');

        $subtotal = Money::zero();

        foreach ($cart->items as $item) {
            $subtotal = $subtotal->plus($item->unit_price_amount->times($item->quantity));
        }

        $discount = $cart->discount_amount ?? Money::zero();

        if ($discount->minorUnits > $subtotal->minorUnits) {
            $discount = $subtotal;
        }

        $net = $subtotal->minus($discount);
        $quote = $subtotal->isZero() ? null : $this->shipping->quote($net, $city, $province);

        $shipping = $quote?->amount ?? Money::zero();
        $codFee = $paymentMethod === 'cod' ? ($quote?->codSurcharge ?? Money::zero()) : Money::zero();

        return new CartTotals(
            subtotal: $subtotal,
            discount: $discount,
            shipping: $shipping,
            codFee: $codFee,
            tax: Money::zero(),
            grandTotal: $net->plus($shipping)->plus($codFee),
            itemsCount: (int) $cart->items->sum('quantity'),
            shippingQuote: $quote,
        );
    }
}
