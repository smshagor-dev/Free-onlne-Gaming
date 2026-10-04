<?php

return [
    'host' => env('CASINO_API_HOST'),
    'cdn_url' => env('CASINO_CDN_URL', 'https://static.cdns-stat.com/resources/'),
    'domain' => env('CASINO_DOMAIN', env('APP_URL')),
    'currency' => env('CASINO_CURRENCY', 'BDT'),

    'providers' => [
        'default' => [
            'hall' => env('CASINO_HALL'),
            'key' => env('CASINO_KEY'),
            'balance_fields' => ['balance', 'available_balance'],
            'win_field' => 'available_balance',
            'refund_field' => 'balance',
            'cache_key' => 'casino_raw_response',
            'transaction_comment' => 'casino game bet',
        ],
        'bonus' => [
            'hall' => env('CASINO_BONUS_HALL'),
            'key' => env('CASINO_BONUS_KEY'),
            'balance_fields' => ['bonus_balance'],
            'win_field' => 'bonus_balance',
            'refund_field' => 'bonus_balance',
            'cache_key' => 'casino_bonus_response',
            'transaction_comment' => 'casino Bonus bet',
        ],
        'cashback' => [
            'hall' => env('CASINO_CASHBACK_HALL'),
            'key' => env('CASINO_CASHBACK_KEY'),
            'balance_fields' => ['cashback'],
            'win_field' => 'cashback',
            'refund_field' => 'cashback',
            'cache_key' => 'casino_cashback_response',
            'transaction_comment' => 'casino cashback bet',
        ],
        'vip_bonus' => [
            'hall' => env('CASINO_VIP_BONUS_HALL'),
            'key' => env('CASINO_VIP_BONUS_KEY'),
            'balance_fields' => ['vip_bonus'],
            'win_field' => 'vip_bonus',
            'refund_field' => 'vip_bonus',
            'cache_key' => 'casino_vip_response',
            'transaction_comment' => 'casino Bonus bet',
        ],
    ],
];
