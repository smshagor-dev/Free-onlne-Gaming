# FreeGames mobile API v1

Base URL: `https://YOUR_DOMAIN/api/v1`. Android, iOS, Flutter and React Native clients use JSON and bearer tokens. The existing website and legacy `/api/games` endpoints remain compatible. New mobile routes always return JSON, even without an Accept header.

Import `openapi.json` into Swagger Editor and `postman.json` into Postman. Replace the local base URL and example IDs. Postman saves a successful login token into `access_token`; use separate actual device/ticket/notification IDs for those requests.

## Deploy

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan test
```

Use HTTPS, `APP_DEBUG=false`, correct `APP_URL`, trusted host/proxy configuration and working SMTP. Email verification and password recovery require mail delivery. Use a shared persistent Redis/database cache across production instances: verification challenges, 2FA setup and OTP replay checks live in cache. Run Laravel's scheduler each minute; expired mobile tokens are pruned daily. No new paid API or package dependency is required.

Do not run `migrate:fresh` on an existing database. The migration adds two tables and does not remove historical data.

## Authentication

1. `POST /auth/register`: name, username, email, password, password_confirmation. Returns 201 without an access token. Emails a six-digit verification code valid for ten minutes.
2. `POST /auth/verify-email`: email, code. Successful verification consumes the challenge. Resend with `/auth/resend-verification`.
3. `POST /auth/login`: email, password, device_name; add otp if existing account 2FA is enabled. Returns `data.access_token`, `token_type`, `expires_at` and a safe user profile. Never returns password hashes or 2FA secrets.
4. Send `Authorization: Bearer TOKEN` on private endpoints. No cookies or CSRF token are needed. Store tokens in Android Keystore/iOS Keychain-backed secure storage.
5. Tokens expire after 30 days. `/auth/refresh` atomically rotates the current token; replace it in secure storage. `/auth/logout` revokes the current device; `/auth/logout-all` revokes all mobile devices. Device listing/revocation is under `/me/devices`; `current` identifies the requesting session.
6. Password changes/resets and 2FA changes revoke mobile sessions. Tokens also detect password/2FA changes made through the website. A revoked, expired or security-invalidated token returns 401; clear it and prompt login. Restricted/unverified accounts return 403.

Tokens are random 256-bit values stored as SHA-256 hashes. Login validates existing 2FA and rejects reuse of the same authenticator code for two minutes. Account creation does not automatically authenticate the player.

Password reset: `/auth/forgot-password` sends the existing Laravel broker reset link with a generic response. `/auth/reset-password` accepts email, token from the email link, password and password_confirmation. Tokens are one-use and obey the existing broker expiration. Browser-based completion also invalidates mobile access through password fingerprint checking.

2FA setup requires the current password and returns a temporary secret/otpauth URL for an authenticator QR. `/me/2fa/enable` verifies otp; `/me/2fa/disable` requires current_password and otp. Both require a fresh login afterward. Never log or cache the setup secret in the app. `/me` DELETE requires current_password and otp when 2FA is enabled; it soft-deletes the account, revokes mobile sessions and clears mobile game state. Historical website/lottery records remain retained; this is account closure, not complete personal-data erasure.

## Games and play

`GET /games` defaults to GameMonetize browser games. Choose provider=gamemonetize|local|freetogame|gamerpower. Search with q and category; pagination defaults to 30 and never exceeds 30.

```json
{"data": [], "meta": {"page": 1, "per_page": 30, "total": 65, "has_more": true, "next_page": 2}}
```

For infinite scrolling, request `meta.next_page` and stop when has_more=false. Deduplicate by `provider + id`, not title. `GET /categories` lists browser-game categories. `GET /games/{provider}/{id}` resolves details. IDs are provider-native: use `provider_id` for catalog providers (v1 also normalizes their `id` to that same value).

GameMonetize returns playable=true and an allowlisted HTTPS play_url. Load it in an isolated WebView: do not inject access tokens, expose native bridges to third-party scripts, or forward app credentials to games. Respect provider ads and distribution terms; test Android/iOS WebView audio, orientation and full screen on devices.

FreeToGame/GamerPower are catalog/download/giveaway entries, not embeddable games. They return playable=false and play_url=null. Use their safe detail links to view offers externally. Local legacy games also return playable=false: their website player requires a browser session, and an API bearer token does not create that session. No token-to-cookie bridge is provided.

Provider failures may return an empty cached catalog. Unknown/unavailable details return 404. Casino/gambling titles/categories are excluded. GamePix and GameDistribution are not implemented in this API version.

## Personalization

- `GET /me/dashboard`: current player, favorite count, mobile browser play count, lottery ticket/win count and 2FA status.
- `PUT /me/games/{provider}/{id}/favorite`: save; DELETE removes it. Writes are idempotent. GET `/me/favorites` returns provider/game_id plus cached `snapshot` for cards and pagination metadata.
- Website local Favorites and catalog My Library are visible in mobile favorites. Saving/removing those providers in the app updates the corresponding website collections. Browser favorites use the new mobile state table; the website has no browser-favorites screen yet.
- `POST /me/games/gamemonetize/{id}/played`: record browser play; GET `/me/recent` returns distinct mobile browser games ordered by last play. DELETE clears mobile browser history. Legacy website local-game history stays separate and is preserved.
- GET/PUT `/me/preferences` use the same website gaming preferences. PUT requires preferred_genres and preferred_platforms arrays (max 20 each).

Favorite snapshots may become stale when a provider removes a game; resolve details before playing and handle 404. Favorite lists preserve server-known snapshots and do not make one external provider request per item.

## Account, notifications and lottery

`GET/PATCH /me` reads/edits safe account fields (name, country, mobile_number). Safe user responses also return mobile_number. Email cannot be changed through this endpoint. No balance or privilege field can be edited. PUT `/me/password` requires current_password and confirmed new password.

`GET /me/notifications`, PATCH `/me/notifications/{id}/read`, and PATCH `/me/notifications/read-all` operate only on the authenticated user's notifications.

Authenticated lottery endpoints: GET `/lotteries`, `/me/lottery/tickets`, `/me/lottery/tickets/{id}`, `/me/lottery/wins`. Lottery purchases remain in the existing website flow; v1 is read-only for lottery. No deposits, withdrawals, casino, betting or payment endpoints are provided. `/config` advertises these capabilities explicitly.

Games/favorites/recent use `data` + `meta` pagination. Notifications/lotteries/tickets/wins use Laravel paginator JSON: `data`, `current_page`, `last_page`, `per_page`, `total`, `next_page_url`. Each page contains at most 30 items.

## Errors and limits

422: `{"message":"...","errors":{"field":["..."]}}`; 401 invalid authentication; 403 restricted/unverified account; 404 missing resource or wrong owner; 409 incompatible current 2FA state; 429 rate limit. Respect Retry-After.

General v1 limit: 120 requests/minute/IP. Auth: 30/minute/IP and 5/minute per endpoint/email. Verification resend/password-reset mail: 2/minute/IP/endpoint. Sensitive account actions: 5/minute/IP/endpoint. Legacy APIs retain their existing policies.

## Local checks

`php artisan test --filter=MobileApiTest` exercises hashing/rotation/revocation, JSON failures, registration verification, expiry/security changes, 2FA/replay protection, pagination/casino filtering, user isolation, mass assignment protection, preferences, password-reset invalidation and rate limiting. These are backend tests, not physical Android/iOS app certification. The Flutter client is included under `mobile/`; see its README for setup and platform validation.

## Endpoint reference

| Method | Path | Authentication |
| --- | --- | --- |
| POST | `/auth/forgot-password` | Public |
| POST | `/auth/login` | Public |
| POST | `/auth/logout` | Bearer |
| POST | `/auth/logout-all` | Bearer |
| POST | `/auth/refresh` | Bearer |
| POST | `/auth/register` | Public |
| POST | `/auth/resend-verification` | Public |
| POST | `/auth/reset-password` | Public |
| POST | `/auth/verify-email` | Public |
| GET | `/categories` | Public |
| GET | `/config` | Public |
| GET | `/games` | Public |
| GET | `/games/{provider}/{id}` | Public |
| GET | `/lotteries` | Bearer |
| GET | `/me` | Bearer |
| PATCH | `/me` | Bearer |
| DELETE | `/me` | Bearer |
| POST | `/me/2fa/disable` | Bearer |
| POST | `/me/2fa/enable` | Bearer |
| POST | `/me/2fa/setup` | Bearer |
| GET | `/me/dashboard` | Bearer |
| GET | `/me/devices` | Bearer |
| DELETE | `/me/devices/{id}` | Bearer |
| GET | `/me/favorites` | Bearer |
| PUT | `/me/games/{provider}/{id}/favorite` | Bearer |
| DELETE | `/me/games/{provider}/{id}/favorite` | Bearer |
| POST | `/me/games/{provider}/{id}/played` | Bearer |
| GET | `/me/lottery/tickets` | Bearer |
| GET | `/me/lottery/tickets/{id}` | Bearer |
| GET | `/me/lottery/wins` | Bearer |
| GET | `/me/notifications` | Bearer |
| PATCH | `/me/notifications/read-all` | Bearer |
| PATCH | `/me/notifications/{id}/read` | Bearer |
| PUT | `/me/password` | Bearer |
| GET | `/me/preferences` | Bearer |
| PUT | `/me/preferences` | Bearer |
| GET | `/me/recent` | Bearer |
| DELETE | `/me/recent` | Bearer |
