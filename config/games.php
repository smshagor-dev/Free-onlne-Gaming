<?php

return [
    'cache_prefix' => env('GAME_PROVIDER_CACHE_PREFIX', 'game-providers'),
    'stale_ttl' => (int) env('GAME_PROVIDER_STALE_TTL', 86400),
    'search_ttl' => (int) env('GAME_SEARCH_CACHE_TTL', 3600),

    'rawg' => [
        'base_url' => env('RAWG_BASE_URL', 'https://api.rawg.io/api'),
        'key' => env('RAWG_API_KEY'),
        'timeout' => (int) env('RAWG_TIMEOUT', env('GAME_PROVIDER_TIMEOUT', 8)),
        'connect_timeout' => (int) env('RAWG_CONNECT_TIMEOUT', env('GAME_PROVIDER_CONNECT_TIMEOUT', 3)),
        'list_ttl' => 21600,
        'detail_ttl' => 43200,
    ],

    'freetogame' => [
        'base_url' => env('FREE_TO_GAME_BASE_URL', 'https://www.freetogame.com/api'),
        'timeout' => (int) env('FREE_TO_GAME_TIMEOUT', env('GAME_PROVIDER_TIMEOUT', 8)),
        'connect_timeout' => (int) env('FREE_TO_GAME_CONNECT_TIMEOUT', env('GAME_PROVIDER_CONNECT_TIMEOUT', 3)),
        'ttl' => 43200,
    ],

    'gamerpower' => [
        'base_url' => env('GAMERPOWER_BASE_URL', 'https://www.gamerpower.com/api'),
        'timeout' => (int) env('GAMERPOWER_TIMEOUT', env('GAME_PROVIDER_TIMEOUT', 8)),
        'connect_timeout' => (int) env('GAMERPOWER_CONNECT_TIMEOUT', env('GAME_PROVIDER_CONNECT_TIMEOUT', 3)),
        'ttl' => 1200,
    ],

    'cheapshark' => [
        'base_url' => env('CHEAPSHARK_BASE_URL', 'https://www.cheapshark.com/api/1.0'),
        'web_url' => env('CHEAPSHARK_WEB_URL', 'https://www.cheapshark.com'),
        'timeout' => (int) env('CHEAPSHARK_TIMEOUT', env('GAME_PROVIDER_TIMEOUT', 8)),
        'connect_timeout' => (int) env('CHEAPSHARK_CONNECT_TIMEOUT', env('GAME_PROVIDER_CONNECT_TIMEOUT', 3)),
        'ttl' => 1200,
    ],
];
