<?php

use App\Modules\Billing\Drivers\BKashDriver;
use App\Modules\Billing\Drivers\ManualDriver;
use App\Modules\Billing\Drivers\NagadDriver;
use App\Modules\Billing\Drivers\PortWalletDriver;
use App\Modules\Billing\Drivers\SSLCommerzDriver;
use App\Modules\Billing\Drivers\StripeDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | The default gateway to use when initiating payments. This can be
    | overridden at the subscription level.
    */
    'default_gateway' => env('BILLING_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    */
    'invoice_prefix' => env('BILLING_INVOICE_PREFIX', 'INV-'),

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    */
    'currency' => env('BILLING_CURRENCY', 'BDT'),
    'currency_locale' => env('BILLING_CURRENCY_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Each gateway must specify a 'driver' class (the implementation of
    | BillingGatewayInterface) and its configuration options.
    |
    | To add a new gateway, create a driver class and add it here.
    */
    'gateways' => [

        'stripe' => [
            'driver' => StripeDriver::class,
            'label' => 'Stripe',
            'config' => [
                'secret_key' => env('STRIPE_SECRET'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            ],
        ],

        'sslcommerz' => [
            'driver' => SSLCommerzDriver::class,
            'label' => 'SSLCommerz',
            'config' => [
                'store_id' => env('SSLC_STORE_ID'),
                'store_password' => env('SSLC_STORE_PASSWORD'),
                'sandbox' => env('SSLC_SANDBOX', true),
            ],
        ],

        'bkash' => [
            'driver' => BKashDriver::class,
            'label' => 'bKash',
            'config' => [
                'username' => env('BKASH_USERNAME'),
                'password' => env('BKASH_PASSWORD'),
                'app_key' => env('BKASH_APP_KEY'),
                'app_secret' => env('BKASH_APP_SECRET'),
                'sandbox' => env('BKASH_SANDBOX', true),
            ],
        ],

        'nagad' => [
            'driver' => NagadDriver::class,
            'label' => 'Nagad',
            'config' => [
                'merchant_id' => env('NAGAD_MERCHANT_ID'),
                'public_key' => env('NAGAD_PUBLIC_KEY'),
                'private_key' => env('NAGAD_PRIVATE_KEY'),
                'sandbox' => env('NAGAD_SANDBOX', true),
            ],
        ],

        'portwallet' => [
            'driver' => PortWalletDriver::class,
            'label' => 'PortWallet',
            'config' => [
                'api_key' => env('PORTWALLET_API_KEY'),
                'api_secret' => env('PORTWALLET_API_SECRET'),
                'sandbox' => env('PORTWALLET_SANDBOX', true),
            ],
        ],

        'manual' => [
            'driver' => ManualDriver::class,
            'label' => 'Manual Payment',
            'config' => [],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Statuses
    |--------------------------------------------------------------------------
    |
    | The number of days after expiration to keep subscriptions in "grace"
    | period before fully expiring.
    */
    'grace_period_days' => env('BILLING_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'stripe' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
