<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Store Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings applied when a new store is created.
    |
    */
    'defaults' => [
        'timezone' => env('STORE_DEFAULT_TIMEZONE', 'UTC'),
        'currency' => env('STORE_DEFAULT_CURRENCY', 'BDT'),
        'locale' => env('STORE_DEFAULT_LOCALE', 'en'),
        'status' => 'active',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    |
    | The session key used to store the current store ID.
    |
    */
    'session_key' => 'current_store_id',

];
