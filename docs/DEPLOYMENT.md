# Production Deployment

This document describes a conservative production deployment for the Laravel 12 application. It does not contain credential values.

## 1. Platform requirements

- PHP 8.2 or newer with the extensions required by Laravel and the selected database driver. CI currently validates PHP 8.4.
- Composer 2.
- Node.js 22 LTS and npm for deterministic frontend builds (`npm ci`).
- MySQL/MariaDB recommended for production.
- HTTPS termination at the web server, load balancer or reverse proxy.
- A persistent cache/session/queue backend. The supplied template uses Laravel's database stores.
- A process supervisor/systemd service for queue workers when `QUEUE_CONNECTION` is asynchronous.
- Cron access for Laravel Scheduler.

The web server document root must point to `public/`, never the repository root.

## 2. Environment and secrets

Copy `.env.production.example` to `.env` on the server, then populate values using your deployment secret manager or protected server configuration.

Never commit `.env`, database passwords, OAuth secrets, RAWG keys, mail credentials, Pusher secrets, casino halls/keys or provider tokens.

Required security posture:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database
FORCE_HTTPS=true
TRUSTED_HOSTS=^your-domain\.example$
HSTS_ENABLED=true
SANITIZE_PROVIDER_ERRORS=true
DEMO_RETENTION_HOURS=24
```

`TRUSTED_PROXIES` is intentionally empty by default. Set it only to proxy IPs/CIDRs you operate. Never use `*` unless the application is network-isolated behind a trusted proxy layer that prevents clients from connecting directly.

If TLS terminates at a reverse proxy, forward `X-Forwarded-Proto: https` and configure that proxy as trusted so Laravel generates HTTPS URLs correctly. The edge proxy should redirect plain HTTP to HTTPS before requests reach Laravel.

## 3. Initial deployment

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

Make `storage/` and `bootstrap/cache/` writable by the PHP worker user. Do not grant write access to the entire source tree.

### Route cache strategy

`php artisan route:list` is required in CI and deployment validation. Route caching is not enabled in this release because the preserved legacy route file still contains Closure routes. Route caching is an optional optimization, not a correctness requirement. Convert those closures to controller or framework redirect/view routes in a dedicated compatibility-tested maintenance change before enabling `php artisan route:cache`.

## 4. Scheduler

Install one scheduler entry per application deployment:

```cron
* * * * * cd /var/www/free-games && php artisan schedule:run >> /dev/null 2>&1
```

Review the registered schedule with:

```bash
php artisan schedule:list
```

State-changing maintenance tasks use `withoutOverlapping()` to avoid concurrent duplicate runs on one cache-lock domain. For a multi-server deployment, run the scheduler on one designated node or move to a shared lock backend and explicitly adopt Laravel `onOneServer()` after validating that topology.

The scheduler includes daily cleanup of expired demo accounts at 03:15. Retention is controlled by `DEMO_RETENTION_HOURS` and defaults to 24 hours.

Manual checks:

```bash
php artisan demo:cleanup --dry-run
php artisan demo:cleanup
```

Only users with `registration_type=demo` older than the cutoff are eligible. Database-session rows are deleted explicitly and a force delete triggers the existing personalization foreign-key cascades. Real users are never selected by the cleanup query.

## 5. Queue worker

With `QUEUE_CONNECTION=database`, keep at least one supervised worker available:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600
```

Example Supervisor program:

```ini
[program:free-games-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/free-games/artisan queue:work --sleep=3 --tries=3 --timeout=90 --max-time=3600
directory=/var/www/free-games
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/free-games-worker.log
```

After deploying new PHP code:

```bash
php artisan queue:restart
```

## 6. Cache and application warm-up

After migrations and before traffic is restored:

```bash
php artisan config:cache
php artisan view:cache
php artisan route:list > /dev/null
php artisan schedule:list > /dev/null
php artisan app:production-check
```

Do not run `php artisan cache:clear` as a routine deploy step on a live multi-node system unless you intentionally want to invalidate application/provider caches.

## 7. SEO and PWA

Public SEO endpoints:

- `/sitemap.xml`
- `/robots.txt`

Authenticated/account/admin/API routes receive `noindex` and `no-store` headers. Search results use `noindex,follow`. Game detail pages publish canonical/Open Graph/Twitter metadata and conservative `VideoGame` structured data.

The service worker caches only public static build assets, the manifest/logo and the offline fallback. Navigation requests are network-first and are never written to the cache. User, API, auth, admin and casino financial routes are explicitly excluded.

If `/manifest.json`, `/sw.js`, or icons are served by a CDN, preserve their content types and do not cache an old service worker indefinitely.

## 8. Casino compatibility review

The existing casino launch and provider callback surface uses `Route::any`, and the current regression test launches a game through GET query parameters. For Milestone 7 these routes remain unchanged to avoid breaking provider/browser compatibility.

Current compensating controls remain in place:

- authenticated/ban middleware where required;
- demo financial/casino mutation restriction;
- provider callback validation/idempotency from earlier milestones;
- provider exception text redaction before user-facing redirects;
- private responses marked `no-store`/`noindex`.

Changing launch routes to POST-only requires a separate migration of all UI forms/provider callback expectations and dedicated compatibility testing.

## 9. Validation before traffic

Run all of the following:

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

Also verify from the deployed edge:

- HTTPS redirect and certificate chain;
- `Strict-Transport-Security` on HTTPS responses when enabled;
- login/register/demo behavior;
- catalog/search/details;
- saved/history/preferences for a normal test user;
- casino navigation without executing real-value transactions;
- unauthenticated denial of financial/account pages;
- admin login/authorization;
- 404 and maintenance/error presentation;
- PWA offline fallback after one successful online load.

## 10. Deployment checklist

1. Snapshot/backup the production database using the database platform's native tooling.
2. Confirm the new release commit and dependency lock files.
3. Install dependencies and build assets in a release directory.
4. Run the automated suite and dependency audits.
5. Put the application into maintenance mode if the deployment method cannot switch releases atomically.
6. Run `php artisan migrate --force`.
7. Create/verify `storage:link`.
8. Cache config/views and run `app:production-check`.
9. Atomically switch the web root/symlink to the new release.
10. Restart PHP workers if required by the hosting stack.
11. Run `php artisan queue:restart`.
12. Verify scheduler, health endpoint `/up`, public catalog, auth and authorization smoke checks.
13. Exit maintenance mode and monitor application/worker logs.

## 11. Rollback

Prefer application-code rollback over destructive database rollback:

1. Put the site in maintenance mode if necessary.
2. Switch the release symlink/code back to the previously verified commit.
3. Run `composer install --no-dev --prefer-dist --optimize-autoloader` for that release if dependencies are not release-isolated.
4. Rebuild/use the previous frontend assets.
5. Run `php artisan config:cache` and `php artisan view:cache`.
6. Run `php artisan queue:restart`.
7. Bring the site back and verify `/up`, auth, catalog and financial authorization.

Do not automatically run `migrate:rollback` on production. Roll back a migration only after reviewing its `down()` path and confirming that reversing it will not delete or invalidate data. Milestone 7 itself adds no database migration.

## 12. Monitoring and logs

Use `LOG_LEVEL=warning` or an operationally appropriate production level. Do not log provider secrets, request authorization headers, session payloads or raw financial callback credentials. Provider-facing UI errors are redacted and the hardening middleware logs only a one-way error fingerprint plus path/user context.

Alert on repeated 500/503 responses, queue failures, scheduler failures, provider cache refresh failures and unexpected growth in demo-user counts.
