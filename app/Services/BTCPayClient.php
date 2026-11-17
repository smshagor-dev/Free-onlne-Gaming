<?php

namespace App\Services;

use App\Models\CryptoPayment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BTCPayClient
{
    public function configured(): bool
    {
        $url = (string) config('crypto_payments.url');
        return (bool) config('crypto_payments.enabled')
            && (bool) preg_match('~^https://[a-z0-9.-]+(?::[0-9]{2,5})?$~iD', $url)
            && filled(config('crypto_payments.store_id'))
            && filled(config('crypto_payments.api_key'))
            && filled(config('crypto_payments.webhook_secret'));
    }

    /**
     * Never accept a server URL or store ID from the browser. Both are
     * administrator-provisioned in config and intentionally not editable
     * through the CMS. Redirects from the API are disabled.
     */
    private function endpoint(string $path): string
    {
        if (! $this->configured()) {
            throw new RuntimeException('Crypto checkout is not configured.');
        }

        return (string) config('crypto_payments.url')
            .'/api/v1/stores/'.rawurlencode((string) config('crypto_payments.store_id'))
            .'/invoices'.$path;
    }

    private function api()
    {
        return Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'token '.config('crypto_payments.api_key'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(12)
            ->connectTimeout(4)
            ->withOptions(['allow_redirects' => false, 'verify' => true]);
    }

    public function createInvoice(CryptoPayment $payment): array
    {
        $response = $this->api()->post($this->endpoint(''), [
            'amount' => (string) $payment->amount,
            'currency' => $payment->currency,
            'metadata' => [
                'orderId' => $payment->reference,
                'orderUrl' => route('user.crypto.show', ['reference' => $payment->reference]),
            ],
            'checkout' => [
                'redirectURL' => route('user.crypto.show', ['reference' => $payment->reference]),
                'redirectAutomatically' => true,
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('The crypto payment provider could not create an invoice.');
        }

        $invoice = $response->json();
        $id = $invoice['id'] ?? null;
        $checkout = $invoice['checkoutLink'] ?? null;
        if (! is_string($id) || ! preg_match('/^[A-Za-z0-9_-]{6,160}$/D', $id)
            || ! is_string($checkout) || ! $this->trustedCheckoutUrl($checkout)) {
            throw new RuntimeException('The provider returned an invalid invoice.');
        }

        return ['id' => $id, 'checkoutLink' => $checkout];
    }

    public function getInvoice(string $id): array
    {
        if (! preg_match('/^[A-Za-z0-9_-]{6,160}$/D', $id)) {
            throw new RuntimeException('Invalid invoice reference.');
        }

        $response = $this->api()->get($this->endpoint('/'.rawurlencode($id)));
        if (! $response->successful()) {
            throw new RuntimeException('The provider could not verify this invoice.');
        }

        $invoice = $response->json();
        if (! is_array($invoice)) {
            throw new RuntimeException('Invalid provider invoice response.');
        }

        return $invoice;
    }

    private function trustedCheckoutUrl(string $url): bool
    {
        $expected = parse_url((string) config('crypto_payments.url'));
        $actual = parse_url($url);

        return is_array($expected) && is_array($actual)
            && ($actual['scheme'] ?? '') === 'https'
            && strtolower((string) ($actual['host'] ?? '')) === strtolower((string) ($expected['host'] ?? ''))
            && ($actual['port'] ?? 443) === ($expected['port'] ?? 443)
            && ! isset($actual['user'], $actual['pass'])
            && ! isset($actual['user']) && ! isset($actual['pass'])
            && ! isset($actual['fragment']);
    }
}
