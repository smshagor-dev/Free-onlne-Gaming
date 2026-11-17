# KYC-gated withdrawal request and administrator approval

## Purpose and safety boundary

Implements a **request, reserve, review and manually attested payout**
workflow over the site's existing numeric `users.balance` and
`users.available_balance`. It does NOT initiate a NOWPayments withdrawal,
transfer cryptocurrency, prove a blockchain settlement, issue card refunds
or create a regulated gaming payout service.

**Disabled by default.** Operators must first verify licensure, user
jurisdiction, KYC/AML/age rules, redeemability and source of wallet funds,
network and conversion rates, and the legitimacy of the receiving wallet.
Do not enable withdrawals based solely on casino points or unbacked credits.

## User journey

1. Login → header **Withdraw** or Profile portal → Wallet & requests →
   **Withdraw funds**. The page shows available balance, KYC eligibility
   and request history.
2. Only users with `kyc_verified = true`, active non-demo accounts, matching
   configured currency and enough existing `available_balance` AND
   `balance` can submit. Disabled flag or missing KYC blocks requests.
3. Specify amount (2 decimal places), BTC/Bitcoin network or USDT/Tron
   TRC20 / Ethereum ERC20 network, and the destination address. Basic
   address format checks reduce mistakes but do not validate chain
   checksums, chain ownership or receiving exchange compatibility.
4. Transaction locks the user row. It requires no other active request
   and atomically subtracts the requested amount from available_balance
   as a *reservation*, without decreasing total balance or issuing a payout.
5. User may cancel **pending** requests; cancellation restores held
   available_balance exactly once. User cannot edit approved requests.
6. Admin CMS → Finance → Withdrawal approvals displays requests, KYC
   state, amount and selected coin/network. Only authenticated admins
   may view the full destination (encrypted at rest).
7. Admin **Approve** performs eligibility revalidation and leaves funds
   reserved. Admin **Reject** requires a reason and restores the hold
   exactly once. These actions do not send money.
8. After an operator **externally sends** a real crypto transfer and checks
   beneficiary/address/network, Admin may record a unique tx hash +
   explicit transfer attestation. The workflow marks `paid_recorded`
   and atomically deducts the amount from total `users.balance`, leaving
   available balance unchanged (it was already reserved). A repeated
   request cannot charge or release the amount twice.

## States

pending → approved → paid_recorded
pending/approved → rejected
pending → cancelled

`paid_recorded` means administrator-provided external transfer reference;
it is **NOT** independently verified on-chain success. Any external payout
must be funded separately by the operator. No automatic crypto payout
integration exists, even if NOWPayments hosted checkout is later enabled.

## Configuration

```dotenv
WITHDRAWAL_REQUESTS_ENABLED=false
WITHDRAWAL_CURRENCY=USD
WITHDRAWAL_MIN_AMOUNT=10.00
WITHDRAWAL_MAX_AMOUNT=10000.00
```

Run `php artisan migrate --force` and `php artisan optimize:clear` then
`php artisan view:cache`. Verify the funding source and currency before
ever enabling requests. Do NOT run `migrate:fresh` on live databases.

## Operational notes

- `withdrawal_requests.destination` uses Laravel's encrypted cast and the
  server APP_KEY. Secure that key and restrict administrator access.
- User-locked DB transactions protect against concurrent requests and
  double refund/finalization within this workflow. **Other legacy wallet
  writes may not use these locks**, so a full financial ledger audit and
  reconciliation is still required before real-money deployment.
- `users.balance` / `users.available_balance` are legacy columns; the
  application does not prove they represent redeemable or independently
  backed money. Funds from independent crypto invoice receipts are
  intentionally not converted into user withdrawable balances.
- The legacy `user_withdrew` and `transactions` records are NOT mutated
  and must not be added together with the new request table as if they
  were the same financial ledger.
- BTC/USDT amounts on the request form are specified in fiat USD-equivalent
  units, NOT guaranteed coin quantities. Admin must separately establish
  exact chain amount, network fees and fair conversion before transfer.
- Do not treat a txid typed by an admin as authenticated provider payment
  evidence; independent blockchain confirmation remains outstanding.
- When compliance readiness is incomplete, keep
  `WITHDRAWAL_REQUESTS_ENABLED=false` and use the feature for review only.

See `tests/Feature/KycWithdrawalApprovalTest.php` for regression cases.
