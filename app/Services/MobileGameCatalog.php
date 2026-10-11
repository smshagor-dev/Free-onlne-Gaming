<?php

namespace App\Services;

use App\Data\GameData;
use App\Models\Game;

class MobileGameCatalog
{
    public function __construct(private EmbeddableGameCatalog $browser, private GameCatalogService $catalog) {}

    public function list(string $provider): array
    {
        return match ($provider) {
            'gamemonetize' => array_map(fn ($g) => ['id' => $g['id'], 'provider' => 'gamemonetize', 'title' => $g['name'], 'image' => $g['image'], 'category' => $g['category'], 'play_url' => $g['url'], 'playable' => true], array_values(array_filter($this->browser->all(), fn ($g) => preg_match('~^https://html5\.gamemonetize\.co/[a-z0-9]+/$~', $g['url'] ?? '')))),
            'local' => Game::with('category')->get()->filter(fn ($g) => ! preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', $g->name.' '.($g->category?->name ?? '')))->map(fn ($g) => ['id' => (string) $g->id, 'provider' => 'local', 'title' => $g->name, 'image' => asset($g->image ?: 'default.png'), 'category' => $g->category?->name ?? 'Games', 'play_url' => route('games.open', $g->id), 'playable' => false])->values()->all(),
            'freetogame' => array_values(array_filter(array_map(fn ($g) => $this->external($g), $this->catalog->freeToPlay()), fn ($g) => ! preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', $g['title'].' '.implode(' ', $g['genres'])))),
            'gamerpower' => array_values(array_filter(array_map(fn ($g) => $this->external($g), $this->catalog->giveaways()), fn ($g) => ! preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', $g['title'].' '.implode(' ', $g['genres'])))),
            default => [],
        };
    }

    private function external(GameData $game): array
    {
        return [...$game->toArray(), 'id' => $game->providerId, 'playable' => false, 'play_url' => null];
    }

    public function find(string $provider, string $id): ?array
    {
        if (in_array($provider, ['freetogame', 'gamerpower'], true)) {
            $game = $this->catalog->details($provider, $id);

            return $game && ! preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', $game->title.' '.implode(' ', $game->genres)) ? $this->external($game) : null;
        }
        foreach ($this->list($provider) as $game) {
            if ($game['id'] === $id) {
                return $game;
            }
        }

        return null;
    }
}
