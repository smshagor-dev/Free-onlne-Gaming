<?php

namespace App\Services;

use App\Models\CatalogSavedGame;
use App\Models\MobileGameState;
use App\Models\User;
use Illuminate\Support\Collection;

class MobileActivityService
{
    public function favorites(User $user): Collection
    {
        $mobile = MobileGameState::where('user_id', $user->id)->where('provider', 'gamemonetize')->where('favorite', true)->get()->map(fn ($s) => $s->only(['provider', 'game_id', 'snapshot', 'favorite', 'played_at', 'updated_at']));
        $local = $user->favorites()->whereHas('game')->with('game.category')->get()->map(fn ($f) => ['provider' => 'local', 'game_id' => (string) $f->game_id, 'snapshot' => ['id' => (string) $f->game_id, 'provider' => 'local', 'title' => $f->game->name, 'category' => $f->game->category?->name, 'image' => asset($f->game->image ?: 'default.png'), 'playable' => false, 'play_url' => null], 'favorite' => true, 'updated_at' => $f->updated_at]);
        $catalog = CatalogSavedGame::where('user_id', $user->id)->whereIn('provider', ['freetogame', 'gamerpower'])->get()->map(fn ($s) => ['provider' => $s->provider, 'game_id' => $s->provider_game_id, 'snapshot' => ['id' => $s->provider_game_id, 'provider' => $s->provider, 'title' => $s->title, 'category' => implode(' ', $s->genres ?? []), 'image' => $s->image, 'playable' => false, 'play_url' => null], 'favorite' => true, 'updated_at' => $s->updated_at]);

        return $this->clean($mobile->concat($local)->concat($catalog));
    }

    public function recent(User $user): Collection
    {
        $mobile = MobileGameState::where('user_id', $user->id)->where('provider', 'gamemonetize')->whereNotNull('played_at')->get()->map(fn ($s) => $s->only(['provider', 'game_id', 'snapshot', 'favorite', 'played_at', 'updated_at']));

        return $this->clean($mobile)->sortByDesc('played_at')->values();
    }

    private function clean(Collection $items): Collection
    {
        return $items->filter(fn ($s) => ! preg_match('/\b(casino|slots?|roulette|black\s?jack|baccarat|poker|gambling)\b/i', ($s['snapshot']['title'] ?? '').' '.($s['snapshot']['category'] ?? '')))->sortByDesc('updated_at')->unique(fn ($s) => $s['provider'].'|'.$s['game_id'])->values();
    }
}
