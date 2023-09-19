<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'tx_types' => env('WALLET_TX_TYPES'),
    'decimal_separator' => env('WALLET_DECIMAL_SEPARATOR', '.'),
    'thousand_separator' => env('WALLET_THOUSAND_SEPARATOR', ','),
    'default_wallet' => env('WALLET_DEFAULT', 'default'),
    'table_names' => [
        'wallet_types' => env('WALLET_TABLE_NAMES_WALLET_TYPES', 'wallet_types'),
        'wallets' => env('WALLET_TABLE_NAMES_WALLET', 'wallets'),
        'transaction_batches' => env('WALLET_TABLE_NAMES_TRANSACTION_BATCHES', 'transaction_batches'),
        'transactions' => env('WALLET_TABLE_NAMES_TRANSACTIONS', 'transactions'),
    ]
];
