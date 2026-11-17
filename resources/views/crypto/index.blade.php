@extends('layouts.app')
@section('title', 'Crypto Payments | Free Games')
@section('styles')
<style>
.crypto-page{max-width:1080px;margin:0 auto;color:#eaf4ff}
.crypto-panel{background:#122b43;border:1px solid #395675;padding:24px;border-radius:16px;margin-bottom:18px}
.crypto-panel h1{font-size:clamp(26px,3.5vw,36px);font-weight:800;margin:0 0 7px}
.crypto-panel p{color:#b6cde3;margin:8px 0 20px}
.crypto-amount-grid{display:flex;gap:10px;flex-wrap:wrap;margin:10px 0 15px}
.crypto-amount-grid button{padding:10px 17px;background:#224462;border:1px solid #4e789e;color:#fff;border-radius:10px;font-weight:800}
.crypto-field{display:block;max-width:400px}
.crypto-field input{display:block;width:100%;background:#091e33;border:1px solid #587899;color:#fff;border-radius:10px;padding:13px 15px;margin-top:8px}
.crypto-submit{background:#0e96ef;border:0;color:#fff;padding:12px 24px;border-radius:10px;font-weight:800;margin-top:14px}
.crypto-submit:disabled{background:#465970;cursor:not-allowed}
.crypto-table-wrap{overflow-x:auto}
.crypto-table{width:100%;min-width:650px;border-collapse:collapse}
.crypto-table td,.crypto-table th{padding:14px;text-align:left;border-bottom:1px solid #365473}
.crypto-table th{color:#b1d6f7}
.crypto-chip{display:inline-flex;padding:4px 9px;background:#284662;border-radius:9px;font-size:12px;text-transform:capitalize}
.crypto-note{color:#aac1d6;font-size:13px;line-height:1.6}
.crypto-link{color:#85caff;text-decoration:underline}
</style>
@endsection
@section('content')
<div class="crypto-page">
    <div class="crypto-panel">
        <h1>Pay with crypto</h1>
        <p>Enter your preferred amount to create a Bitcoin / Lightning payment invoice. Complete payment securely on the BTCPay checkout page.</p>
        @if(!$available)
            <p role="status" class="crypto-note">Crypto checkout is currently unavailable. The merchant must configure and enable a BTCPay store before payments can be accepted.</p>
        @endif
        <form action="{{ route('user.crypto.store') }}" method="POST">
            @csrf
            <label class="crypto-field" for="crypto-amount">
                Amount ({{ $currency }})
                <input id="crypto-amount" name="amount" type="number" min="{{ $minimum }}" max="{{ $maximum }}" step="0.01" inputmode="decimal"
                       value="{{ old('amount', '10.00') }}" required {{ $available ? '' : 'disabled' }} aria-describedby="crypto-amount-note">
            </label>
            <div class="crypto-amount-grid" aria-label="Example amounts">
                @foreach(['10', '20', '55', '100'] as $example)
                    <button type="button" data-crypto-amount="{{ $example }}" {{ $available ? '' : 'disabled' }}>{{ $example }} {{ $currency }}</button>
                @endforeach
            </div>
            <div class="crypto-note" id="crypto-amount-note">Choose any amount between {{ $minimum }} and {{ $maximum }} {{ $currency }}. Presets are optional and don't limit custom amounts.</div>
            @error('amount')<p role="alert">{{ $message }}</p>@enderror
            <button class="crypto-submit" type="submit" {{ $available ? '' : 'disabled' }}>Create secure invoice</button>
        </form>
    </div>
    <div class="crypto-panel">
        <h2 style="font-size:22px;font-weight:800;margin:0 0 13px">My payment history</h2>
        <div class="crypto-table-wrap">
            <table class="crypto-table">
                <thead><tr><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th><th>Details</th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ Str::limit($payment->reference, 14) }}</td>
                        <td>{{ $payment->amount }} {{ $payment->currency }}</td>
                        <td><span class="crypto-chip">{{ $payment->status }}</span></td>
                        <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a class="crypto-link" href="{{ route('user.crypto.show', ['reference' => $payment->reference]) }}">View payment</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No crypto payment invoices have been created yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:15px">{{ $payments->links() }}</div>
    </div>
    <p class="crypto-note">Payments are tracked separately from casino deposits and wagering balances. A confirmed crypto invoice does not automatically increase your withdrawable or betting balance. Network fees, available coins and confirmations are shown on BTCPay checkout.</p>
</div>
<script>
document.querySelectorAll('[data-crypto-amount]').forEach(button => {
    button.addEventListener('click', () => {
        const amount = document.getElementById('crypto-amount');
        if (amount && !amount.disabled) amount.value = button.dataset.cryptoAmount;
    });
});
</script>
@endsection
