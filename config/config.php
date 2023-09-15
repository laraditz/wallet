<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'tx_types' => env('WALLET_TX_TYPES'),
    'wallet_type' => [
        'name' => env('WALLET_TYPE_DEFAULT_NAME', 'Default'),
        'slug' => env('WALLET_TYPE_DEFAULT_SLUG', 'default'),
        'description' => env('WALLET_TYPE_DEFAULT_DESCRIPTION', 'The default wallet'),
        'currency_code' => env('WALLET_TYPE_DEFAULT_CURRENCY', 'PTS'),
        'currency_symbol' => env('WALLET_TYPE_DEFAULT_CURRENCY_SYMBOL', 'Pts'),
    ],
    'table_names' => [
        'wallet_types' => env('WALLET_TABLE_NAMES_WALLET_TYPES', 'wallet_types'),
        'wallets' => env('WALLET_TABLE_NAMES_WALLET', 'wallets'),
        'transaction_batches' => env('WALLET_TABLE_NAMES_TRANSACTION_BATCHES', 'transaction_batches'),
        'transactions' => env('WALLET_TABLE_NAMES_TRANSACTIONS', 'transactions'),
    ]
];
