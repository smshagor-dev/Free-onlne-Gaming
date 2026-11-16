# Casino activity reporting (Admin CMS)

Route: authenticated admin sidebar → Games & rewards → Casino games & activity.

This is a **read-only reporting page** over existing tables, not a casino
provider integration and not a betting or settlement system. No new
deposit, bet, win, credit, refund or wager-writing route is introduced.

## Recorded source and definitions

- `casino_game_sessions`: each row represents a provider/session launch as recorded by the existing application. Counts are **not** confirmed completed rounds or iframe page views. `COUNT(DISTINCT user_id)` is called unique players, not raw page views.
- `games.play_time`: existing locally managed games increment this on their logged-in open route; this is an **open counter**, not actual confirmed gameplay. `game_opens` tracks distinct local users via one row per user/game.
- `transactions`: gross bet/win amounts are derived ONLY from approved entries with both `provider_name` and `provider_transaction_id` present, `provider_action` strictly `bet` or `win`, a matching `casino_game_sessions.id` as `session_id`, and matching user ownership. Excluded: pending, rejected, unverified legacy entries, disconnected deposits, rollback/refund actions, and cross-user session references.
- Multiple transactions within a single session must not multiply session/launch counts; reporting groups sessions and ledger entries separately.
- Existing transactions do **not** persist a reliable transaction currency. Reporting figures are labelled *unlabelled units* and are not interpreted as currency totals, financial revenue, or settlement proof. New provider adapters must preserve authoritative currency data and refunds before real monetary claims can be made.

## Tables

1. Casino games: game name, distinct players, session launches, recorded gross bet, recorded gross win.
2. Player-by-game: user, game, launch count, recorded bet, recorded win.
3. Locally managed casino-style games: CMS game and category, `play_time` opens, `game_opens` users, edit link. No artificial bet/win values; these games are not joined to provider sessions.

The page supports searching game names, a user-ID filter for player-by-game rows, pagination, and an admin-only route. Its queries are read-only.

## Prerequisites and limitations

- The existing `casino_game_sessions` and `transactions` migrations must already have been run.
- This does not create new session records, load remote casino games, or remove the existing GameMonetize casino exclusion. A provider integration must be separately implemented and authorized.
- Providers can use different display names for one game or the same name for multiple games. Existing session schema only stores `game_name`, so groups are by that recorded name, not a globally unique provider game ID.
- Existing session records are not guaranteed actual completed bets. Provider contracts and applicable local legal/age requirements should be verified before a public casino launch.

Deploy: `git pull origin main`, `php artisan optimize:clear`, `php artisan view:cache`. No schema changes are necessary for these read-only reports.
