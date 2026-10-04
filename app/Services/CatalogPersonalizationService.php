<?php

namespace App\Services;

use App\Data\GameData;
use App\Models\CatalogRecentGame;
use App\Models\CatalogSavedGame;
use App\Models\UserGamePreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class CatalogPersonalizationService
{
    public const RECENT_LIMIT = 50;

    public function savedKeys(User $user): array
    {
        return CatalogSavedGame::query()->where('user_id', $user->id)
            ->get(['provider', 'provider_game_id'])
            ->mapWithKeys(fn ($row) => [$this->key($row->provider, $row->provider_game_id) => true])
            ->all();
    }

    public function save(User $user, GameData $game): CatalogSavedGame
    {
        return CatalogSavedGame::query()->firstOrCreate([
            'user_id' => $user->id,
            'provider' => $game->provider,
            'provider_game_id' => $game->providerId,
        ], $this->snapshot($game));
    }

    public function remove(User $user, string $provider, string $id): int
    {
        return CatalogSavedGame::query()->where('user_id', $user->id)
            ->where('provider', $provider)->where('provider_game_id', $id)->delete();
    }

    public function recordRecent(User $user, GameData $game): void
    {
        CatalogRecentGame::query()->updateOrCreate([
            'user_id' => $user->id,
            'provider' => $game->provider,
            'provider_game_id' => $game->providerId,
        ], [...$this->snapshot($game), 'viewed_at' => now()]);

        $keepIds = CatalogRecentGame::query()->where('user_id', $user->id)
            ->latest('viewed_at')->limit(self::RECENT_LIMIT)->pluck('id');
        CatalogRecentGame::query()->where('user_id', $user->id)
            ->when($keepIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->delete();
    }

    public function saved(User $user, int $limit = 12): array
    {
        return $this->hydrate(CatalogSavedGame::query()->where('user_id', $user->id)->latest()->limit($limit)->get());
    }

    public function recent(User $user, int $limit = 12): array
    {
        return $this->hydrate(CatalogRecentGame::query()->where('user_id', $user->id)->latest('viewed_at')->limit($limit)->get());
    }

    public function preferences(User $user): UserGamePreference
    {
        return UserGamePreference::query()->firstOrNew(['user_id' => $user->id]);
    }

    public function updatePreferences(User $user, array $genres, array $platforms): UserGamePreference
    {
        return UserGamePreference::query()->updateOrCreate(['user_id' => $user->id], [
            'preferred_genres' => array_values(array_unique($genres)),
            'preferred_platforms' => array_values(array_unique($platforms)),
        ]);
    }

    public function personalize(array $games, UserGamePreference $preferences, int $limit = 12): array
    {
        $genres = collect($preferences->preferred_genres ?? [])->map(fn ($v) => Str::lower((string) $v));
        $platforms = collect($preferences->preferred_platforms ?? [])->map(fn ($v) => Str::lower((string) $v));
        if ($genres->isEmpty() && $platforms->isEmpty()) {
            return array_slice(array_values($games), 0, $limit);
        }

        $scored = collect($games)->filter(fn ($game) => $game instanceof GameData)->map(function (GameData $game) use ($genres, $platforms) {
            $gameGenres = collect($game->genres)->map(fn ($v) => Str::lower((string) $v));
            $gamePlatforms = collect($game->platforms)->map(fn ($v) => Str::lower((string) $v));
            $score = $gameGenres->intersect($genres)->count() * 2 + $gamePlatforms->intersect($platforms)->count();
            return ['game' => $game, 'score' => $score];
        })->sortByDesc('score')->pluck('game')->values()->all();

        return array_slice($scored, 0, $limit);
    }

    public function clearRecent(User $user): int
    {
        return CatalogRecentGame::query()->where('user_id', $user->id)->delete();
    }

    private function snapshot(GameData $game): array
    {
        return [
            'title' => Str::limit($game->title, 255, ''),
            'image' => $game->image ?? $game->backgroundImage,
            'genres' => array_slice($game->genres, 0, 12),
            'platforms' => array_slice($game->platforms, 0, 12),
        ];
    }

    private function hydrate(Collection $rows): array
    {
        return $rows->map(fn ($row) => GameData::fromArray([
            'id' => $row->provider.':'.$row->provider_game_id,
            'provider' => $row->provider,
            'provider_id' => $row->provider_game_id,
            'title' => $row->title,
            'image' => $row->image,
            'background_image' => $row->image,
            'genres' => $row->genres ?? [],
            'platforms' => $row->platforms ?? [],
        ]))->all();
    }

    private function key(string $provider, string $id): string
    {
        return Str::lower($provider).'|'.$id;
    }
}
