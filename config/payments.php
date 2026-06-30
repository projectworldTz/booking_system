<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Airtel Money (Tanzania)
    |--------------------------------------------------------------------------
    | Obtain credentials from: https://developers.airtel.africa
    | Sandbox base URL: https://openapiuat.airtel.africa
    | Production base URL: https://openapi.airtel.africa
    */
    'airtel_money' => [
        'api_key'    => env('AIRTEL_MONEY_API_KEY'),
        'api_secret' => env('AIRTEL_MONEY_API_SECRET'),
        'base_url'   => env('AIRTEL_MONEY_BASE_URL', 'https://openapi.airtel.africa'),
        'country'    => env('AIRTEL_MONEY_COUNTRY', 'TZ'),
        'currency'   => env('AIRTEL_MONEY_CURRENCY', 'TZS'),
    ],

    /*
    |--------------------------------------------------------------------------
    | M-Pesa / Vodacom Tanzania
    |--------------------------------------------------------------------------
    | Obtain credentials from: https://developers.vodacom.co.tz
    | Sandbox base URL: https://sandbox.safaricom.co.ke (or local Vodacom endpoint)
    | Production base URL: provided by Vodacom Tanzania
    */
    'mpesa' => [
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'short_code'      => env('MPESA_SHORT_CODE'),
        'passkey'         => env('MPESA_PASSKEY'),
        'base_url'        => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
        'auth_url'        => env('MPESA_AUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Halotel / HaloPesa (Tanzania)
    |--------------------------------------------------------------------------
    | Contact Halotel business team to obtain API credentials.
    | Endpoint provided directly by Halotel upon onboarding.
    */
    'halotel' => [
        'username' => env('HALOTEL_USERNAME'),
        'password' => env('HALOTEL_PASSWORD'),
        'base_url' => env('HALOTEL_BASE_URL', 'https://halopesa.halotel.co.tz'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mix by Yas (formerly Tigo Pesa / MIC Tanzania)
    |--------------------------------------------------------------------------
    | Contact Mix by Yas (Yas Tanzania) business team for API access.
    | Previously known as Tigo Pesa — credentials work on the same platform.
    */
    'mix_by_yas' => [
        'api_key'        => env('MIX_BY_YAS_API_KEY'),
        'api_secret'     => env('MIX_BY_YAS_API_SECRET'),
        'biller_msisdn'  => env('MIX_BY_YAS_BILLER_MSISDN'),
        'base_url'       => env('MIX_BY_YAS_BASE_URL', 'https://api.mixbyyas.co.tz'),
    ],

];
