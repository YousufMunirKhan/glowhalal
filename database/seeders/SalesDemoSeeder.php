<?php

namespace Database\Seeders;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Enums\PakistanProvince;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingRateType;
use App\Models\BankAccount;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Support\Money;
use Illuminate\Database\Seeder;

class SalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->bankAccounts();
        $this->shipping();
        $this->coupons();
        $this->orders();
    }

    private function bankAccounts(): void
    {
        BankAccount::firstOrCreate(
            ['account_number' => '01234567890123'],
            [
                'bank_name' => 'Meezan Bank',
                'account_title' => 'Glow Halal (Pvt) Ltd',
                'iban' => 'PK00MEZN0001234567890123',
                'branch_name' => 'Clifton, Karachi',
                'instructions' => 'Transfer the exact order total and upload the receipt. Orders are released once the transfer is verified, usually within one working day.',
                'is_active' => true,
                'position' => 0,
            ],
        );
    }

    private function shipping(): void
    {
        $metro = ShippingZone::firstOrCreate(
            ['name' => 'Major cities'],
            [
                'provinces' => ['sindh', 'punjab', 'islamabad'],
                'cities' => ['Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad'],
                'is_active' => true,
                'position' => 0,
            ],
        );

        $rest = ShippingZone::firstOrCreate(
            ['name' => 'Rest of Pakistan'],
            ['is_fallback' => true, 'is_active' => true, 'position' => 10],
        );

        ShippingRate::firstOrCreate(
            ['shipping_zone_id' => $metro->id, 'name' => 'Standard courier'],
            [
                'type' => ShippingRateType::FreeOver,
                'amount' => Money::fromRupees(250),
                'free_over_subtotal_amount' => Money::fromRupees(3500),
                'cod_surcharge_amount' => Money::fromRupees(100),
                'min_delivery_days' => 2,
                'max_delivery_days' => 4,
                'is_active' => true,
            ],
        );

        ShippingRate::firstOrCreate(
            ['shipping_zone_id' => $rest->id, 'name' => 'Nationwide courier'],
            [
                'type' => ShippingRateType::Flat,
                'amount' => Money::fromRupees(350),
                'cod_surcharge_amount' => Money::fromRupees(150),
                'min_delivery_days' => 4,
                'max_delivery_days' => 7,
                'is_active' => true,
            ],
        );
    }

    private function coupons(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'name' => 'Welcome 10% off',
                'description' => 'First order only.',
                'type' => CouponType::Percentage,
                'percentage_value' => 1000,   // basis points
                'max_discount_amount' => Money::fromRupees(1000),
                'min_subtotal_amount' => Money::fromRupees(2000),
                'applies_to' => CouponScope::All,
                'first_order_only' => true,
                'usage_limit_per_customer' => 1,
                'is_active' => true,
                'ends_at' => now()->addMonths(3),
            ],
        );

        Coupon::firstOrCreate(
            ['code' => 'FREESHIP'],
            [
                'name' => 'Free shipping over Rs 2,500',
                'type' => CouponType::FreeShipping,
                'min_subtotal_amount' => Money::fromRupees(2500),
                'applies_to' => CouponScope::All,
                'is_active' => true,
            ],
        );
    }

    private function orders(): void
    {
        if (Order::exists()) {
            return;
        }

        $variants = ProductVariant::with('product')->get();

        if ($variants->isEmpty()) {
            return;
        }

        $specs = [
            [
                'customer_name' => 'Ayesha Siddiqui',
                'email' => 'ayesha.siddiqui@example.com',
                'phone' => '+923001234567',
                'city' => 'Karachi',
                'province' => PakistanProvince::Sindh,
                'method' => 'cod',
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'lines' => 2,
                'daysAgo' => 0,
            ],
            [
                'customer_name' => 'Fatima Khan',
                'email' => 'fatima.khan@example.com',
                'phone' => '+923219876543',
                'city' => 'Lahore',
                'province' => PakistanProvince::Punjab,
                'method' => 'bank_transfer',
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::AwaitingVerification,
                'lines' => 1,
                'daysAgo' => 1,
            ],
            [
                'customer_name' => 'Zainab Malik',
                'email' => 'zainab.malik@example.com',
                'phone' => '+923335554444',
                'city' => 'Islamabad',
                'province' => PakistanProvince::Islamabad,
                'method' => 'cod',
                'status' => OrderStatus::Shipped,
                'payment_status' => PaymentStatus::Pending,
                'lines' => 3,
                'daysAgo' => 4,
            ],
            [
                'customer_name' => 'Hira Ahmed',
                'email' => 'hira.ahmed@example.com',
                'phone' => '+923047778888',
                'city' => 'Multan',
                'province' => PakistanProvince::Punjab,
                'method' => 'bank_transfer',
                'status' => OrderStatus::Delivered,
                'payment_status' => PaymentStatus::Paid,
                'lines' => 2,
                'daysAgo' => 9,
            ],
        ];

        foreach ($specs as $spec) {
            $placedAt = now()->subDays($spec['daysAgo'])->subHours(random_int(1, 8));
            $chosen = $variants->random(min($spec['lines'], $variants->count()));

            $subtotal = 0;
            $lines = [];

            foreach ($chosen as $variant) {
                $qty = random_int(1, 2);
                $unit = $variant->price_amount->minorUnits;
                $lineTotal = $unit * $qty;
                $subtotal += $lineTotal;

                $lines[] = compact('variant', 'qty', 'unit', 'lineTotal');
            }

            $shipping = $subtotal >= 350000 ? 0 : 25000;
            $codFee = $spec['method'] === 'cod' ? 10000 : 0;
            $grand = $subtotal + $shipping + $codFee;

            $order = Order::create([
                'customer_name' => $spec['customer_name'],
                'email' => $spec['email'],
                'phone' => $spec['phone'],
                'status' => $spec['status'],
                'payment_status' => $spec['payment_status'],
                'currency' => 'PKR',
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'shipping_amount' => $shipping,
                'cod_fee_amount' => $codFee,
                'tax_amount' => 0,
                'grand_total_amount' => $grand,
                'paid_amount' => $spec['payment_status'] === PaymentStatus::Paid ? $grand : 0,
                'refunded_amount' => 0,
                'payment_method' => $spec['method'],
                'shipping_method_name' => 'Standard courier',
                'placed_at' => $placedAt,
                'created_at' => $placedAt,
                'tracking_number' => $spec['status'] === OrderStatus::Shipped ? 'TCS'.random_int(100000000, 999999999) : null,
                'courier' => $spec['status'] === OrderStatus::Shipped ? 'tcs' : null,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['variant']->product_id,
                    'product_variant_id' => $line['variant']->id,
                    'sku' => $line['variant']->sku,
                    'product_name' => $line['variant']->product->name,
                    'variant_name' => $line['variant']->name,
                    'product_slug' => $line['variant']->product->slug,
                    'quantity' => $line['qty'],
                    'unit_price_amount' => $line['unit'],
                    'line_subtotal_amount' => $line['lineTotal'],
                    'line_discount_amount' => 0,
                    'line_tax_amount' => 0,
                    'line_total_amount' => $line['lineTotal'],
                    // §2.6 — the compliance record, captured at purchase time.
                    'halal_snapshot' => [
                        'overall_status' => $line['variant']->product->halalProfile?->overall_status?->value,
                        'is_certified' => (bool) $line['variant']->product->halalProfile?->is_certified,
                        'is_self_declared' => (bool) $line['variant']->product->halalProfile?->is_self_declared,
                        'alcohol_status' => $line['variant']->product->halalProfile?->alcohol_status?->value,
                        'captured_at' => $placedAt->toIso8601String(),
                    ],
                ]);
            }

            $order->addresses()->create([
                'type' => 'shipping',
                'first_name' => str($spec['customer_name'])->before(' ')->toString(),
                'last_name' => str($spec['customer_name'])->after(' ')->toString(),
                'phone' => $spec['phone'],
                'line_1' => random_int(1, 200).' Example Street',
                'area' => 'Block '.chr(random_int(65, 72)),
                'city' => $spec['city'],
                'province' => $spec['province'],
                'country_code' => 'PK',
            ]);

            if ($spec['method'] === 'bank_transfer') {
                Payment::create([
                    'order_id' => $order->id,
                    'driver' => 'bank_transfer',
                    'status' => $spec['payment_status'] === PaymentStatus::Paid
                        ? PaymentAttemptStatus::Paid
                        : PaymentAttemptStatus::AwaitingVerification,
                    'currency' => 'PKR',
                    'amount' => $grand,
                    'refunded_amount' => 0,
                    'reference' => 'TRX'.random_int(10000000, 99999999),
                    'bank_account_id' => BankAccount::value('id'),
                    'paid_at' => $spec['payment_status'] === PaymentStatus::Paid ? $placedAt : null,
                    'created_at' => $placedAt,
                ]);
            }
        }
    }
}
