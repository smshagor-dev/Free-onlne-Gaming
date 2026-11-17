@extends('layouts.app')
@section('title', 'Crypto Payment Status | Free Games')
@section('styles')
<style>
.crypto-detail{color:#ecf5ff;max-width:790px;margin:0 auto;padding:10px 0}
.crypto-detail-card{padding:26px;border:1px solid #355477;border-radius:16px;background:#102943}
.crypto-detail h1{font-size:clamp(24px,3vw,34px);font-weight:800;margin-bottom:12px}
.crypto-detail dl{display:grid;grid-template-columns:160px 1fr;gap:12px;margin:25px 0}
.crypto-detail dt{color:#a6c2df}.crypto-detail dd{overflow-wrap:anywhere}
.crypto-detail-action{display:inline-block;padding:12px 22px;background:#148fe1;color:#fff;border-radius:10px;font-weight:800;margin:5px 12px 5px 0}
.crypto-detail-action.secondary{border:1px solid #456989;background:#223f59}
.crypto-detail-note{font-size:13px;color:#b5cde5;line-height:1.7;margin-top:16px}
</style>
@endsection
@section('content')
<div class="crypto-detail"><div class="crypto-detail-card">
    <h1>Crypto payment status</h1>
    <dl>
        <dt>Payment reference</dt><dd>{{ $payment->reference }}</dd>
        <dt>Amount</dt><dd><strong>{{ $payment->amount }} {{ $payment->currency }}</strong></dd>
        <dt>Provider</dt><dd>NOWPayments Server</dd>
        <dt>Status</dt><dd><strong style="text-transform:capitalize">{{ $payment->status }}</strong></dd>
        <dt>Last checked</dt><dd>{{ $payment->last_checked_at?->format('Y-m-d H:i') ?: 'Not checked yet' }}</dd>
    </dl>
    @if($payment->checkout_url && in_array($payment->status, ['pending','processing'], true))
        <a class="crypto-detail-action" href="{{ $payment->checkout_url }}" rel="noopener noreferrer" target="_blank">Continue to NOWPayments checkout</a>
    @endif
    @if($payment->invoice_id)
        <form method="POST" action="{{ route('user.crypto.refresh', ['reference' => $payment->reference]) }}" style="display:inline">
            @csrf
            <button class="crypto-detail-action secondary" type="submit">Check provider status</button>
        </form>
    @endif
    <a class="crypto-detail-action secondary" href="{{ route('user.crypto.index') }}">All payments</a>
    @if($payment->status === 'settled')
        <p class="crypto-detail-note" role="status">The provider reports this invoice as settled. This payment record is not a casino wallet deposit or withdrawable balance credit.</p>
    @elseif($payment->status === 'review')
        <p class="crypto-detail-note" role="alert">This invoice requires manual review (late, partial, overpaid or other exceptional status). Contact support with your payment reference.</p>
    @else
        <p class="crypto-detail-note">Payment status changes after NOWPayments confirms the invoice. A browser redirect alone never marks a payment as settled. You may safely use Check provider status if needed.</p>
    @endif
</div></div>
@endsection
