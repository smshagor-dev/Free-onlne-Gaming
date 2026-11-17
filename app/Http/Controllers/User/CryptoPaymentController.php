<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Services\BTCPayClient;
use App\Services\CryptoPaymentReconciliation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CryptoPaymentController extends Controller
{
    public function index(Request $request, BTCPayClient $client): View
    {
        $payments = CryptoPayment::query()
            ->where('user_id', $request->user()->id)
            ->latest()->paginate(15);

        return view('crypto.index', [
            'payments' => $payments,
            'available' => $client->configured(),
            'currency' => config('crypto_payments.currency'),
            'minimum' => config('crypto_payments.minimum'),
            'maximum' => config('crypto_payments.maximum'),
        ]);
    }

    public function store(Request $request, BTCPayClient $client): RedirectResponse
    {
        if (! $client->configured()) {
            return back()->with('error', 'Crypto checkout is not yet enabled by the merchant.');
        }
        if ($request->user()->registration_type === 'demo') {
            abort(403, 'Demo accounts cannot make payments.');
        }

        $validated = $request->validate([
            'amount' => [
                'required', 'regex:/^\d{1,7}(?:\.\d{1,2})?$/D',
                'numeric', 'min:'.config('crypto_payments.minimum'),
                'max:'.config('crypto_payments.maximum'),
            ],
        ]);

        $raw = (string) $validated['amount'];
        [$integer, $decimal] = array_pad(explode('.', $raw, 2), 2, '');
        $amount = ((int) $integer).'.'.str_pad($decimal, 2, '0');

        // Basic anti-spam limit in addition to route throttling.
        if (CryptoPayment::query()->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count() >= 3) {
            throw ValidationException::withMessages([
                'amount' => 'Please finish your recent crypto invoices before creating another.',
            ]);
        }

        $payment = CryptoPayment::query()->create([
            'reference' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'provider' => 'btcpay',
            'amount' => $amount,
            'currency' => config('crypto_payments.currency'),
            'status' => 'pending',
        ]);

        try {
            $invoice = $client->createInvoice($payment);
            $payment->update([
                'invoice_id' => $invoice['id'],
                'checkout_url' => $invoice['checkoutLink'],
            ]);
        } catch (Throwable $e) {
            $payment->update(['status' => 'failed']);
            Log::warning('Crypto invoice creation unsuccessful', [
                'payment_id' => $payment->id,
                'failure_type' => get_class($e),
            ]);

            return redirect()->route('user.crypto.index')
                ->with('error', 'The payment provider could not create an invoice. No charge was made.');
        }

        return redirect()->route('user.crypto.show', ['reference' => $payment->reference])
            ->with('success', 'Crypto invoice created. Complete payment on the secure BTCPay checkout page.');
    }

    public function show(Request $request, string $reference): View
    {
        $payment = CryptoPayment::query()
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)->firstOrFail();

        return view('crypto.show', compact('payment'));
    }

    public function refresh(Request $request, string $reference, CryptoPaymentReconciliation $reconciler): RedirectResponse
    {
        $payment = CryptoPayment::query()
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)->firstOrFail();

        if ($payment->invoice_id === null) {
            return back()->with('error', 'This invoice was not issued by the provider.');
        }

        try {
            $reconciler->reconcile($payment);
        } catch (Throwable $e) {
            Log::warning('Crypto invoice refresh unsuccessful', [
                'payment_id' => $payment->id,
                'failure_type' => get_class($e),
            ]);

            return back()->with('error', 'Could not confirm the provider status right now.');
        }

        return redirect()->route('user.crypto.show', ['reference' => $payment->reference]);
    }
}
