# Country and currency reference dataset

This directory contains an OFFLINE pinned snapshot of the MIT-licensed `annexare/Countries` project.

- Source repository: https://github.com/annexare/Countries
- Source commit: `1c393325611d89d3511de01269a4f60fe9dc1c6f` (2026-07-14)
- Countries: `dist/countries.min.json` (249 ISO 3166-1 code entries after excluding pseudo-codes AC, TA and XK)
- Alpha-3 country codes: `packages/countries/src/data/countries.2to3.ts`
- Currencies: `dist/currencies.min.json` (160 active ISO 4217 currency codes, with CLDR user-facing symbols)
- License and required copyright notice: `ANNEXARE_LICENSE.txt` (MIT)

Seeded countries store the **first official currency** when more than one is listed (e.g. Zimbabwe). This is a reference default, not a live FX source and not a jurisdictional payment-availability determination.

The fixture files are checked into Git; Laravel seeders never make HTTP requests.
CountrySeeder matches on ISO-2 code, inserts missing rows, fills missing fields and fixes the old `Latitude` typo for Latvia. It never resets a country's status or an existing manually set currency. CurrencySeeder inserts missing codes without overwriting existing entries.

Production initialization:

```bash
php artisan migrate --force
php artisan db:seed --class=CurrencySeeder --force
php artisan db:seed --class=CountrySeeder --force
php artisan optimize:clear
```

DatabaseSeeder also includes both. Prefer the targeted commands in production: the existing DatabaseSeeder also creates example users and administrators.

Do not use `migrate:fresh` or `db:wipe` for these updates. Back up existing database tables before a release.

Data freshness: this is a versioned Git snapshot, not an always-updated ISO registry. New official country or currency changes should be reviewed and explicitly incorporated into a later revision; existing customized production rows remain unchanged by default.
