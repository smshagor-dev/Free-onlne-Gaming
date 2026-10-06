<?php

return [
    'install-button' => true,

    'manifest' => [
        'id' => '/games',
        'name' => 'Free Games',
        'short_name' => 'FreeGames',
        'background_color' => '#0b141d',
        'display' => 'standalone',
        'orientation' => 'any',
        'description' => 'Discover free-to-play games, giveaways and current game deals from multiple trusted sources.',
        'theme_color' => '#0b141d',
        'start_url' => '/games',
        'scope' => '/',
        'icons' => [
            [
                'src' => '/logo.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],

    'debug' => env('APP_DEBUG', false),
    'livewire-app' => false,
];
