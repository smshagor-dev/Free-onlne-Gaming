<?php

namespace App\Http\Controllers;

use App\Models\CryptoPayment;
use App\Services\NOWPaymentsClient;
use App\Services\CryptoPaymentReconciliation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CryptoPaymentWebhookController extends Controller
{
    /**
     * NOWPayments webhook: x-nowpayments-sig is HMAC-SHA512 of the
     * lexically key-sorted JSON (including nested arrays), NOT raw bytes.
     * After checking the IPN signature, independently fetch the payment
     * status and verify invoice/order/amount/currency before updating.
     */
    public function __invoke(Request $request, NOWPaymentsClient $client, CryptoPaymentReconciliation $reconciler): JsonResponse
    {
        if (! $client->configured()) {
            return response()->json(['error' => 'Unavailable'], 503);
        }

        $raw = $request->getContent();
        if (strlen($raw) > 65536) {
            return response()->json(['error' => 'Oversized'], 413);
        }

        $event = json_decode($raw, true);
        if (! is_array($event)) {
            return response()->json(['error' => 'Invalid payload'], 422);
        }

        $canonical = $this->canonicalJson($event);
        $expected = hash_hmac('sha512', $canonical, (string) config('crypto_payments.ipn_secret'));
        $actual = (string) $request->header('x-nowpayments-sig', '');
        if ($actual === '' || ! hash_equals($expected, $actual)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $invoiceId = (string) ($event['invoice_id'] ?? '');
        $paymentId = (string) ($event['payment_id'] ?? '');
        if (! preg_match('/^[0-9]{1,40}$/D', $invoiceId)
            || ! preg_match('/^[0-9]{1,40}$/D', $paymentId)) {
            return response()->json(['error' => 'Invalid payment identifiers'], 422);
        }

        $payment = CryptoPayment::query()->where('invoice_id', $invoiceId)->first();
        if ($payment === null) {
            // NOWPayments should redeliver if the local order isn't available.
            return response()->json(['error' => 'Invoice not found'], 503);
        }

        try {
            $reconciler->reconcile($payment, $paymentId);
        } catch (Throwable $e) {
            Log::warning('NOWPayments IPN reconciliation failed', [
                'payment_id' => $payment->id,
                'failure_type' => get_class($e),
            ]);

            return response()->json(['error' => 'Unable to verify payment'], 503);
        }

        return response()->json(['acknowledged' => true]);
    }

    private function canonicalJson(array $input): string
    {
        $sorted = $this->sortKeys($input);

        return json_encode($sorted, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function sortKeys(array $values): array
    {
        if (! array_is_list($values)) {
            ksort($values, SORT_STRING);
        }
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = $this->sortKeys($value);
            }
        }
        unset($value);

        return $values;
    }
}
