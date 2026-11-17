# BTCPay Server crypto custom-amount checkout

## Status

**Implementation complete for the website's independent payment-invoice lifecycle.**
Production is OFF until the merchant configures a self-hosted or managed BTCPay
Server store with a receiving wallet, API key and webhook secret. No private
keys or signing seed phrases belong in this application.

**Not a casino funding rail:** even on invoice settlement, the application
does not credit `users.balance`, `available_balance`, `user_deposits`,
casino game sessions or `transactions`. Crypto invoices represent
independent payment receipts, not wagers, withdrawable balances or casino
settlements. Such regulated product flows require separate legal and
provider approval, controls and explicit implementation.

## Website / user flow

1. Front header (logged in) → Crypto pay, or User portal → Payments → Pay with crypto.
2. Type any amount, e.g. `10`, `20`, `55`, `125.50`; presets are convenient
   suggestions only. Server validates precise two-decimal amount, configured
   min/max and locks fiat unit to one configured currency.
3. POST `/user/crypto-payments` creates an internal UUID payment record.
4. Backend creates a BTCPay Greenfield invoice, remembers invoice ID and
   provider checkout URL, and opens an internal payment status page.
5. Click 'Continue to BTCPay checkout' to pay via provider-hosted Bitcoin
   or Lightning checkout. BTCPay wallet setup determines available methods.
6. BTCPay POSTs a signed webhook to `/payments/crypto/btcpay/webhook`.
   The backend compares the SHA256 HMAC of **raw** bytes with `BTCPay-Sig`,
   checks the store, and fetches the invoice from the provider via API.
   Provider GET must match invoice ID, internal order UUID, amount, currency
   and store ID (when included) before updating status.
7. Invoice `Processing` remains processing. Only `Settled` with
   `additionalStatus=None` becomes settled. Partial, late, overpaid and
   manually marked invoices require review, never automated acceptance.
   Expired/invalid invoices are marked accordingly. Repeated webhooks cannot
   duplicate credit or revert a previously settled record.
8. User views only their own invoices; can request status recheck (throttled).
   Admin → Finance → Crypto payments displays read-only payment records.

## BTCPay merchant setup

1. Deploy/configure an HTTPS-accessible BTCPay Server instance and a store
   with an operational Bitcoin/Lightning receiving wallet. Test with
   BTCPay's testnet environment before using production funds.
2. Verify your applicable jurisdictions, legal entity, legal basis for
   payments (especially if connected to casino features), identity/age/AML
   controls, refund and tax obligations. Do not accept wagers before
   required licensing and any host/provider permissions.
3. Create a **store-scoped API key** with only
   `btcpay.store.cancreateinvoice` and `btcpay.store.canviewinvoices`.
4. Under store Settings → Webhooks, create a webhook using the exact URL:
   `https://YOUR_DOMAIN/payments/crypto/btcpay/webhook`.
   Subscribe to invoice lifecycle events (Created, Processing, Settled,
   Expired, Invalid, ReceivedPayment, PaymentSettled). Copy the webhook
   secret into a private server environment variable. Do NOT send secrets
   to the browser or store them in the Admin gateway metadata form.
5. Configure environment variables:

```dotenv
CRYPTO_BTCPAY_ENABLED=false
CRYPTO_BTCPAY_URL=https://btcpay.your-server.example
CRYPTO_BTCPAY_STORE_ID=your-store-id
CRYPTO_BTCPAY_API_KEY=your-store-scoped-api-key
CRYPTO_BTCPAY_WEBHOOK_SECRET=your-webhook-secret
CRYPTO_BTCPAY_CURRENCY=USD
CRYPTO_BTCPAY_MIN_AMOUNT=1.00
CRYPTO_BTCPAY_MAX_AMOUNT=10000.00
```

6. Run `php artisan migrate --force`, `php artisan optimize:clear`,
   and `php artisan view:cache`. Rebuild the frontend if required by your
   release process; the checkout interface is server-rendered.
7. In **testnet**, verify the complete flow: browser invoice, provider
   checkout, payment confirmation, signed webhook, refresh, duplicates,
   expiry and invalid/late/partial states; verify no wallet/deposit credit.
8. ONLY after legal review and live merchant/wallet configuration are complete,
   set `CRYPTO_BTCPAY_ENABLED=true` and refresh the config cache.

## Operational safeguards

- Browser amount input is NOT trusted; server enforces numeric bounds and
  up to 2 decimals; no float used to prepare the stored amount.
- Provider URL/store/API secrets are server configuration only.
- Payment invoice/create and status-refresh endpoints require login, 2FA
  and active user status and are rate-limited. Demo accounts are blocked.
- All customer pages are scoped to `auth()->id()`; admin reporting is read-only.
- Provider checkout URL must be HTTPS and share exact provider host/port.
- Never treat browser return, POST fields, a webhook's body status, or
  a manually edited DB value as definitive evidence of receipt.
- Webhook has exact CSRF exemption, HMAC signature, event schema and
  payload-size limit; a provider GET verifies final status and identity.
- DB state updates lock the payment row and are idempotent.
- No server-side coin private keys, withdrawal endpoints, wager funds or
  automatic refunds are implemented.
- The Admin `gateways` table is **not** the source of truth for these
  invoices and existing legacy `user_deposits` remain unchanged.
- Configure HTTPS, queueing/monitoring of 5xx webhook failures, periodic
  reconciliation, refunds/chargebacks and accounting policies before
  treating this as an audited financial product.

Official reference:
https://docs.btcpayserver.org/Developers/api/examples/
https://docs.btcpayserver.org/Development/ecommerce-integration-guide/

## Local regression tests

Run `php artisan test --filter=CryptoPaymentCheckoutTest` and the full suite.
Laravel Http fake exercises checkout, invoice lookup, signed webhook,
underpayments/partial status, repeated delivery and ownership isolation.
