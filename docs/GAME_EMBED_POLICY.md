# Game iframe ad handling — Free Games

This feature distinguishes first-party games from external games.

## First-party game documents

From Admin CMS → Site builder → Game Iframe Policy, enable 'Hide marked ads inside our own first-party games'.
The toggle defaults off. Both the server policy and browser require an exact scheme, hostname and port match.
Only owner-marked elements in the controlled iframe are removed: [data-owned-ad] and .owned-game-ad.
A MutationObserver also removes dynamically inserted, deliberately marked elements. Unmarked game UI is never removed.
If the iframe navigates to an external origin, JavaScript catches the cross-origin restriction and does nothing.

## Third-party iframe documents

Parent JavaScript cannot access another website's iframe DOM. No universal cross-origin ad blocker is implemented.
When the actual provider contract officially documents an ad-free launch parameter, add its exact host to
config/game_embeds.php under approved_ad_free_providers. The entry must have verified=true and an
official parameter + value. The administrator must ALSO enable 'Prefer officially licensed third-party ad-free embed mode'.
Both conditions are required; default config is an empty provider allowlist.

Illustrative NON-WORKING placeholder (not an approved real provider):

    'licensed.example.test' => [
        'verified' => true,
        'parameter' => 'official_mode',
        'value' => 'adfree',
    ],

Replace the placeholder only using independently verified provider documentation and permission.
Ads remain controlled by each provider otherwise; no ad request interception, iframe proxy or injection is attempted.
Casino-related game catalog filtering remains unchanged.

## Security and rollout

- Only HTTP(S) URLs without embedded user credentials may be framed. External HTTP URLs are rejected.
- Invalid iframe URLs are refused before game-open points are awarded.
- APP_URL must match the deployed public site origin.
- Existing SiteCmsConfig database table is reused; no new migration is needed.
- Saved user data, games and SMTP settings are not modified.
- Build frontend: npm ci && npm run build; then php artisan optimize:clear and php artisan view:cache.
- Test first-party marker removal in an owned game and confirm a remote iframe is never modified.

Feature tests cover origin checking, explicit provider verification, CMS permissions and fail-closed behavior.
