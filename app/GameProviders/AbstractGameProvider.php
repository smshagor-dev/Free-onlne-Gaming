<?php

namespace App\GameProviders;

use App\Data\GameData;
use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

abstract class AbstractGameProvider
{
    abstract public function name(): string;

    abstract protected function configKey(): string;

    protected function requestJson(string $path, array $query = []): ?array
    {
        $attempts = 2;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $response = Http::acceptJson()
                    ->connectTimeout($this->connectTimeout())
                    ->timeout($this->timeout())
                    ->get($this->url($path), $query);
            } catch (ConnectionException $exception) {
                if ($attempt < $attempts) {
                    usleep(100_000);
                    continue;
                }

                $this->logFailure('connection', null, $path, $exception);
                return null;
            } catch (Throwable $exception) {
                $this->logFailure('exception', null, $path, $exception);
                return null;
            }

            if ($response->successful()) {
                $json = $response->json();

                if (! is_array($json)) {
                    $this->logFailure('malformed', $response->status(), $path);
                    return null;
                }

                return $json;
            }

            if ($response->serverError() && $attempt < $attempts) {
                usleep(100_000);
                continue;
            }

            $this->logFailure('http', $response->status(), $path);
            return null;
        }

        return null;
    }

    protected function cached(string $scope, array $identity, int $ttl, Closure $loader, mixed $fallback): mixed
    {
        $key = $this->cacheKey($scope, $identity);

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $value = $loader();

        if ($value !== null) {
            Cache::put($key, $value, now()->addSeconds($ttl));
            Cache::put($key.':stale', $value, now()->addSeconds(max($this->staleTtl(), $ttl * 2)));

            return $value;
        }

        return Cache::get($key.':stale', $fallback);
    }

    protected function hydrateMany(array $items): array
    {
        return array_values(array_map(
            static fn (array $item): GameData => GameData::fromArray($item),
            array_filter($items, 'is_array')
        ));
    }

    protected function hydrateOne(?array $item): ?GameData
    {
        return $item === null ? null : GameData::fromArray($item);
    }

    protected function validList(mixed $payload): ?array
    {
        return is_array($payload) && array_is_list($payload) ? $payload : null;
    }

    protected function names(mixed $items, string $key = 'name'): array
    {
        if (! is_array($items)) {
            return [];
        }

        $values = [];
        foreach ($items as $item) {
            $value = is_array($item) ? ($item[$key] ?? null) : null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                $values[] = trim((string) $value);
            }
        }

        return array_values(array_unique($values));
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    protected function nullableFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    protected function slug(mixed $value, string $title): ?string
    {
        $slug = $this->nullableString($value);
        return $slug ?? (($generated = Str::slug($title)) !== '' ? $generated : null);
    }

    protected function parsePrice(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = preg_replace('/[^0-9.]+/', '', $value);
        return $normalized !== null && is_numeric($normalized) ? (float) $normalized : null;
    }

    protected function cacheTtl(string $key, int $default): int
    {
        return max(1, (int) config("games.{$this->configKey()}.{$key}", $default));
    }

    protected function searchTtl(): int
    {
        return max(1, (int) config('games.search_ttl', 3600));
    }

    private function cacheKey(string $scope, array $identity): string
    {
        ksort($identity);
        $fingerprint = hash('sha256', json_encode($identity, JSON_UNESCAPED_SLASHES) ?: '[]');

        return sprintf('%s:%s:%s:%s', config('games.cache_prefix', 'game-providers'), $this->name(), $scope, $fingerprint);
    }

    private function url(string $path): string
    {
        return rtrim((string) config("games.{$this->configKey()}.base_url"), '/').'/'.ltrim($path, '/');
    }

    private function timeout(): int
    {
        return max(1, (int) config("games.{$this->configKey()}.timeout", 8));
    }

    private function connectTimeout(): int
    {
        return max(1, (int) config("games.{$this->configKey()}.connect_timeout", 3));
    }

    private function staleTtl(): int
    {
        return max(60, (int) config('games.stale_ttl', 86400));
    }

    private function logFailure(string $type, ?int $status, string $path, ?Throwable $exception = null): void
    {
        Log::warning('Game provider request failed', array_filter([
            'provider' => $this->name(),
            'type' => $type,
            'status' => $status,
            'path' => $path,
            'exception' => $exception ? $exception::class : null,
        ], static fn (mixed $value): bool => $value !== null));
    }
}
