<?php

namespace App\GameProviders;

use App\Data\GameData;

final class RawgProvider extends AbstractGameProvider
{
    public function name(): string
    {
        return 'rawg';
    }

    protected function configKey(): string
    {
        return 'rawg';
    }

    public function discover(array $filters = []): array
    {
        if (! $this->configured()) {
            return [];
        }

        $query = $this->listQuery($filters);
        $items = $this->cached('discover', $query, $this->cacheTtl('list_ttl', 21600), function () use ($query): ?array {
            $payload = $this->requestJson('/games', $this->withKey($query));
            $results = $payload['results'] ?? null;
            if (! is_array($results) || ! array_is_list($results)) {
                return null;
            }

            return $this->normalizeMany($results);
        }, []);

        return $this->hydrateMany($items);
    }

    public function search(string $term, array $filters = []): array
    {
        if (! $this->configured()) {
            return [];
        }

        $query = $this->listQuery($filters);
        $query['search'] = trim($term);
        $query['search_precise'] = 'true';

        $items = $this->cached('search', $query, $this->searchTtl(), function () use ($query): ?array {
            $payload = $this->requestJson('/games', $this->withKey($query));
            $results = $payload['results'] ?? null;
            if (! is_array($results) || ! array_is_list($results)) {
                return null;
            }

            return $this->normalizeMany($results);
        }, []);

        return $this->hydrateMany($items);
    }

    public function details(string $id): ?GameData
    {
        if (! $this->configured() || trim($id) === '') {
            return null;
        }

        $normalized = $this->cached('details', ['id' => $id], $this->cacheTtl('detail_ttl', 43200), function () use ($id): ?array {
            $payload = $this->requestJson('/games/'.rawurlencode($id), $this->withKey([]));
            if ($payload === null || ! isset($payload['id'], $payload['name'])) {
                return null;
            }

            return $this->normalize($payload);
        }, null);

        return is_array($normalized) ? $this->hydrateOne($normalized) : null;
    }

    public function genres(): array
    {
        return $this->metadata('/genres', 'genres');
    }

    public function platforms(): array
    {
        return $this->metadata('/platforms', 'platforms');
    }

    private function metadata(string $path, string $scope): array
    {
        if (! $this->configured()) {
            return [];
        }

        return $this->cached($scope, [], $this->cacheTtl('list_ttl', 21600), function () use ($path): ?array {
            $payload = $this->requestJson($path, $this->withKey(['page_size' => 100]));
            $results = $payload['results'] ?? null;
            if (! is_array($results) || ! array_is_list($results)) {
                return null;
            }

            return array_values(array_filter(array_map(static function (mixed $item): ?array {
                if (! is_array($item) || ! isset($item['id'], $item['name'])) {
                    return null;
                }

                return [
                    'id' => (string) $item['id'],
                    'slug' => isset($item['slug']) && is_scalar($item['slug']) ? (string) $item['slug'] : null,
                    'name' => (string) $item['name'],
                ];
            }, $results)));
        }, []);
    }

    private function normalizeMany(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (is_array($item) && isset($item['id'], $item['name'])) {
                $normalized[] = $this->normalize($item);
            }
        }

        return $normalized;
    }

    private function normalize(array $item): array
    {
        $providerId = (string) $item['id'];
        $title = (string) $item['name'];
        $platforms = [];
        foreach (($item['platforms'] ?? []) as $platform) {
            $name = is_array($platform) ? ($platform['platform']['name'] ?? null) : null;
            if (is_scalar($name) && trim((string) $name) !== '') {
                $platforms[] = trim((string) $name);
            }
        }

        $stores = [];
        foreach (($item['stores'] ?? []) as $store) {
            $name = is_array($store) ? ($store['store']['name'] ?? null) : null;
            if (is_scalar($name) && trim((string) $name) !== '') {
                $stores[] = trim((string) $name);
            }
        }

        $screenshots = [];
        foreach (($item['short_screenshots'] ?? $item['screenshots'] ?? []) as $screenshot) {
            $image = is_array($screenshot) ? ($screenshot['image'] ?? null) : null;
            if (is_scalar($image) && trim((string) $image) !== '') {
                $screenshots[] = (string) $image;
            }
        }

        return [
            'id' => "rawg:{$providerId}",
            'provider' => $this->name(),
            'provider_id' => $providerId,
            'slug' => $this->slug($item['slug'] ?? null, $title),
            'title' => $title,
            'description' => $this->nullableString($item['description_raw'] ?? $item['description'] ?? null),
            'image' => $this->nullableString($item['background_image'] ?? null),
            'background_image' => $this->nullableString($item['background_image_additional'] ?? $item['background_image'] ?? null),
            'screenshots' => array_values(array_unique($screenshots)),
            'genres' => $this->names($item['genres'] ?? []),
            'platforms' => array_values(array_unique($platforms)),
            'release_date' => $this->nullableString($item['released'] ?? null),
            'rating' => $this->nullableFloat($item['rating'] ?? null),
            'developers' => $this->names($item['developers'] ?? []),
            'publishers' => $this->names($item['publishers'] ?? []),
            'stores' => array_values(array_unique($stores)),
            'website' => $this->nullableString($item['website'] ?? null),
            'is_free' => false,
            'giveaway' => null,
            'price' => null,
            'normal_price' => null,
            'discount' => null,
            'deal_url' => null,
        ];
    }

    private function listQuery(array $filters): array
    {
        return array_filter([
            'page' => isset($filters['page']) ? max(1, (int) $filters['page']) : 1,
            'page_size' => isset($filters['page_size']) ? min(40, max(1, (int) $filters['page_size'])) : 20,
            'ordering' => $filters['ordering'] ?? null,
            'genres' => $filters['genres'] ?? null,
            'platforms' => $filters['platforms'] ?? null,
            'dates' => $filters['dates'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function withKey(array $query): array
    {
        $query['key'] = (string) config('games.rawg.key');
        return $query;
    }

    private function configured(): bool
    {
        return trim((string) config('games.rawg.key')) !== '';
    }
}
