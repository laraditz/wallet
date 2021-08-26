<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'decimal' => env('WALLET_DECIMAL', 2),
    'tx_types' => env('WALLET_TX_TYPES', null),
    'wallet_type' => [
        'name' => env('WALLET_TYPE_DEFAULT_NAME', 'Default'),
        'slug' => env('WALLET_TYPE_DEFAULT_SLUG', 'default'),
        'description' => env('WALLET_TYPE_DEFAULT_DESCRIPTION', 'The default wallet'),
        'currency_code' => env('WALLET_TYPE_DEFAULT_CURRENCY', 'MYR'),
    ],
];
