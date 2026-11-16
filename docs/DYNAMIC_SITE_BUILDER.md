# Dynamic Site Builder — Free Games

Admin login remains at:
`/sm-shagor/free-games/admin-main/control-back-office/login`

From the left sidebar select **Content → Dynamic site builder** or use the
**Dynamic Site Builder** shortcut on the admin dashboard.

## Editable sections

| Section | Website behavior |
|---|---|
| Homepage | Hero badge, three headline spans, description, CTA labels, visibility and headings for categories/featured/new/explore sections |
| Header & Menu | Text and visibility of fixed route-based links; comma-separated approved game categories |
| Footer | Public footer copy and Games/Contact links |
| Catalog | Featured/new group sizes, public listing page size, AJAX suggestion cap, number of categories shown, external catalog toggle |
| PWA | App name, short name, description, theme/background colors, and display mode, from the live `/site.webmanifest` |

Dynamic labels are escaped in Blade. Navigation uses known Laravel routes;
the CMS does not accept arbitrary external menu URLs, JavaScript, or HTML.

The PWA icons remain the same existing branded PNG assets, so custom image
generation and installation icon replacement are not part of this update.
The manifest is not cached by the service worker.

### Users and activity reports

Go to **Community → User activity reports** to see real recorded sign-ins,
game activity, and daily charts. You can filter by database user ID and
date range, limited to 90 days per query. Results paginate. Only events
already recorded by the application are included, so the report is not
a complete independent traffic analytics system.

The existing detailed admin profile keeps account details, KYC and
financial fields. Login activity, password hashes, tokens and 2FA keys
are handled separately; secrets are never printed in these reports.

### Game provider behavior

The third-party embeddable catalog remains filtered against unapproved
URLs and gambling content. Disabling the provider removes external games
from public playable listings without deleting existing local game rows,
external feed cache entries, user accounts or settings. Toggle it back on
to re-enable the approved catalog.

The CMS controls content and presentation settings; it does not introduce
arbitrary PHP/template editing or a drag-and-drop theme engine.

## Deployment

Back up database and storage before deploying.

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan optimize:clear
php artisan view:cache
```

The new `site_cms_configs` table is additive. No destructive migrations
touch users, saved site settings, existing games, or encrypted/legacy SMTP
credentials. All sections have fallbacks so an unconfigured installation
renders the current site design.

Settings are cached briefly and cleared after saving in the Site Builder.
Edits take effect on subsequent page loads; a PWA may require a restart
or browser reload to display new manifest metadata.

### Scope and QA

Run the Laravel feature suite, build frontend assets, and verify the actual
staging site before production promotion. Particularly verify mobile header,
homepage, login modal, catalog pagination, dynamic manifest, and user-activity
permission boundaries. Known dependency security advisories are tracked
separately and must be resolved before declaring the system security-ready.
