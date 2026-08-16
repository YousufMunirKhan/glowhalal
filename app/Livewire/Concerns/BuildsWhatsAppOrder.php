<?php

namespace App\Livewire\Concerns;

use App\Models\Cart;
use App\Settings\StoreSettings;

/**
 * Builds a prefilled "order on WhatsApp" link from the current cart.
 *
 * Why this exists: WhatsApp is the channel this store has actually sold on —
 * every real customer so far ordered in a conversation, not through the cart.
 * The cart and checkout offered no way back to it, so at the exact moment a
 * first-time buyer hesitates, an unknown brand was asking them to fill a
 * six-field address form instead. This puts the proven channel back at the
 * point of maximum hesitation.
 *
 * The message carries the full basket and total so the owner can confirm an
 * order without asking the customer to retype anything. It contains NO
 * personal data — the customer supplies their own name and address in the
 * chat, on their own terms.
 */
trait BuildsWhatsAppOrder
{
    /**
     * Null when the cart is empty or the owner has not set a WhatsApp number
     * in Admin → Store settings (settings-only rule: a fake fallback number
     * must never reach a customer, so the button simply does not render).
     */
    public function whatsappOrderHref(?Cart $cart, ?string $grandTotal = null): ?string
    {
        if (! $cart || $cart->items->isEmpty()) {
            return null;
        }

        $lines = $cart->items
            ->map(function ($item) {
                $name = $item->name_snapshot ?: $item->variant?->product?->name ?: 'Item';
                $variant = $item->variant?->name;
                $price = $item->unit_price_amount?->format();

                return '• '.$item->quantity.' x '.$name
                    .($variant ? ' ('.$variant.')' : '')
                    .($price ? ' — '.$price : '');
            })
            ->implode("\n");

        $message = "Assalam o Alaikum, I want to order:\n".$lines;

        if ($grandTotal) {
            $message .= "\n\nTotal: ".$grandTotal;
        }

        $message .= "\n\nPlease confirm. (Cash on Delivery)";

        return rescue(
            fn () => app(StoreSettings::class)->whatsappLink($message),
            null,
            false,
        );
    }
}
