# NOWPayments custom-amount hosted crypto checkout

## Status and boundaries

NOWPayments hosted invoice flow is implemented in backend and frontend,
**but disabled until a real merchant account/API key/IPN secret and compliant
wallet are configured**. No production payment has been processed here.

**This is not casino funding.** Confirmed invoices are stored in the
`crypto_payments` audit table. No code credits `users.balance`,
`user_deposits`, casino betting transactions or withdrawable cash.
Real-money gaming balances, KYC, age-gating, licensing, AML, geographic
controls and provider merchant approval require an entirely separate
regulated implementation and legal review.

NOWPayments' Terms of Service (April 13, 2026; FD Transfers LLC) section
15.1 states that services are not rendered to EU, UK or US citizens and
residents. Check the latest official terms and obtain written merchant
approval for each operating/consumer country **before enabling**.

## End-to-end user flow

1. User account sidebar → Payments → Pay with crypto, or logged-in header → Crypto pay.
2. Enter any valid amount such as 10, 20, 55 or 125.50; price currency is
   controlled server-side. The presets are only convenience shortcuts.
3. Laravel POST `/user/crypto-payments` validates auth/2FA/ban/demo and
   precise two-decimal amount and configured min/max, then creates
   a UUID-based pending `crypto_payments` record.
4. Server POSTs `https://api.nowpayments.io/v1/invoice`, using `x-api-key`,
   `price_amount`, `price_currency`, `order_id` (UUID), IPN URL and
   return URLs. No `pay_currency` is set: NOWPayments' hosted invoice
   allows the buyer to choose an available coin.
5. Backend saves the returned unique invoice ID and validates the hosted
   `https://nowpayments.io/...` invoice URL. User can visit it and pay.
6. NOWPayments sends IPN POST to `/payments/crypto/nowpayments/webhook`.
   Laravel exempts only that path from CSRF. Handler recursively sorts
   JSON object keys and verifies `x-nowpayments-sig` as HMAC-SHA512 with
   the privately configured IPN secret using constant-time comparison.
7. **Never trust IPN status alone.** After HMAC verification the backend
   GETs `/v1/payment/{payment_id}` with the private API key and matches
   provider payment ID, invoice ID, UUID order ID, fiat price amount and
   currency against the stored local invoice. A DB row lock prevents
   repeat/replayed notifications from creating duplicate financial effects.
8. Status maps: `waiting`→pending; `confirming`, `confirmed`, `sending`
   →processing; **only** `finished`→settled; `failed`→invalid;
   `expired`→expired; `partially_paid` or `refunded`→manual review.
   Multiple different provider payment IDs for the same invoice also
   require manual review. No browser return alone marks success.
9. User sees private invoice history/status. Manual status recheck is
   possible after the provider has supplied a payment ID in an IPN.
   Admin → Finance → Crypto payments is read-only.

## Merchant setup and deployment

1. Create/verify a NOWPayments merchant account. Verify **eligibility**,
   business purpose, user/merchant countries, prohibited jurisdictions,
   fees, AML/KYC obligations and approved wallet currencies.
2. Configure a receiving crypto wallet in the merchant dashboard.
3. Generate an API key and IPN Secret Key; do not paste either into the
   Admin gateway metadata form or commit keys to source control.
4. Keep `NOWPAYMENTS_ENABLED=false` until your account is ready.
5. Configure private `.env` values:

```dotenv
NOWPAYMENTS_ENABLED=false
NOWPAYMENTS_API_URL=https://api.nowpayments.io
NOWPAYMENTS_API_KEY=YOUR_MERCHANT_API_KEY
NOWPAYMENTS_IPN_SECRET=YOUR_IPN_SECRET
NOWPAYMENTS_PRICE_CURRENCY=USD
NOWPAYMENTS_MIN_AMOUNT=1.00
NOWPAYMENTS_MAX_AMOUNT=10000.00
```

6. The IPN callback passed at invoice creation is:
   `https://YOUR_DOMAIN/payments/crypto/nowpayments/webhook`.
   Public HTTPS, a responsive server, valid TLS and firewall rules
   permitting NOWPayments IPN senders are necessary. Their Help Center
   recommends responding to callbacks within **3 seconds**.
7. Deploy `php artisan migrate --force`, `php artisan optimize:clear`,
   `php artisan view:cache`. `npm run build` if part of your deployment.
8. Run a real **sandbox/test** with provider-supported test account and
   sandbox API before enabling. Simulate invalid HMAC, pending, partial,
   confirmed, finished, expired, repeated IPN and wrong order/amount.
9. Once permitted and verified, set `NOWPAYMENTS_ENABLED=true`, then
   refresh Laravel config. DO NOT enable until geographic rules are met.

## Security and limitations

- API URL allowlisted to official prod/sandbox NOWPayments API hosts;
  all backend API requests enforce TLS, timeouts and no redirects.
- Client must not send host, callback, order reference, or merchant account
  identifiers; Laravel generates these.
- Invoice link accepts only HTTPS nowpayments.io/approved subdomains.
- Webhook signature and independently verified GET payment identity required.
- Unknown/mismatching provider statuses fail closed. Refresh is throttled.
- No API secrets in browser, logs, database or source.
- No wallet/deposit credit, cash-out, refund, payout or casino balance enabled.
- NOWPayments may impose crypto-specific minimums/fees beyond the site's
  price amount range, which appear during hosted checkout.
- Existing legacy Admin Payment gateways configuration is separate from
  this real provider adapter and doesn't activate it.

Official sources:
- https://nowpayments.zendesk.com/hc/en-us/articles/21345824322717-API-and-endpoint-description
- https://nowpayments.zendesk.com/hc/en-us/articles/21395546303389-IPN-and-how-to-setup
- https://nowpayments.io/doc/fd-tos.pdf?v=1.3
