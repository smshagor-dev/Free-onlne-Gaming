<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EmbeddableGameCatalog
{
    public function all(): array
    {
        $cached = Cache::get('gamemonetize.embeddable.all');
        if (is_array($cached)) {
            return $this->withoutCasinoGames($cached);
        }

        try {
            $items = Http::connectTimeout(3)->timeout(8)->get('https://gamemonetize.com/rssfeed.php', [
                'format' => 'json',
                'category' => 'All',
                'type' => 'html5',
                'popularity' => 'newest',
                'company' => 'All',
                'amount' => 'All',
            ])->json();
        } catch (\Throwable) {
            Cache::put('gamemonetize.embeddable.all', [], now()->addMinute());
            return [];
        }

        if (!is_array($items)) {
            Cache::put('gamemonetize.embeddable.all', [], now()->addMinute());
            return [];
        }

        $games = [];
        foreach ($items as $item) {
            if (!is_array($item) || !is_string($item['url'] ?? null)) {
                continue;
            }

            $url = $item['url'];
            if (!preg_match('~^https://html5\.gamemonetize\.co/([a-z0-9]+)/$~', $url, $matches)) {
                continue;
            }

            $games[$matches[1]] = [
                'id' => $matches[1],
                'name' => html_entity_decode((string) ($item['title'] ?? $matches[1]), ENT_QUOTES | ENT_HTML5),
                'category' => (string) ($item['category'] ?? 'Games'),
                'image' => filter_var($item['thumb'] ?? null, FILTER_VALIDATE_URL) ?: null,
                'url' => $url,
            ];
        }

        $games = $this->withoutCasinoGames(array_values($games));
        Cache::put('gamemonetize.embeddable.all', $games, $games !== [] ? now()->addHours(6) : now()->addMinute());

        return $games;
    }

    private function withoutCasinoGames(array $games): array
    {
        return array_values(array_filter($games, static function (array $game): bool {
            $text = ($game['name'] ?? '').' '.($game['category'] ?? '');
            return !preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', $text);
        }));
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $game) {
            if ($game['id'] === $id) {
                return $game;
            }
        }

        return null;
    }
}
