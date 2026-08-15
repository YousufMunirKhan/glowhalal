<?php

namespace App\Http\Controllers;

use App\Contracts\Payments\PaymentAction;
use App\Contracts\Payments\PaymentContext;
use App\Contracts\Payments\PaymentDriver;
use App\Enums\PakistanProvince;
use App\Models\Cart;
use App\Services\Cart\CartCalculator;
use App\Services\Cart\CartManager;
use App\Services\Orders\CartAlreadyConvertedException;
use App\Services\Orders\CheckoutData;
use App\Services\Orders\CheckoutValidationException;
use App\Services\Orders\EmptyCartException;
use App\Services\Orders\PlaceOrderAction;
use App\Services\Payments\PaymentManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /** Session key holding the token of a Buy Now cart (see Livewire\Cart\AddToCart::buyNow). */
    public const DIRECT_SESSION_KEY = 'gh.checkout.direct';

    public function __invoke(Request $request, CartManager $carts): View|RedirectResponse
    {
        $direct = $request->boolean('direct');

        if ($direct && ! $request->session()->has(self::DIRECT_SESSION_KEY)) {
            return redirect()->route('cart.index');
        }

        if (! $direct) {
            $cart = $carts->current(createIfMissing: false);

            if (! $cart || $cart->items_count < 1) {
                return redirect()->route('cart.index');
            }
        }

        return view('checkout.index', ['direct' => $direct]);
    }

    /**
     * No-JavaScript fallback for the checkout form.
     *
     * The Livewire component intercepts the submit when JS is running, so this
     * only ever receives the NATIVE form post: JS off, JS failed to load on a
     * flaky connection, or a non-browser client driving the plain HTML. Before
     * this existed the form had no action/method and its inputs had no name
     * attributes — a submit without Livewire was a GET to the same URL carrying
     * no data at all (the checkout was simply unusable without JS).
     *
     * Field names, validation rules and messages mirror Livewire\Checkout
     * exactly, and the heavy lifting is the same PlaceOrderAction — this method
     * only translates HTTP in and redirects out. If the two ever drift, the
     * JS-off shopper sees different rules than the JS-on one, so change them
     * together.
     */
    public function store(Request $request, CartManager $carts): RedirectResponse
    {
        $direct = $request->boolean('direct');

        $cart = $this->cartFor($request, $carts, $direct);

        if (! $cart || $cart->items()->count() < 1) {
            return redirect()->route('cart.index');
        }

        $data = $request->validate([
            'customerName' => ['required', 'string', 'min:3', 'max:200'],
            'phone' => ['required', 'string', 'regex:/^(\+92|0)?3\d{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:180'],
            'addressLine1' => ['required', 'string', 'min:10', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', new \Illuminate\Validation\Rules\Enum(PakistanProvince::class)],
            'paymentMethod' => ['required', 'string'],
            'customerNote' => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'Enter a Pakistani mobile number, for example 0300 1234567.',
            'addressLine1.min' => 'Please give the courier a full address — house or shop number, street and area.',
            'customerName.min' => 'Please enter your full name.',
        ]);

        $totals = app(CartCalculator::class)->preview(
            $cart,
            $data['city'] ?: null,
            $data['province'] ?: null,
            $data['paymentMethod'],
        );

        $driver = collect(app(PaymentManager::class)->availableFor(new PaymentContext(
            subtotal: $totals->subtotal,
            grandTotal: $totals->grandTotal,
            city: $data['city'] ?: null,
            province: $data['province'] ?: null,
            user: auth()->user(),
            itemsCount: $totals->itemsCount,
        )))->first(fn (PaymentDriver $d) => $d->key() === $data['paymentMethod']);

        if (! $driver) {
            return back()->withInput()->withErrors([
                'paymentMethod' => 'That payment method is not available for this order.',
            ]);
        }

        try {
            $order = app(PlaceOrderAction::class)->execute($cart, new CheckoutData(
                customerName: $data['customerName'],
                phone: $this->normalisePhone($data['phone']),
                email: ($data['email'] ?? '') !== '' ? $data['email'] : null,
                addressLine1: $data['addressLine1'],
                city: $data['city'],
                province: PakistanProvince::from($data['province']),
                paymentMethod: $driver->key(),
                customerNote: ($data['customerNote'] ?? '') !== '' ? $data['customerNote'] : null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));
        } catch (CheckoutValidationException $e) {
            // Stock/price drift. The Livewire path auto-repairs the cart line
            // by line; here the shopper re-reads the summary and resubmits —
            // same information, one extra step, acceptable for a fallback.
            return back()->withInput()->withErrors([
                'checkout' => array_map(fn ($p) => $p->message, $e->problems),
            ]);
        } catch (EmptyCartException) {
            return redirect()->route('cart.index');
        } catch (CartAlreadyConvertedException $e) {
            $order = $e->cart->convertedOrder;

            return $order
                ? redirect()->route('orders.confirmation', $order->public_token)
                : redirect()->route('cart.index');
        }

        $initiation = $driver->initiate($order, "order-{$order->id}-{$driver->key()}");

        if ($initiation->action === PaymentAction::Failed) {
            return back()->withInput()->withErrors([
                'paymentMethod' => $initiation->failureMessage ?? 'That payment method failed to start.',
            ]);
        }

        // Mirror Livewire\Checkout::finaliseCart().
        if ($direct) {
            $request->session()->forget(self::DIRECT_SESSION_KEY);
        } else {
            $carts->forget();
        }

        if ($initiation->action === PaymentAction::Redirect && $initiation->redirectUrl) {
            return redirect()->to($initiation->redirectUrl);
        }

        return redirect()->route('orders.confirmation', $order->public_token);
    }

    /** Same cart resolution as Livewire\Checkout::cart(). */
    private function cartFor(Request $request, CartManager $carts, bool $direct): ?Cart
    {
        if ($direct) {
            $token = $request->session()->get(self::DIRECT_SESSION_KEY);

            return $token
                ? Cart::active()->where('token', $token)->with('items.variant.product')->first()
                : null;
        }

        return $carts->current(createIfMissing: false)?->load('items.variant.product');
    }

    /** Same normalisation as Livewire\Checkout::normalisePhone(). */
    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '92')) {
            $digits = substr($digits, 2);
        }

        $digits = ltrim($digits, '0');

        return '+92'.$digits;
    }
}
