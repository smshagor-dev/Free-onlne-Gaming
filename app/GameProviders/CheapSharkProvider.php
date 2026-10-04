<?php

namespace App\GameProviders;

use App\Data\GameData;
use Carbon\CarbonImmutable;

final class CheapSharkProvider extends AbstractGameProvider
{
    public function name(): string
    {
        return 'cheapshark';
    }

    protected function configKey(): string
    {
        return 'cheapshark';
    }

    public function deals(array $filters = []): array
    {
        $query = array_filter([
            'storeID' => $filters['store_id'] ?? null,
            'pageNumber' => $filters['page'] ?? null,
            'pageSize' => isset($filters['page_size']) ? min(60, max(1, (int) $filters['page_size'])) : 20,
            'sortBy' => $filters['sort_by'] ?? null,
            'desc' => isset($filters['desc']) ? ((bool) $filters['desc'] ? 1 : 0) : null,
            'lowerPrice' => $filters['lower_price'] ?? null,
            'upperPrice' => $filters['upper_price'] ?? null,
            'title' => $filters['title'] ?? null,
            'exact' => isset($filters['exact']) ? ((bool) $filters['exact'] ? 1 : 0) : null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        $items = $this->cached('deals', $query, $this->cacheTtl('ttl', 1200), function () use ($query): ?array {
            $payload = $this->requestJson('/deals', $query);
            $list = $this->validList($payload);
            return $list === null ? null : $this->normalizeDeals($list);
        }, []);

        return $this->hydrateMany($items);
    }

    public function search(string $term): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $identity = ['title' => $term, 'limit' => 20];
        $items = $this->cached('search', $identity, $this->searchTtl(), function () use ($identity): ?array {
            $payload = $this->requestJson('/games', $identity);
            $list = $this->validList($payload);
            return $list === null ? null : $this->normalizeSearchResults($list);
        }, []);

        return $this->hydrateMany($items);
    }

    public function details(string $id): ?GameData
    {
        if (trim($id) === '') {
            return null;
        }

        $normalized = $this->cached('details', ['id' => $id], $this->cacheTtl('ttl', 1200), function () use ($id): ?array {
            $payload = $this->requestJson('/games', ['id' => $id]);
            if ($payload === null || ! isset($payload['info']) || ! is_array($payload['info'])) {
                return null;
            }

            return $this->normalizeGameDetail($id, $payload);
        }, null);

        return is_array($normalized) ? $this->hydrateOne($normalized) : null;
    }

    private function normalizeDeals(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['dealID'], $item['title'])) {
                continue;
            }

            $dealId = (string) $item['dealID'];
            $gameId = isset($item['gameID']) && is_scalar($item['gameID']) ? (string) $item['gameID'] : $dealId;
            $title = (string) $item['title'];
            $sale = $this->nullableFloat($item['salePrice'] ?? null);
            $normal = $this->nullableFloat($item['normalPrice'] ?? null);
            $releaseDate = null;
            if (isset($item['releaseDate']) && is_numeric($item['releaseDate']) && (int) $item['releaseDate'] > 0) {
                $releaseDate = CarbonImmutable::createFromTimestampUTC((int) $item['releaseDate'])->toDateString();
            }

            $normalized[] = [
                'id' => "cheapshark:deal:{$dealId}",
                'provider' => $this->name(),
                'provider_id' => $gameId,
                'slug' => $this->slug(null, $title),
                'title' => $title,
                'description' => null,
                'image' => $this->nullableString($item['thumb'] ?? null),
                'background_image' => null,
                'screenshots' => [],
                'genres' => [],
                'platforms' => [],
                'release_date' => $releaseDate,
                'rating' => $this->nullableFloat($item['metacriticScore'] ?? null),
                'developers' => [],
                'publishers' => [],
                'stores' => ($store = $this->nullableString($item['storeID'] ?? null)) ? [$store] : [],
                'website' => null,
                'is_free' => $sale !== null && $sale <= 0.0,
                'giveaway' => null,
                'price' => $sale,
                'normal_price' => $normal,
                'discount' => $this->nullableFloat($item['savings'] ?? null),
                'deal_url' => $this->dealUrl($dealId),
            ];
        }
        return $normalized;
    }

    private function normalizeSearchResults(array $items): array
    {
        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['gameID'], $item['external'])) {
                continue;
            }

            $gameId = (string) $item['gameID'];
            $title = (string) $item['external'];
            $price = $this->nullableFloat($item['cheapest'] ?? null);
            $dealId = $this->nullableString($item['cheapestDealID'] ?? null);
            $normalized[] = [
                'id' => "cheapshark:{$gameId}",
                'provider' => $this->name(),
                'provider_id' => $gameId,
                'slug' => $this->slug(null, $title),
                'title' => $title,
                'description' => null,
                'image' => $this->nullableString($item['thumb'] ?? null),
                'background_image' => null,
                'screenshots' => [],
                'genres' => [],
                'platforms' => [],
                'release_date' => null,
                'rating' => null,
                'developers' => [],
                'publishers' => [],
                'stores' => [],
                'website' => null,
                'is_free' => $price !== null && $price <= 0.0,
                'giveaway' => null,
                'price' => $price,
                'normal_price' => null,
                'discount' => null,
                'deal_url' => $dealId ? $this->dealUrl($dealId) : null,
            ];
        }
        return $normalized;
    }

    private function normalizeGameDetail(string $gameId, array $payload): array
    {
        $info = $payload['info'];
        $title = (string) ($info['title'] ?? '');
        $deals = is_array($payload['deals'] ?? null) ? $payload['deals'] : [];
        $firstDeal = is_array($deals[0] ?? null) ? $deals[0] : [];
        $price = $this->nullableFloat($firstDeal['price'] ?? null);
        $normal = $this->nullableFloat($firstDeal['retailPrice'] ?? null);
        $dealId = $this->nullableString($firstDeal['dealID'] ?? null);
        $stores = [];
        foreach ($deals as $deal) {
            $store = is_array($deal) ? $this->nullableString($deal['storeID'] ?? null) : null;
            if ($store !== null) {
                $stores[] = $store;
            }
        }

        return [
            'id' => "cheapshark:{$gameId}",
            'provider' => $this->name(),
            'provider_id' => $gameId,
            'slug' => $this->slug(null, $title),
            'title' => $title,
            'description' => null,
            'image' => $this->nullableString($info['thumb'] ?? null),
            'background_image' => null,
            'screenshots' => [],
            'genres' => [],
            'platforms' => [],
            'release_date' => null,
            'rating' => null,
            'developers' => [],
            'publishers' => [],
            'stores' => array_values(array_unique($stores)),
            'website' => null,
            'is_free' => $price !== null && $price <= 0.0,
            'giveaway' => null,
            'price' => $price,
            'normal_price' => $normal,
            'discount' => $normal !== null && $normal > 0 && $price !== null ? round((1 - ($price / $normal)) * 100, 2) : null,
            'deal_url' => $dealId ? $this->dealUrl($dealId) : null,
        ];
    }

    private function dealUrl(string $dealId): string
    {
        return rtrim((string) config('games.cheapshark.web_url', 'https://www.cheapshark.com'), '/').'/redirect?dealID='.rawurlencode($dealId);
    }
}
