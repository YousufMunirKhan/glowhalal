<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\UnknownPaymentDriverException;
use App\Models\Payment;
use App\Services\Orders\OrderStateMachine;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Architecture §6.8 — one webhook route, forever.
 *
 * Adding Easypaisa means writing a driver class and a config entry. No route,
 * no controller change, no checkout change. CSRF is excluded on this route
 * because the caller is a gateway server, not a browser session; the
 * replacement for CSRF is the driver's own signature verification, which is
 * why `verified: false` is a hard 400 and a log line rather than something
 * swallowed quietly.
 */
class PaymentCallbackController extends Controller
{
    public function __construct(
        private readonly PaymentManager $payments,
        private readonly OrderStateMachine $orders,
    ) {}

    public function __invoke(Request $request, string $driver)
    {
        try {
            $result = $this->payments->driver($driver)->handleCallback($request);
        } catch (UnknownPaymentDriverException|\LogicException $e) {
            Log::warning('Payment callback for a driver with no callback surface', [
                'driver' => $driver,
                'ip' => $request->ip(),
            ]);

            return response('UNSUPPORTED', 400);
        }

        if (! $result->verified) {
            Log::warning('Rejected payment callback', ['driver' => $driver, 'ip' => $request->ip()]);

            return response($result->acknowledgement, 400);
        }

        if ($result->outcome->successful && $result->payment) {
            $this->paymentSucceeded($result->payment);
        }

        return $result->redirectUrl
            ? redirect()->to($result->redirectUrl)
            : response($result->acknowledgement);
    }

    private function paymentSucceeded(Payment $payment): void
    {
        $order = $payment->order;

        if (! $order) {
            return;
        }

        $order->forceFill([
            'payment_status' => PaymentStatus::Paid,
            'paid_amount' => $payment->amount,
        ])->save();

        if ($order->status === OrderStatus::Pending) {
            $this->orders->transition($order, OrderStatus::Confirmed);
        }
    }
}
