<?php

namespace App\Livewire;

use App\Contracts\Payments\PaymentAction;
use App\Contracts\Payments\PaymentContext;
use App\Contracts\Payments\PaymentDriver;
use App\Enums\PakistanProvince;
use App\Http\Controllers\CheckoutController;
use App\Models\Cart;
use App\Services\Cart\CartCalculator;
use App\Services\Cart\CartManager;
use App\Services\Cart\CartService;
use App\Services\Cart\CartTotals;
use App\Services\Orders\CartAlreadyConvertedException;
use App\Services\Orders\CheckoutData;
use App\Services\Orders\CheckoutProblem;
use App\Services\Orders\CheckoutValidationException;
use App\Services\Orders\EmptyCartException;
use App\Services\Payments\PaymentManager;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Single-page checkout. Six fields, Cash on Delivery pre-selected, no account
 * required.
 *
 * What is deliberately absent, per the research: CNIC (a hard stop for most
 * Pakistani shoppers, and PII with no lawful purpose here), postal code (no
 * local courier uses it), account creation, and a separate billing address.
 * Every field removed is a measurable lift in completion on a COD store.
 *
 * Class-based rather than single-file on purpose (architecture §5.1): this
 * component carries injected services and the heaviest logic in the
 * application, and that belongs in a normal, testable PHP class.
 */
class Checkout extends Component
{
    #[Locked]
    public bool $direct = false;

    // --- the six fields -----------------------------------------------------
    public string $customerName = '';

    public string $phone = '';

    public string $email = '';

    public string $addressLine1 = '';

    public string $city = '';

    public string $province = '';

    // --- everything else ----------------------------------------------------
    public string $paymentMethod = 'cod';

    public string $customerNote = '';

    public bool $showNote = false;

    public bool $placing = false;

    /** @var array<int, string> */
    public array $problems = [];

    public const CITIES = [
        'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan',
        'Peshawar', 'Quetta', 'Gujranwala', 'Sialkot', 'Hyderabad', 'Bahawalpur',
        'Sargodha', 'Sukkur', 'Abbottabad', 'Mardan', 'Gujrat', 'Sahiwal',
    ];

    public function mount(bool $direct = false): void
    {
        $this->direct = $direct;

        if ($user = auth()->user()) {
            $this->customerName = (string) ($user->name ?? '');
            $this->email = (string) ($user->email ?? '');
            $this->phone = (string) ($user->phone ?? '');

            // Reuse the address from the customer's most recent order, so a
            // returning signed-in customer does not retype it.
            $last = $user->orders()->latest()->with('shippingAddress')->first()?->shippingAddress;

            if ($last) {
                $this->addressLine1 = trim(implode(', ', array_filter([
                    $last->line_1, $last->line_2, $last->area,
                ])));
                $this->city = (string) ($last->city ?? '');

                // province may be cast to the PakistanProvince enum — take its value.
                $prov = $last->province;
                $this->province = $prov instanceof \BackedEnum ? $prov->value : (string) ($prov ?? '');
            }
        }
    }

    #[Computed(persist: false)]
    public function cart(): ?Cart
    {
        if ($this->direct) {
            $token = session(CheckoutController::DIRECT_SESSION_KEY);

            return $token
                ? Cart::active()->where('token', $token)->with('items.variant.product')->first()
                : null;
        }

        return app(CartManager::class)
            ->current(createIfMissing: false)
            ?->load('items.variant.product');
    }

    #[Computed(persist: false)]
    public function totals(): CartTotals
    {
        $cart = $this->cart;

        return $cart
            ? app(CartCalculator::class)->preview(
                $cart,
                $this->city ?: null,
                $this->province ?: null,
                $this->paymentMethod,
            )
            : CartTotals::empty();
    }

    /** @return array<int, PaymentDriver> */
    #[Computed(persist: false)]
    public function paymentDrivers(): array
    {
        $totals = $this->totals;

        return app(PaymentManager::class)->availableFor(new PaymentContext(
            subtotal: $totals->subtotal,
            grandTotal: $totals->grandTotal,
            city: $this->city ?: null,
            province: $this->province ?: null,
            user: auth()->user(),
            itemsCount: $totals->itemsCount,
        ));
    }

    public function provinces(): array
    {
        return PakistanProvince::options();
    }

    /** Recompute the delivery charge the moment the destination is known. */
    public function updatedCity(): void
    {
        $this->guessProvince();
        unset($this->totals, $this->paymentDrivers);
    }

    public function updatedProvince(): void
    {
        unset($this->totals, $this->paymentDrivers);
    }

    public function updatedPaymentMethod(): void
    {
        unset($this->totals);
    }

