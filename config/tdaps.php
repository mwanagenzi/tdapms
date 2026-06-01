<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => 'Tenant Deposit & Property Management System',
    'short_name' => 'TDAPS',

    /*
    |--------------------------------------------------------------------------
    | Deposit Rules
    |--------------------------------------------------------------------------
    | default_deposit_months: number of months' rent used to auto-calculate
    | the deposit when creating a lease agreement.
    */
    'deposit' => [
        'default_deposit_months' => env('TDAPS_DEFAULT_DEPOSIT_MONTHS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | MPESA / Daraja API
    |--------------------------------------------------------------------------
    */
    'mpesa' => [
        'env'                    => env('MPESA_ENV', 'sandbox'),
        'consumer_key'           => env('MPESA_CONSUMER_KEY', ''),
        'consumer_secret'        => env('MPESA_CONSUMER_SECRET', ''),
        'shortcode'              => env('MPESA_SHORTCODE', ''),
        'passkey'                => env('MPESA_PASSKEY', ''),
        'stk_callback_url'       => env('MPESA_STK_CALLBACK_URL', ''),

        // B2C (refund disbursement)
        'b2c_shortcode'          => env('MPESA_B2C_SHORTCODE', ''),
        'b2c_initiator_name'     => env('MPESA_B2C_INITIATOR_NAME', ''),
        'b2c_initiator_password' => env('MPESA_B2C_INITIATOR_PASSWORD', ''),
        'b2c_result_url'         => env('MPESA_B2C_RESULT_URL', ''),
        'b2c_timeout_url'        => env('MPESA_B2C_TIMEOUT_URL', ''),

        // Sandbox base URL
        'sandbox_url'            => 'https://sandbox.safaricom.co.ke',
        'live_url'               => 'https://api.safaricom.co.ke',
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code'   => 'KES',
        'symbol' => 'KES',
    ],
];
