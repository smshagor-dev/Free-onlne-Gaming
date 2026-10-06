<?php

namespace App\Http\Controllers;

use App\Models\GamesCategory;
use App\Models\Page;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class SeoController extends Controller
{
    public function sitemap(): Response
    {
        try {
            $entries = Cache::remember('seo.sitemap.v1', 3600, function (): array {
                $entries = $this->staticEntries();

                Page::query()->where('status', 1)->get(['slug', 'updated_at'])->each(function (Page $page) use (&$entries): void {
                    $entries[] = ['loc' => route('page.show', $page->slug), 'lastmod' => $page->updated_at?->toAtomString()];
                });

                GamesCategory::query()->get(['id', 'updated_at'])->each(function (GamesCategory $category) use (&$entries): void {
                    $entries[] = ['loc' => route('category.view', $category->id), 'lastmod' => $category->updated_at?->toAtomString()];
                });

                return $entries;
            });
        } catch (Throwable) {
            $entries = $this->staticEntries();
        }

        return response()
            ->view('seo.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /user/',
            'Disallow: /api/',
            'Disallow: /auth/',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /registration',
            'Disallow: /verify',
            'Disallow: /2fa/',
            'Disallow: /password/',
            'Disallow: /forgot-password',
            'Disallow: /private-files/',
            'Disallow: /sm-shagor/free-games/admin-main/control-back-office/',
            '',
            'Sitemap: '.route('seo.sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function staticEntries(): array
    {
        return [
            ['loc' => route('games.viewIndex'), 'lastmod' => null],
            ['loc' => route('catalog.index'), 'lastmod' => null],
            ['loc' => route('catalog.free'), 'lastmod' => null],
            ['loc' => route('catalog.giveaways'), 'lastmod' => null],
            ['loc' => route('catalog.deals'), 'lastmod' => null],
            ['loc' => route('free.games.index'), 'lastmod' => null],
            ['loc' => route('casino.index'), 'lastmod' => null],
        ];
    }
}
