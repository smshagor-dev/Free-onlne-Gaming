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
            route('catalog.deals'),
            route('free.games.index'),
            route('casino.index'),
            route('casino.bonus.index'),
            route('casino.cashback.index'),
            route('casino.vip.bonus.index'),
            route('bonuses.index'),
            route('promotion.index'),
        ];

        $urls = collect($routes)
            ->filter()
            ->unique()
            ->values();

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
