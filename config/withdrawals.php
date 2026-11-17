<?php

return [
    // Keep all cash-out requests closed until licensing and operator
    // procedures have been established. No API payout is implemented.
    'enabled' => (bool) env('WITHDRAWAL_REQUESTS_ENABLED', false),
    'currency' => strtoupper((string) env('WITHDRAWAL_CURRENCY', 'USD')),
    'minimum' => (string) env('WITHDRAWAL_MIN_AMOUNT', '10.00'),
    'maximum' => (string) env('WITHDRAWAL_MAX_AMOUNT', '10000.00'),
];
