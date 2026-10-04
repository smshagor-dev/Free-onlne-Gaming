<?php

namespace App\GameProviders;

use App\Data\GameData;
use Illuminate\Support\Str;

final class FreeToGameProvider extends AbstractGameProvider
{
    public function name(): string
    {
        return 'freetogame';
    }

    protected function configKey(): string
    {
        return 'freetogame';
    }

    public function freeToPlay(array $filters = []): array
    {
        $query = array_filter([
            'platform' => $filters['platform'] ?? null,
            'category' => $filters['category'] ?? null,
            'sort-by' => $filters['sort_by'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $items = $this->cached('games', $query, $this->cacheTtl('ttl', 43200), function () use ($query): ?array {
            $payload = $this->requestJson('/games', $query);
            $list = $this->validList($payload);
            return $list === null ? null : $this->normalizeMany($list);
        }, []);

        return $this->hydrateMany($items);
    }

    public function search(string $term): array
    {
        $needle = Str::lower(trim($term));
        if ($needle === '') {
            return [];
        }

        return array_values(array_filter(
            $this->freeToPlay(),
            static fn (GameData $game): bool => str_contains(Str::lower($game->title), $needle)
        ));
    }

    public function details(string $id): ?GameData
    {
        if (trim($id) === '') {
            return null;
        }

        $normalized = $this->cached('details', ['id' => $id], $this->cacheTtl('ttl', 43200), function () use ($id): ?array {
            $payload = $this->requestJson('/game', ['id' => $id]);
            if ($payload === null || ! isset($payload['id'], $payload['title'])) {
                return null;
            }

            return $this->normalize($payload);
        }, null);

        return is_array($normalized) ? $this->hydrateOne($normalized) : null;
    }

    private function normalizeMany(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['id'], $item['title'])) {
                $normalized[] = $this->normalize($item);
            }
        }
        return $normalized;
    }

    private function normalize(array $item): array
    {
        $providerId = (string) $item['id'];
        $title = (string) $item['title'];
        $screenshots = [];
        foreach (($item['screenshots'] ?? []) as $screenshot) {
            $image = is_array($screenshot) ? ($screenshot['image'] ?? null) : null;
            if (is_scalar($image) && trim((string) $image) !== '') {
                $screenshots[] = (string) $image;
            }
        }

        $platform = $this->nullableString($item['platform'] ?? null);

        return [
            'id' => "freetogame:{$providerId}",
            'provider' => $this->name(),
            'provider_id' => $providerId,
            'slug' => $this->slug(null, $title),
            'title' => $title,
            'description' => $this->nullableString($item['description'] ?? $item['short_description'] ?? null),
            'image' => $this->nullableString($item['thumbnail'] ?? null),
            'background_image' => $this->nullableString($item['thumbnail'] ?? null),
            'screenshots' => array_values(array_unique($screenshots)),
            'genres' => ($genre = $this->nullableString($item['genre'] ?? null)) ? [$genre] : [],
            'platforms' => $platform ? array_values(array_filter(array_map('trim', preg_split('/[,;]/', $platform) ?: []))) : [],
            'release_date' => $this->nullableString($item['release_date'] ?? null),
            'rating' => null,
            'developers' => ($developer = $this->nullableString($item['developer'] ?? null)) ? [$developer] : [],
            'publishers' => ($publisher = $this->nullableString($item['publisher'] ?? null)) ? [$publisher] : [],
            'stores' => [],
            'website' => $this->nullableString($item['game_url'] ?? $item['freetogame_profile_url'] ?? null),
            'is_free' => true,
            'giveaway' => null,
            'price' => 0.0,
            'normal_price' => null,
            'discount' => null,
            'deal_url' => $this->nullableString($item['game_url'] ?? null),
        ];
    }
}
