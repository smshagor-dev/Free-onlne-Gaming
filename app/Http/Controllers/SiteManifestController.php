<?php

namespace App\Http\Controllers;

use App\Services\SiteCms;
use Illuminate\Http\JsonResponse;

class SiteManifestController extends Controller
{
    public function show(): JsonResponse
    {
        $config = SiteCms::section('pwa');

        // Only safe enumerated values are served, never arbitrary URLs or HTML.
        $manifest = [
            'id' => '/',
            'name' => $config['name'],
            'short_name' => $config['short_name'],
            'description' => $config['description'],
            'start_url' => '/',
            'scope' => '/',
            'display' => $config['display'],
            'theme_color' => $config['theme_color'],
            'background_color' => $config['background_color'],
            'icons' => [
                [
                    'src' => '/icons/free-games-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/free-games-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json; charset=utf-8')
            ->header('Cache-Control', 'no-cache, must-revalidate');
    }
}
