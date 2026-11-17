<?php

namespace App\Http\Controllers;

use App\Models\CryptoPayment;
use App\Services\BTCPayClient;
use App\Services\CryptoPaymentReconciliation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CryptoPaymentWebhookController extends Controller
{
    /**
     * Public webhook endpoint, exempt from CSRF only on this exact path.
     * Authentication = HMAC-SHA256 signature over RAW bytes plus a fresh
     * provider GET of the invoice and matching local order metadata.
     */
    public function __invoke(Request $request, BTCPayClient $client, CryptoPaymentReconciliation $reconciler): JsonResponse
    {
        if (! $client->configured()) {
            return response()->json(['error' => 'Unavailable'], 503);
        }

        $raw = $request->getContent();
        if (strlen($raw) > 65536) {
            return response()->json(['error' => 'Oversized'], 413);
        }

        $secret = (string) config('crypto_payments.webhook_secret');
        $actual = (string) $request->header('BTCPay-Sig', '');
        $expected = 'sha256='.hash_hmac('sha256', $raw, $secret);

        if ($actual === '' || ! hash_equals($expected, $actual)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($raw, true);
        if (! is_array($event)
            || ! is_string($event['invoiceId'] ?? null)
            || ! is_string($event['type'] ?? null)
            || (isset($event['storeId']) && $event['storeId'] !== config('crypto_payments.store_id'))) {
            return response()->json(['error' => 'Invalid event'], 422);
        }

        if (! str_starts_with($event['type'], 'Invoice')) {
            return response()->json(['acknowledged' => true]);
        }

        $payment = CryptoPayment::query()->where('invoice_id', $event['invoiceId'])->first();
        if ($payment === null) {
            // Return an error for a potential create/invoice-store race so
            // BTCPay can redeliver rather than silently losing this update.
            return response()->json(['error' => 'Invoice not found'], 503);
        }

        try {
            $reconciler->reconcile($payment);
        } catch (Throwable $e) {
            Log::warning('Crypto webhook reconciliation failed', [
                'payment_id' => $payment->id,
                'failure_type' => get_class($e),
            ]);

            return response()->json(['error' => 'Unable to verify invoice'], 503);
        }

        return response()->json(['acknowledged' => true]);
    }
}
