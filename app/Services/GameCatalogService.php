<?php

namespace App\Services;

use App\Data\GameData;
use App\GameProviders\CheapSharkProvider;
use App\GameProviders\FreeToGameProvider;
use App\GameProviders\GamerPowerProvider;
use App\GameProviders\RawgProvider;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class GameCatalogService
{
    public function __construct(
        private readonly RawgProvider $rawg,
        private readonly FreeToGameProvider $freeToGame,
        private readonly GamerPowerProvider $gamerPower,
        private readonly CheapSharkProvider $cheapShark,
    ) {
    }

    public function discover(array $filters = []): array
    {
        return $this->safeList('rawg', fn (): array => $this->rawg->discover($filters));
    }

    public function search(string $term, array $filters = []): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $key = $this->searchCacheKey($term, $filters);
        $cached = Cache::remember($key, now()->addSeconds(max(1, (int) config('games.search_ttl', 3600))), function () use ($term, $filters): array {
            $games = [
                ...$this->safeList('rawg', fn (): array => $this->rawg->search($term, $filters)),
                ...$this->safeList('freetogame', fn (): array => $this->freeToGame->search($term)),
                ...$this->safeList('gamerpower', fn (): array => $this->gamerPower->search($term)),
                ...$this->safeList('cheapshark', fn (): array => $this->cheapShark->search($term)),
            ];

            return array_map(
                static fn (GameData $game): array => $game->toArray(),
                $this->deduplicate($games)
            );
        });

        return array_map(
            static fn (array $item): GameData => GameData::fromArray($item),
            is_array($cached) ? array_filter($cached, 'is_array') : []
        );
    }

    public function details(string $provider, string $id): ?GameData
    {
        $provider = Str::lower($provider);

        return $this->safeDetail($provider, fn (): ?GameData => match ($provider) {
            'rawg' => $this->rawg->details($id),
            'freetogame' => $this->freeToGame->details($id),
            'gamerpower' => $this->gamerPower->details($id),
            'cheapshark' => $this->cheapShark->details($id),
            default => null,
        });
    }

    public function freeToPlay(array $filters = []): array
    {
        return $this->safeList('freetogame', fn (): array => $this->freeToGame->freeToPlay($filters));
    }

    public function giveaways(array $filters = []): array
    {
        return $this->safeList('gamerpower', fn (): array => $this->gamerPower->giveaways($filters));
    }

    public function deals(array $filters = []): array
    {
        return $this->safeList('cheapshark', fn (): array => $this->cheapShark->deals($filters));
    }

    public function genres(): array
    {
        return $this->safeArray('rawg', fn (): array => $this->rawg->genres());
    }

    public function platforms(): array
    {
        return $this->safeArray('rawg', fn (): array => $this->rawg->platforms());
    }

    private function deduplicate(array $games): array
    {
        $accepted = [];
        $seenProviderIds = [];

        foreach ($games as $game) {
            if (! $game instanceof GameData) {
                continue;
            }

            $providerKey = Str::lower($game->provider).'|'.$game->providerId;
            if (isset($seenProviderIds[$providerKey])) {
                continue;
            }

            $seenProviderIds[$providerKey] = true;
            $duplicate = false;
            foreach ($accepted as $existing) {
                if ($existing->provider === $game->provider) {
                    continue;
                }

                if ($this->confidentDuplicate($existing, $game)) {
                    $duplicate = true;
                    break;
                }
            }

            if (! $duplicate) {
                $accepted[] = $game;
            }
        }

        return $accepted;
    }

    private function confidentDuplicate(GameData $left, GameData $right): bool
    {
        $leftTitle = $this->normalizedTitle($left->title);
        $rightTitle = $this->normalizedTitle($right->title);

        if ($leftTitle === '' || $leftTitle !== $rightTitle) {
            return false;
        }

        if ($left->releaseDate === null || $right->releaseDate === null || $left->releaseDate !== $right->releaseDate) {
            return false;
        }

        $leftPlatforms = array_map(fn (mixed $platform): string => Str::lower(trim((string) $platform)), $left->platforms);
        $rightPlatforms = array_map(fn (mixed $platform): string => Str::lower(trim((string) $platform)), $right->platforms);

        return count(array_intersect($leftPlatforms, $rightPlatforms)) > 0;
    }

    private function normalizedTitle(string $title): string
    {
        return preg_replace('/[^a-z0-9]+/', '', Str::lower($title)) ?? '';
    }

    private function searchCacheKey(string $term, array $filters): string
    {
        ksort($filters);
        $identity = json_encode(['q' => Str::lower($term), 'filters' => $filters], JSON_UNESCAPED_SLASHES) ?: '';

        return config('games.cache_prefix', 'game-providers').':aggregate-search:'.hash('sha256', $identity);
    }

    private function safeList(string $provider, Closure $callback): array
    {
        $result = $this->safeArray($provider, $callback);

        return array_values(array_filter($result, static fn (mixed $game): bool => $game instanceof GameData));
    }

    private function safeArray(string $provider, Closure $callback): array
    {
        try {
            $result = $callback();
            return is_array($result) ? $result : [];
        } catch (Throwable $exception) {
            $this->logProviderFailure($provider, $exception);
            return [];
        }
    }

    private function safeDetail(string $provider, Closure $callback): ?GameData
    {
        try {
            $result = $callback();
            return $result instanceof GameData ? $result : null;
        } catch (Throwable $exception) {
            $this->logProviderFailure($provider, $exception);
            return null;
        }
    }

    private function logProviderFailure(string $provider, Throwable $exception): void
    {
        Log::warning('Game provider service failed safely', [
            'provider' => $provider,
            'exception' => $exception::class,
        ]);
    }
}
