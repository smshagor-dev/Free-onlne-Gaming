<?php

return [
    'force_https' => (bool) env('FORCE_HTTPS', false),

    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),

    'trusted_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_HOSTS', ''))
    ))),

    'hsts' => [
        'enabled' => (bool) env('HSTS_ENABLED', false),
        'max_age' => max(0, (int) env('HSTS_MAX_AGE', 31536000)),
        'include_subdomains' => (bool) env('HSTS_INCLUDE_SUBDOMAINS', true),
        'preload' => (bool) env('HSTS_PRELOAD', false),
    ],

    'sanitize_provider_errors' => (bool) env('SANITIZE_PROVIDER_ERRORS', true),

    'demo_retention_hours' => max(1, (int) env('DEMO_RETENTION_HOURS', 24)),
];
