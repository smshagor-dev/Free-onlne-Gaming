<?php

return [
    // Remains off until the merchant configures a NOWPayments account and IPN key.
    'enabled' => (bool) env('NOWPAYMENTS_ENABLED', false),
    'url' => rtrim((string) env('NOWPAYMENTS_API_URL', 'https://api.nowpayments.io'), '/'),
    'api_key' => (string) env('NOWPAYMENTS_API_KEY', ''),
    'ipn_secret' => (string) env('NOWPAYMENTS_IPN_SECRET', ''),
    'currency' => strtoupper((string) env('NOWPAYMENTS_PRICE_CURRENCY', 'USD')),
    'minimum' => (string) env('NOWPAYMENTS_MIN_AMOUNT', '1.00'),
    'maximum' => (string) env('NOWPAYMENTS_MAX_AMOUNT', '10000.00'),
];