    /**
     * Fills the province from the city so the shopper types one thing, not two.
     * Only for the handful of cities where it is unambiguous — a wrong guess
     * routes a parcel to the wrong zone, so silence beats a bad guess.
     */
    private function guessProvince(): void
    {
        if ($this->province !== '') {
            return;
        }

        $map = [
            'karachi' => 'sindh', 'hyderabad' => 'sindh', 'sukkur' => 'sindh',
            'lahore' => 'punjab', 'faisalabad' => 'punjab', 'multan' => 'punjab',
            'rawalpindi' => 'punjab', 'gujranwala' => 'punjab', 'sialkot' => 'punjab',
            'bahawalpur' => 'punjab', 'sargodha' => 'punjab', 'gujrat' => 'punjab',
            'sahiwal' => 'punjab',
            'islamabad' => 'islamabad',
            'peshawar' => 'kpk', 'abbottabad' => 'kpk', 'mardan' => 'kpk',
            'quetta' => 'balochistan',
        ];

        $this->province = $map[mb_strtolower(trim($this->city))] ?? '';
    }

    protected function rules(): array
    {
        return [
            'customerName' => ['required', 'string', 'min:3', 'max:200'],
            'phone' => ['required', 'string', 'regex:/^(\+92|0)?3\d{9}$/'],
            // Optional: this is a COD store confirmed by SMS, so email is only a
            // receipt. A blank value is skipped (email/max are non-implicit rules
            // and are not run on an empty string); a filled value must be valid.
            'email' => ['nullable', 'email:rfc', 'max:180'],
            'addressLine1' => ['required', 'string', 'min:10', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', new \Illuminate\Validation\Rules\Enum(PakistanProvince::class)],
            'paymentMethod' => ['required', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => 'Enter a Pakistani mobile number, for example 0300 1234567.',
            'addressLine1.min' => 'Please give the courier a full address — house or shop number, street and area.',
            'customerName.min' => 'Please enter your full name.',
        ];
    }

    public function placeOrder()
    {
        $this->problems = [];

        $cart = $this->cart;

        if (! $cart || $cart->items()->count() < 1) {
            return $this->redirect(route('cart.index'), navigate: false);
        }

        $data = $this->validate();

        $driver = collect($this->paymentDrivers)
            ->first(fn (PaymentDriver $d) => $d->key() === $this->paymentMethod);

        if (! $driver) {
            $this->addError('paymentMethod', 'That payment method is not available for this order.');

            return null;
        }

        try {
            $order = app(\App\Services\Orders\PlaceOrderAction::class)->execute($cart, new CheckoutData(
                customerName: $data['customerName'],
                phone: $this->normalisePhone($data['phone']),
                // Blank email becomes null so nothing downstream stores '' as an
                // address; orders.email is coerced back to '' at the NOT NULL column.
                email: ($data['email'] ?? '') !== '' ? $data['email'] : null,
                addressLine1: $data['addressLine1'],
                city: $data['city'],
                province: PakistanProvince::from($data['province']),
                paymentMethod: $driver->key(),
                customerNote: $this->customerNote !== '' ? $this->customerNote : null,
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            ));
        } catch (CheckoutValidationException $e) {
            $this->applyProblems($e->problems);

            return null;
        } catch (EmptyCartException) {
            return $this->redirect(route('cart.index'), navigate: false);
        } catch (CartAlreadyConvertedException $e) {
            $order = $e->cart->convertedOrder;

            return $order
                ? $this->redirect(route('orders.confirmation', $order->public_token), navigate: false)
                : $this->redirect(route('cart.index'), navigate: false);
        }

        // The driver answers one question: am I done, do I redirect, or do I
        // show instructions? Checkout branches on the answer, never on the
        // driver — which is why adding JazzCash later touches nothing here.
        $initiation = $driver->initiate($order, "order-{$order->id}-{$driver->key()}");

        if ($initiation->action === PaymentAction::Failed) {
            $this->addError('paymentMethod', $initiation->failureMessage ?? 'That payment method failed to start.');

            return null;
        }

        $this->finaliseCart();

        if ($initiation->action === PaymentAction::Redirect && $initiation->redirectUrl) {
            return $this->redirect($initiation->redirectUrl, navigate: false);
        }

        return $this->redirect(route('orders.confirmation', $order->public_token), navigate: false);
    }

    /** @param array<int, CheckoutProblem> $problems */
    private function applyProblems(array $problems): void
    {
        $cart = $this->cart;
        $service = app(CartService::class);

        foreach ($problems as $problem) {
            $this->problems[] = $problem->message;

            $item = $cart?->items()->whereKey($problem->cartItemId)->first();

            if (! $item) {
                continue;
            }

            match (true) {
                $problem->availableQuantity === 0 => $service->remove($item),
                $problem->availableQuantity !== null => $service->updateQuantity($item, $problem->availableQuantity),
                // A price change is not fixed silently: the customer sees the
                // new price and re-confirms. Re-snapshotting behind their back
                // is how a store charges more than the page said.
                default => $item->forceFill([
                    'unit_price_amount' => $item->variant?->price_amount ?? $item->unit_price_amount,
                ])->save(),
            };
        }

        unset($this->cart, $this->totals, $this->paymentDrivers);
    }

    private function finaliseCart(): void
    {
        if ($this->direct) {
            session()->forget(CheckoutController::DIRECT_SESSION_KEY);

            return;
        }

        // The session basket has become an order; the next visit starts clean.
        app(CartManager::class)->forget();
    }

    private function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '92')) {
            $digits = substr($digits, 2);
        }

        $digits = ltrim($digits, '0');

        return '+92'.$digits;
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
