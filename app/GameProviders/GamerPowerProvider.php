<?php

namespace App\GameProviders;

use App\Data\GameData;
use Illuminate\Support\Str;

final class GamerPowerProvider extends AbstractGameProvider
{
    public function name(): string
    {
        return 'gamerpower';
    }

    protected function configKey(): string
    {
        return 'gamerpower';
    }

    public function giveaways(array $filters = []): array
    {
        $query = array_filter([
            'platform' => $filters['platform'] ?? null,
            'type' => $filters['type'] ?? null,
            'sort-by' => $filters['sort_by'] ?? null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $items = $this->cached('giveaways', $query, $this->cacheTtl('ttl', 1200), function () use ($query): ?array {
            $payload = $this->requestJson('/giveaways', $query);
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
            $this->giveaways(),
            static fn (GameData $game): bool => str_contains(Str::lower($game->title), $needle)
        ));
    }

    public function details(string $id): ?GameData
    {
        if (trim($id) === '') {
            return null;
        }

        $normalized = $this->cached('details', ['id' => $id], $this->cacheTtl('ttl', 1200), function () use ($id): ?array {
            $payload = $this->requestJson('/giveaway', ['id' => $id]);
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
        $worth = $this->parsePrice($item['worth'] ?? null);
        $platforms = [];
        $platformValue = $this->nullableString($item['platforms'] ?? null);
        if ($platformValue !== null) {
            $platforms = array_values(array_filter(array_map('trim', preg_split('/[,;]/', $platformValue) ?: [])));
        }

        return [
            'id' => "gamerpower:{$providerId}",
            'provider' => $this->name(),
            'provider_id' => $providerId,
            'slug' => $this->slug(null, $title),
            'title' => $title,
            'description' => $this->nullableString($item['description'] ?? null),
            'image' => $this->nullableString($item['thumbnail'] ?? $item['image'] ?? null),
            'background_image' => $this->nullableString($item['image'] ?? $item['thumbnail'] ?? null),
            'screenshots' => [],
            'genres' => ($type = $this->nullableString($item['type'] ?? null)) ? [$type] : [],
            'platforms' => $platforms,
            'release_date' => null,
            'rating' => null,
            'developers' => [],
            'publishers' => [],
            'stores' => [],
            'website' => $this->nullableString($item['gamerpower_url'] ?? null),
            'is_free' => true,
            'giveaway' => [
                'status' => $this->nullableString($item['status'] ?? null),
                'type' => $this->nullableString($item['type'] ?? null),
                'published_date' => $this->nullableString($item['published_date'] ?? null),
                'end_date' => $this->nullableString($item['end_date'] ?? null),
                'instructions' => $this->nullableString($item['instructions'] ?? null),
            ],
            'price' => 0.0,
            'normal_price' => $worth,
            'discount' => $worth !== null && $worth > 0 ? 100.0 : null,
            'deal_url' => $this->nullableString($item['open_giveaway_url'] ?? null),
        ];
    }
}
