<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EmbeddableGameCatalog
{
    public function all(): array
    {
        $cached = Cache::get('gamemonetize.embeddable.all');
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        try {
            $items = Http::timeout(15)->get('https://gamemonetize.com/rssfeed.php', [
                'format' => 'json',
                'category' => 'All',
                'type' => 'html5',
                'popularity' => 'newest',
                'company' => 'All',
                'amount' => 'All',
            ])->json();
        } catch (\Throwable) {
            return [];
        }

        if (!is_array($items)) {
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

        $games = array_values($games);
        if ($games !== []) {
            Cache::put('gamemonetize.embeddable.all', $games, now()->addHours(6));
        }

        return $games;
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
