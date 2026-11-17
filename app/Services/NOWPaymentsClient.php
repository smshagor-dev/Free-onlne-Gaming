<?php

namespace App\Services;

use App\Models\CryptoPayment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class NOWPaymentsClient
{
    public function configured(): bool
    {
        return (bool) config('crypto_payments.enabled')
            && in_array(config('crypto_payments.url'), [
                'https://api.nowpayments.io',
                'https://api-sandbox.nowpayments.io',
            ], true)
            && filled(config('crypto_payments.api_key'))
            && filled(config('crypto_payments.ipn_secret'))
            && (bool) preg_match('/^[A-Z]{3}$/D', (string) config('crypto_payments.currency'));
    }

    private function endpoint(string $path): string
    {
        if (! $this->configured()) {
            throw new RuntimeException('NOWPayments checkout is not configured.');
        }

        return config('crypto_payments.url').'/v1/'.$path;
    }

    private function api()
    {
        return Http::acceptJson()
            ->withHeaders([
                'x-api-key' => config('crypto_payments.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(12)->connectTimeout(4)
            ->withOptions(['allow_redirects' => false, 'verify' => true]);
    }

    public function createInvoice(CryptoPayment $payment): array
    {
        $webhook = route('crypto.nowpayments.webhook');
        if (app()->environment('production') && ! str_starts_with($webhook, 'https://')) {
            throw new RuntimeException('NOWPayments IPN requires public HTTPS.');
        }

        $response = $this->api()->post($this->endpoint('invoice'), [
            'price_amount' => (string) $payment->amount,
            'price_currency' => strtolower($payment->currency),
            'order_id' => $payment->reference,
            'order_description' => 'Free Games crypto payment',
            'ipn_callback_url' => $webhook,
            'success_url' => route('user.crypto.show', ['reference' => $payment->reference]),
            'cancel_url' => route('user.crypto.show', ['reference' => $payment->reference]),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('NOWPayments could not create a checkout invoice.');
        }

        $data = $response->json();
        $id = (string) ($data['id'] ?? '');
        $url = $data['invoice_url'] ?? null;

        if (! preg_match('/^[0-9]{1,40}$/D', $id)
            || ! is_string($url) || ! $this->trustedCheckoutUrl($url)
            || (isset($data['order_id']) && $data['order_id'] !== $payment->reference)
            || (isset($data['price_currency']) && strtolower((string) $data['price_currency']) !== strtolower($payment->currency))
            || (isset($data['price_amount']) && ! $this->sameAmount((string) $payment->amount, $data['price_amount']))) {
            throw new RuntimeException('NOWPayments returned an invalid checkout invoice.');
        }

        return ['id' => $id, 'checkoutLink' => $url];
    }

    public function getPayment(string $paymentId): array
    {
        if (! preg_match('/^[0-9]{1,40}$/D', $paymentId)) {
            throw new RuntimeException('Invalid NOWPayments payment reference.');
        }

        // Provider IPN endpoints should respond promptly; retry on timeout.
        $response = $this->api()->timeout(2.5)->connectTimeout(1.4)
            ->get($this->endpoint('payment/'.$paymentId));
        if (! $response->successful() || ! is_array($response->json())) {
            throw new RuntimeException('NOWPayments could not verify the payment.');
        }

        return $response->json();
    }

    private function sameAmount(string $expected, mixed $actual): bool
    {
        if (! is_string($actual) && ! is_int($actual) && ! is_float($actual)) {
            return false;
        }
        $value = (string) $actual;
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/D', $value)) {
            return false;
        }
        [$whole, $decimals] = array_pad(explode('.', $value, 2), 2, '');
        [$owed, $owedDecimals] = array_pad(explode('.', $expected, 2), 2, '');

        return ((int) $whole) * 100 + (int) str_pad($decimals, 2, '0')
            === ((int) $owed) * 100 + (int) str_pad($owedDecimals, 2, '0');
    }

    public function trustedCheckoutUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        return ($parts['scheme'] ?? '') === 'https'
            && ($host === 'nowpayments.io' || str_ends_with($host, '.nowpayments.io'))
            && ! isset($parts['user'])
            && ! isset($parts['pass'])
            && ! isset($parts['fragment'])
            && ($parts['port'] ?? 443) === 443;
    }
}
