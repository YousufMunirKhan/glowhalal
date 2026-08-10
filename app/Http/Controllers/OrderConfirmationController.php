<?php

namespace App\Http\Controllers;

use App\Contracts\Payments\PaymentAction;
use App\Models\Order;
use App\Services\Payments\PaymentManager;
use Illuminate\Contracts\View\View;

/**
 * Guest-reachable order confirmation, keyed on the order's opaque ULID
 * `public_token` — no account required, which is non-negotiable for the
 * Pakistani COD market.
 */
class OrderConfirmationController extends Controller
{
    public function __invoke(string $token, PaymentManager $payments): View
    {
        $order = Order::query()
            ->forGuestToken($token)
            ->with(['items', 'shippingAddress', 'payments.bankAccount'])
            ->firstOrFail();

        $payment = $order->payments()->latest('id')->with('bankAccount')->first();

        $instructions = null;

        if ($payment && $payment->driver === 'bank_transfer' && $payment->bankAccount) {
            $instructions = [
                'account' => $payment->bankAccount,
                'amount' => $order->grand_total_amount,
                'reference' => $order->order_number,
                'expiresAt' => $payment->expires_at,
            ];
        }

        return view('checkout.confirmation', [
            'order' => $order,
            'payment' => $payment,
            'instructions' => $instructions,
            'action' => $instructions ? PaymentAction::Instructions : PaymentAction::Completed,
        ]);
    }
}
