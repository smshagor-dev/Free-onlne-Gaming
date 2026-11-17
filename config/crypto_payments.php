<?php

return [
    // Explicit opt-in: no invoice will be issued without your own BTCPay store.
    'enabled' => (bool) env('CRYPTO_BTCPAY_ENABLED', false),
    'url' => rtrim((string) env('CRYPTO_BTCPAY_URL', ''), '/'),
    'store_id' => (string) env('CRYPTO_BTCPAY_STORE_ID', ''),
    'api_key' => (string) env('CRYPTO_BTCPAY_API_KEY', ''),
    'webhook_secret' => (string) env('CRYPTO_BTCPAY_WEBHOOK_SECRET', ''),
    'currency' => strtoupper((string) env('CRYPTO_BTCPAY_CURRENCY', 'USD')),
    'minimum' => (string) env('CRYPTO_BTCPAY_MIN_AMOUNT', '1.00'),
    'maximum' => (string) env('CRYPTO_BTCPAY_MAX_AMOUNT', '10000.00'),
];
