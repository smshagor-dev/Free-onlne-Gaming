<?php

namespace App\Services;

use App\Models\CryptoPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CryptoPaymentReconciliation
{
    public function __construct(private readonly BTCPayClient $client) {}

    /**
     * Provider GET is authoritative, not browser redirects or webhook JSON.
     * No balance/deposit/wager ledger is credited by this reconciler.
     */
    public function reconcile(CryptoPayment $record): CryptoPayment
    {
        if (blank($record->invoice_id)) {
            throw new RuntimeException('This checkout has no provider invoice.');
        }

        $invoice = $this->client->getInvoice($record->invoice_id);
        if (($invoice['id'] ?? null) !== $record->invoice_id
            || ($invoice['metadata']['orderId'] ?? null) !== $record->reference
            || strtoupper((string) ($invoice['currency'] ?? '')) !== $record->currency
            || ! $this->sameAmount($record->amount, $invoice['amount'] ?? null)
            || (isset($invoice['storeId']) && $invoice['storeId'] !== config('crypto_payments.store_id'))) {
            throw new RuntimeException('Provider invoice fields do not match the locally recorded order.');
        }

        $state = (string) ($invoice['status'] ?? '');
        $additional = (string) ($invoice['additionalStatus'] ?? 'None');
        $valid = ['New', 'Processing', 'Settled', 'Expired', 'Invalid'];
        if (! in_array($state, $valid, true)) {
            throw new RuntimeException('Provider returned an unknown invoice status.');
        }

        $newState = match (true) {
            $additional !== 'None' => 'review', // late/partial/over/marked requires investigation
            $state === 'Settled' => 'settled',
            $state === 'Processing' => 'processing',
            $state === 'Expired' => 'expired',
            $state === 'Invalid' => 'invalid',
            default => 'pending',
        };

        return DB::transaction(function () use ($record, $newState, $additional): CryptoPayment {
            $payment = CryptoPayment::query()->lockForUpdate()->findOrFail($record->id);
            // Old delayed callbacks cannot revert an already settled invoice.
            if ($payment->status === 'settled' && $newState !== 'review') {
                $payment->last_checked_at = now();
                $payment->save();
                return $payment;
            }

            $payment->status = $newState;
            $payment->additional_status = $additional;
            $payment->last_checked_at = now();
            if ($newState === 'settled' && $payment->settled_at === null) {
                $payment->settled_at = now();
            }
            $payment->save();

            return $payment;
        }, 3);
    }

    private function sameAmount(string $expected, mixed $actual): bool
    {
        if (! is_string($actual) && ! is_int($actual) && ! is_float($actual)) {
            return false;
        }
        $amount = (string) $actual;
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D', $amount)) {
            return false;
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');
        $cents = ((int) $whole) * 100 + (int) str_pad($fraction, 2, '0');
        [$recordWhole, $recordFraction] = array_pad(explode('.', $expected, 2), 2, '');
        $recordCents = ((int) $recordWhole) * 100 + (int) str_pad($recordFraction, 2, '0');

        return $recordCents === $cents;
    }
}
