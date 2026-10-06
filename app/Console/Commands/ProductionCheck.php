<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class ProductionCheck extends Command
{
    protected $signature = 'app:production-check';

    protected $description = 'Validate security-critical production configuration without printing secrets.';

    public function handle(): int
    {
        $checks = [
            ['APP_ENV is production', (string) config('app.env') === 'production'],
            ['APP_DEBUG is disabled', config('app.debug') === false],
            ['APP_URL uses HTTPS', str_starts_with((string) config('app.url'), 'https://')],
            ['APP_KEY is configured', trim((string) config('app.key')) !== ''],
            ['Session cookies require HTTPS', config('session.secure') === true],
            ['Session cookies are HTTP-only', config('session.http_only') === true],
            ['Server-side session payload is encrypted', config('session.encrypt') === true],
            ['At least one trusted host is configured', config('security.trusted_hosts', []) !== []],
            ['Cache store is production-capable', ! in_array((string) config('cache.default'), ['array', 'null'], true)],
            ['Queue is asynchronous', (string) config('queue.default') !== 'sync'],
        ];

        $this->table(['Check', 'Result'], array_map(
            fn (array $check) => [$check[0], $check[1] ? 'PASS' : 'FAIL'],
            $checks
        ));

        if (collect($checks)->contains(fn (array $check) => $check[1] === false)) {
            $this->error('Production configuration is not ready. No secret values were printed.');
            return self::FAILURE;
        }

        $this->info('Production configuration checks passed.');
        return self::SUCCESS;
    }
}
