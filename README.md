# Free Games

Laravel 12 + Blade application for free games, catalog discovery, casino provider integration, bonuses, payments, demo mode, and admin operations.

## Requirements

- PHP 8.2 or newer with common Laravel extensions
- Composer 2
- Node.js 20 or newer and npm
- MySQL 8 or a compatible database for production
- A process manager for queues
- Cron access for Laravel Scheduler

## Environment

Never commit `.env`, provider credentials, app keys, OAuth secrets, mail passwords, casino credentials, or database passwords.

Required production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=base64:...

TRUSTED_PROXIES=REMOTE_ADDR
TRUSTED_HOSTS=your-domain.example,www.your-domain.example

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stack
LOG_LEVEL=warning

DEMO_RETENTION_DAYS=7

RAWG_API_KEY=
RAWG_BASE_URL=https://api.rawg.io/api
FREE_TO_GAME_BASE_URL=https://www.freetogame.com/api
GAMERPOWER_BASE_URL=https://www.gamerpower.com/api
CHEAPSHARK_BASE_URL=https://www.cheapshark.com/api/1.0

CASINO_API_HOST=
CASINO_CDN_URL=https://static.cdns-stat.com/resources/
CASINO_DOMAIN="${APP_URL}"
CASINO_CURRENCY=BDT
CASINO_HALL=
CASINO_KEY=
CASINO_BONUS_HALL=
CASINO_BONUS_KEY=
CASINO_CASHBACK_HALL=
CASINO_CASHBACK_KEY=
CASINO_VIP_BONUS_HALL=
CASINO_VIP_BONUS_KEY=
```

Use real secret values only in the deployment environment.

## Install

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
```

For local development:

```bash
composer install
npm install
php artisan migrate
composer run dev
```

## Production Cache

Run these during deployment after environment variables are present:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

After changing `.env`, config files, or routes, rerun the relevant cache commands.

## Scheduler

Install one cron entry:

```cron
* * * * * cd /path/to/free-games && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs are defined in `routes/console.php`. Jobs that can overlap are protected with `withoutOverlapping()`, including provider cache refreshes and demo cleanup.

Demo cleanup:

```bash
php artisan demo:cleanup
php artisan demo:cleanup --days=14
php artisan demo:cleanup --dry-run
```

Only users with `registration_type=demo` older than `DEMO_RETENTION_DAYS` are force-deleted. Session rows are removed explicitly; personalization and related user-owned records are removed by database cascades.

## Queue Worker

Use a supervised worker in production:

```bash
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
```

Restart workers after each deployment:

```bash
php artisan queue:restart
```

## PWA And SEO

- Manifest: `public/manifest.json`
- Service worker: `public/sw.js`
- Offline fallback: `public/offline.html`
- Sitemap: `/sitemap.xml`
- Robots: `public/robots.txt`

The service worker avoids authenticated, private, API, and financial/casino launch paths. Account/admin/demo pages are marked `noindex`.

## Security Notes

- Keep `APP_DEBUG=false` in production.
- Serve the app only over HTTPS.
- Configure `TRUSTED_PROXIES` and `TRUSTED_HOSTS` for the load balancer/domain.
- Casino callback and launch routes intentionally keep broad method compatibility because providers and existing UI links depend on it. Do not narrow `Route::any` without provider verification.
- `.env`, `.env.production`, `auth.json`, vendor directories, node modules, build output, and logs are ignored.

## Validation

Run before release:

```bash
php artisan test
php artisan route:list
composer audit
npm audit
npm run build
php -l app/Console/Commands/CleanupDemoUsers.php
```

For a full PHP lint pass on PowerShell:

```powershell
Get-ChildItem -Recurse -Include *.php -Path app,bootstrap,config,database,routes,tests |
    ForEach-Object { php -l $_.FullName }
```

## Browser Smoke Checks

Before promoting a release, verify:

- Desktop and mobile catalog pages load
- Register, login, logout, and demo login work
- Catalog search, details, save, history, and preferences work
- Casino listing and launch navigation still work with valid provider config
- Financial pages require authentication and block demo users
- Admin login and core read pages load
- 403, 404, 419, 429, 500, and 503 pages are polished and hide internals

## Deployment Checklist

1. Pull the release commit.
2. Install dependencies with production flags.
3. Build assets.
4. Put the app in maintenance mode if required.
5. Run migrations with `--force`.
6. Refresh config, route, view, and event caches.
7. Restart PHP-FPM/opcache if used.
8. Restart queue workers.
9. Confirm scheduler cron is active.
10. Smoke test public, auth, catalog, casino, financial authorization, and admin flows.
11. Bring the app out of maintenance mode.

## Rollback

1. Put the app in maintenance mode.
2. Deploy the previous release artifact or commit.
3. Restore the previous `.env` if changed.
4. Run `php artisan optimize:clear`, then rebuild production caches.
5. Restart PHP-FPM/opcache and queue workers.
6. If a migration needs rollback, use a tested database backup or a specific migration rollback plan. Do not run destructive rollback commands blindly on production financial data.
