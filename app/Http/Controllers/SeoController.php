<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $routes = [
            route('games.viewIndex'),
            route('catalog.index'),
            route('catalog.free'),
            route('catalog.giveaways'),
            route('free.games.index'),
            route('user.lottaries.view'),
        ];

        $urls = collect($routes)
            ->filter()
            ->unique()
            ->values();

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
