<?php

namespace App\Services;

use App\Models\CryptoPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CryptoPaymentReconciliation
{
    public function __construct(private readonly NOWPaymentsClient $client) {}

    /**
     * Verified API GET, never browser URL or unsigned IPN status, is authoritative.
     * No wager, deposit, withdrawal or casino wallet balance is credited here.
     */
    public function reconcile(CryptoPayment $record, string $providerPaymentId): CryptoPayment
    {
        $payment = $this->client->getPayment($providerPaymentId);
        if ((string) ($payment['payment_id'] ?? '') !== $providerPaymentId
            || (string) ($payment['invoice_id'] ?? '') !== (string) $record->invoice_id
            || ($payment['order_id'] ?? null) !== $record->reference
            || strtoupper((string) ($payment['price_currency'] ?? '')) !== $record->currency
            || ! $this->sameAmount((string) $record->amount, $payment['price_amount'] ?? null)) {
            throw new RuntimeException('Provider payment does not match local invoice amount, currency or order.');
        }

        $status = strtolower((string) ($payment['payment_status'] ?? ''));
        $newState = match ($status) {
            'waiting' => 'pending',
            'confirming', 'confirmed', 'sending' => 'processing',
            'finished' => 'settled',
            'failed' => 'invalid',
            'expired' => 'expired',
            'partially_paid', 'refunded' => 'review',
            default => throw new RuntimeException('Unrecognized NOWPayments status.'),
        };

        return DB::transaction(function () use ($record, $providerPaymentId, $status, $newState): CryptoPayment {
            $row = CryptoPayment::query()->lockForUpdate()->findOrFail($record->id);
            if ($row->provider_payment_id !== null && $row->provider_payment_id !== $providerPaymentId) {
                // One invoice can produce multiple payments/partial receipts.
                // Do not attribute a second payment to the first's settlement.
                $row->status = 'review';
                $row->additional_status = 'multiple_payments';
                $row->last_checked_at = now();
                $row->save();
                return $row;
            }

            $row->provider_payment_id = $providerPaymentId;
            if ($row->status === 'settled' && $newState !== 'review') {
                $row->last_checked_at = now();
                $row->save();
                return $row;
            }

            $row->status = $newState;
            $row->additional_status = $status;
            $row->last_checked_at = now();
            if ($newState === 'settled' && $row->settled_at === null) {
                $row->settled_at = now();
            }
            $row->save();

            return $row;
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

        [$whole, $cents] = array_pad(explode('.', $amount, 2), 2, '');
        [$owed, $owedCents] = array_pad(explode('.', $expected, 2), 2, '');

        return ((int) $whole) * 100 + (int) str_pad($cents, 2, '0')
            === ((int) $owed) * 100 + (int) str_pad($owedCents, 2, '0');
    }
}
