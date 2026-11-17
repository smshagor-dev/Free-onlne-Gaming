# Payment gateway configuration — first phase

Admin CMS → Finance → Payment gateways is an authenticated, admin-only
configuration inventory for the existing `gateways` table.

## What is implemented

- List existing payment method records without deleting or overwriting them.
- Create inactive gateway metadata: provider name, ISO currency, symbol, min/max
  amounts, administrative instructions.
- Edit a record's display and amount metadata without changing its stored
  `status` flag (including legacy rows already marked enabled).
- Form validation, CSRF, admin authentication, pagination and feedback.
- No new schema migrations; reuse existing `gateways` table.

## What is intentionally NOT implemented

- No real online checkout or provider API credentials.
- No charge, card collection, provider callbacks, webhook endpoints, wallet
  balance credit, wager funding, deposit approval, refund or withdrawal.
- No claim that a provider accepts casino/gambling merchants.
- The legacy `status` flag is not proof of an active merchant integration.

## Requirements for the live implementation

1. Identify the **legal entity and merchant jurisdiction** operating the site,
   along with target countries, exact currency and the payment purpose
   (ordinary subscriptions/cosmetics vs actual casino stakes or withdrawals).
2. Obtain a valid merchant agreement. Where gambling is involved, confirm
   licensing, geographic eligibility, age/KYC/AML controls and explicit
   gateway / acquiring-bank authorization for that merchant category.
3. Confirm provider-specific official SDK/API docs and staging keys.
4. Use provider-hosted checkout, encrypted secret storage, signed webhooks,
   server-side verification of amount/currency/order, stable idempotency keys,
   transactional settlement, retry/replay handling, audit events and refunds.
5. Isolate standard e-commerce payments from casino/betting balances unless
   that separate regulated flow is legally and contractually approved.

Only after those details are known should a live payment connector be enabled.
Never paste API keys or cardholder data into the current metadata form.

Deploy: `git pull origin main` then `php artisan optimize:clear` and
`php artisan view:cache`. No new migration or frontend asset bundle.
