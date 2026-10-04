<?php

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class GameData implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $provider,
        public readonly string $providerId,
        public readonly ?string $slug,
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $image = null,
        public readonly ?string $backgroundImage = null,
        public readonly array $screenshots = [],
        public readonly array $genres = [],
        public readonly array $platforms = [],
        public readonly ?string $releaseDate = null,
        public readonly ?float $rating = null,
        public readonly array $developers = [],
        public readonly array $publishers = [],
        public readonly array $stores = [],
        public readonly ?string $website = null,
        public readonly bool $isFree = false,
        public readonly ?array $giveaway = null,
        public readonly ?float $price = null,
        public readonly ?float $normalPrice = null,
        public readonly ?float $discount = null,
        public readonly ?string $dealUrl = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            provider: (string) ($data['provider'] ?? ''),
            providerId: (string) ($data['provider_id'] ?? ''),
            slug: self::nullableString($data['slug'] ?? null),
            title: (string) ($data['title'] ?? ''),
            description: self::nullableString($data['description'] ?? null),
            image: self::nullableString($data['image'] ?? null),
            backgroundImage: self::nullableString($data['background_image'] ?? null),
            screenshots: self::arrayValue($data['screenshots'] ?? []),
            genres: self::arrayValue($data['genres'] ?? []),
            platforms: self::arrayValue($data['platforms'] ?? []),
            releaseDate: self::nullableString($data['release_date'] ?? null),
            rating: self::nullableFloat($data['rating'] ?? null),
            developers: self::arrayValue($data['developers'] ?? []),
            publishers: self::arrayValue($data['publishers'] ?? []),
            stores: self::arrayValue($data['stores'] ?? []),
            website: self::nullableString($data['website'] ?? null),
            isFree: (bool) ($data['is_free'] ?? false),
            giveaway: is_array($data['giveaway'] ?? null) ? $data['giveaway'] : null,
            price: self::nullableFloat($data['price'] ?? null),
            normalPrice: self::nullableFloat($data['normal_price'] ?? null),
            discount: self::nullableFloat($data['discount'] ?? null),
            dealUrl: self::nullableString($data['deal_url'] ?? null),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_id' => $this->providerId,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'background_image' => $this->backgroundImage,
            'screenshots' => $this->screenshots,
            'genres' => $this->genres,
            'platforms' => $this->platforms,
            'release_date' => $this->releaseDate,
            'rating' => $this->rating,
            'developers' => $this->developers,
            'publishers' => $this->publishers,
            'stores' => $this->stores,
            'website' => $this->website,
            'is_free' => $this->isFree,
            'giveaway' => $this->giveaway,
            'price' => $this->price,
            'normal_price' => $this->normalPrice,
            'discount' => $this->discount,
            'deal_url' => $this->dealUrl,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_scalar($value) ? (string) $value : null;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function arrayValue(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }
}
