# Free Games Administration CMS

The existing protected admin URL is unchanged. Open the admin login at:

`/sm-shagor/free-games/admin-main/control-back-office/login`

After authentication, the new responsive left sidebar gives access to existing
CRUD operations for games, categories, tags, banners, pages, users, user bans,
country/currency settings, points, levels, lottery and prizes, contact messages,
scheduler logs, and account settings. The dashboard displays live, database-backed
metrics for users, gaming activity, notifications, lottery and finance.

## Before a production deployment

1. Back up the application database and storage uploads using your hosting
   provider's supported database and filesystem backup tools.
2. Deploy the latest `main` branch:
   ```bash
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   npm ci
   npm run build
   php artisan optimize:clear
   php artisan view:cache
   ```
3. Verify admin login, sidebar navigation, game/page editing, user detail screens,
   SMTP settings and the dashboard with a real admin test account.
4. On small screens verify the sidebar opens, closes and maintains keyboard access.

## Site settings

The form at **Administration → Site / SMTP / SEO** includes:

- General: site name, title, description, contact phone/email, currency and symbol.
- Branding: logo, favicon, social preview image.
- SEO: title, description and meta tags.
- Analytics: preserves existing analytics and AdSense code fields.
- SMTP: mail driver, server host, port, username, password, transport security,
  sender address and sender display name.

Settings are saved to the existing `settings` database record; they are **not**
written into `.env`. CMS SMTP values now use Laravel's actual `mail.default`
and `mail.mailers.smtp.*` configuration keys with environment fallbacks.

**SMTP passwords:** Existing saved passwords are retained without change. A
blank password box preserves the stored value. New credentials are encrypted
using the application's `APP_KEY`, and the database column is widened without
discarding old values. Do not rotate the production `APP_KEY` without a planned
re-encryption process; encrypted SMTP secrets depend on that key.

The SMTP test sends **only to the signed-in administrator's email**. It is
rate limited and returns a generic failure message so mailer internal errors
cannot leak the secret. Verify deliverability in the administrator mailbox
and check the provider's delivery logs.

Re-running `SettingSeeder` no longer overwrites configured values or seeds
fake SMTP credentials. Uploaded assets are not deleted when saving other
settings fields.

## Graphs and financial records

The four 14-day dashboard charts display daily counts of registrations and
game opens, plus recorded deposit/withdrawal sums. Missing days show zero.
These are database records, **not** traffic analytics or recognized revenue.

Financial oversight is **read-only**: approvals, refunds, settlement and ledger
mutations must use their designated validated business workflows. Do not
change financial status flags directly in the database.

User profiles expose account fields, verification and KYC status, activity,
wallet and bonus amounts, and recent logins. Password hashes, 2FA secrets,
reset tokens and email verification codes are never shown.

## Operations and known boundaries

- All CMS routes are protected by `auth:admin`, and all writes retain CSRF protection.
- CMS settings do not automatically deploy PWA app icon sizes after a custom
  favicon is uploaded. Rebuild icons separately if app branding changes.
- Analytics and AdSense snippet fields remain stored and editable; never inject
  untrusted HTML/JavaScript into a public site without a separate consent
  and security review.
- For major upgrades, test all operational workflows in staging before promoting
  to production. A successful CI build does not replace live browser QA.
