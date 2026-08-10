<?php

return [
    'default' => 'cod',

    'drivers' => [

        'cod' => [
            'driver' => \App\Services\Payments\Drivers\CashOnDeliveryDriver::class,
            'enabled' => env('PAYMENTS_COD_ENABLED', true),
            'config' => [
                // Paisa. Unbounded COD is how Pakistani stores lose money to
                // refused high-value deliveries; the ceiling is config so the
                // owner can tune it without a deploy.
                'max_order_amount' => (int) env('PAYMENTS_COD_MAX_AMOUNT', 5_000_000),   // Rs 50,000
                // The COD collection fee comes from the matched shipping rate
                // (shipping_rates.cod_surcharge_amount). This is the fallback
                // used only when no rate matches.
                'fee_amount' => (int) env('PAYMENTS_COD_FEE', 0),
                'serviceable_cities' => null,   // null = everywhere
            ],
        ],

        'bank_transfer' => [
            'driver' => \App\Services\Payments\Drivers\BankTransferDriver::class,
            'enabled' => env('PAYMENTS_BANK_TRANSFER_ENABLED', true),
            'config' => [
                'payment_window_hours' => 48,
                'require_proof' => true,
            ],
        ],

        // Drop-in later. No checkout change required — write a class against
        // App\Contracts\Payments\PaymentDriver and flip `enabled`.
        // 'jazzcash' => [
        //     'driver'  => \App\Services\Payments\Drivers\JazzCashDriver::class,
        //     'enabled' => env('PAYMENTS_JAZZCASH_ENABLED', false),
        //     'config'  => [
        //         'merchant_id'    => env('JAZZCASH_MERCHANT_ID'),
        //         'password'       => env('JAZZCASH_PASSWORD'),
        //         'integrity_salt' => env('JAZZCASH_INTEGRITY_SALT'),
        //         'endpoint'       => env('JAZZCASH_ENDPOINT'),
        //     ],
        // ],
    ],
];
