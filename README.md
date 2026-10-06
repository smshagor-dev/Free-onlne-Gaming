# Free Games

Laravel 12 + Blade gaming platform with a legacy free-game library, casino flows, multi-provider game catalog, user personalization, safe demo mode, financial controls, PWA support and production validation.

## Runtime requirements

- PHP **8.2+** (CI validates PHP 8.4)
- Composer 2
- Node.js **22 LTS** + npm
- MySQL/MariaDB for production (SQLite is supported for tests/local development)
- A persistent cache/session/queue backend; the supplied production template uses the database drivers
- HTTPS in production

## Game catalog providers

The public catalog supports RAWG, FreeToGame, GamerPower and CheapShark. Provider credentials and endpoints are read only from environment configuration. Do not commit API keys or casino credentials.

## Local installation

```bash
git clone https://github.com/smshagor-dev/Free-onlne-Gaming.git
cd Free-onlne-Gaming
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

For development assets use `npm run dev` instead of the production build.

## Production deployment

Start from the dedicated template rather than the local example:

```bash
cp .env.production.example .env
php artisan key:generate
```

Set the real database, mail, OAuth, catalog-provider and casino values through your deployment secret store. Keep these mandatory production security settings:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_ENCRYPT=true
FORCE_HTTPS=true
TRUSTED_HOSTS=^your-domain\.example$
```

Only add `TRUSTED_PROXIES` for reverse proxies/load balancers you actually control.

Before serving traffic, run:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan view:cache
php artisan app:production-check
```

The current legacy route file still contains a small number of Closure routes, so route caching is intentionally not part of the required deployment path. `php artisan route:list` remains the deployment validation gate. Convert those legacy closures to controllers before enabling `php artisan route:cache` in a future maintenance change.

Full production procedures, scheduler/queue configuration, reverse-proxy guidance, deployment checklist and rollback notes are in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Scheduler

The scheduler performs bonus/cashback/VIP/lottery maintenance, casino cache refreshes and expired demo-user cleanup. Run Laravel's scheduler once per minute from cron:

```cron
* * * * * cd /var/www/free-games && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled stateful tasks use `withoutOverlapping()` locks. Demo users are cleaned daily according to `DEMO_RETENTION_HOURS` (default 24). A dry run is available:

```bash
php artisan demo:cleanup --dry-run
```

## Queue worker

Production defaults to the database queue. Run a supervised worker, for example:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

Restart workers after each deployment:

```bash
php artisan queue:restart
```

## Production validation

```bash
php artisan test
php artisan route:list
php artisan schedule:list
php artisan app:production-check
composer audit
npm audit
npm run build
find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l
```

`app:production-check` validates production/debug/HTTPS/session/trusted-host/cache/queue configuration without printing secret values.

## Security and data isolation

- Financial and casino callbacks retain the existing ownership/idempotency protections.
- Demo users have normal-user permissions only and cannot perform financial/casino mutations.
- Demo data is isolated per generated account and old demo accounts are hard-deleted after the retention window; related catalog personalization records cascade and database-session rows are explicitly removed.
- Catalog saved/history identity is scoped by `user_id + provider + provider_game_id`.
- Private/authenticated pages send `no-store` and `noindex` response headers.
- Production errors use generic 403/404/419/429/500/503 pages; casino provider exception text is never shown directly to users.
- The PWA service worker caches public static assets only and never stores authenticated, API, admin or financial pages.

## Casino route compatibility

Casino launch and provider callback routes currently retain their existing `Route::any`/GET compatibility. The regression suite proves the current launch flow uses GET query parameters, so narrowing methods in this hardening release would be a breaking behavioral change. Do not convert these routes to POST-only until all browser/provider callback contracts have been audited and migrated together.

## Tests

The feature suite covers authentication/security, financial ownership, casino callback idempotency, catalog providers, catalog UI, personalization/demo isolation and production hardening.

Pull requests into `main` run the complete backend validation workflow and must pass before merge.
