@extends('layouts.app')
@section('title', 'Withdrawals | Free Games')
@section('styles')
<style>
.withdraw-portal{max-width:1030px;margin:0 auto;color:#edf6ff}
.withdraw-card{background:#122c46;border:1px solid #385777;padding:23px;border-radius:14px;margin-bottom:18px}
.withdraw-card h1{font-size:clamp(25px,3vw,35px);font-weight:800;margin:0 0 8px}
.withdraw-card p{color:#bad0e7;font-size:13px;line-height:1.7}
.withdraw-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.withdraw-field label{display:block;font-size:13px;margin:10px 0 5px;font-weight:700}
.withdraw-field input,.withdraw-field select{width:100%;padding:11px 12px;border-radius:8px;border:1px solid #476c8d;background:#10253a;color:#f1f7ff}
.withdraw-btn{margin:16px 0 0;display:inline-flex;padding:11px 18px;background:#168de5;border:0;border-radius:9px;color:white;font-weight:750;cursor:pointer}
.withdraw-btn:disabled{opacity:.45;cursor:not-allowed}
.withdraw-table-wrap{overflow-x:auto}
.withdraw-table{border-collapse:collapse;width:100%;min-width:725px}
.withdraw-table td,.withdraw-table th{padding:12px 14px;text-align:left;border-bottom:1px solid #33526e;font-size:13px}
.withdraw-table th{color:#b7d2eb}
.withdraw-tag{background:#244965;border-radius:6px;padding:4px 8px;font-size:12px}
@media(max-width:650px){.withdraw-grid{grid-template-columns:1fr}}
</style>
@endsection
@section('content')
<div class="withdraw-portal">
    <section class="withdraw-card">
        <h1>Crypto withdrawal requests</h1>
        <p>Requests require approved KYC, sufficient withdrawable funds, and administrator approval. A request reserves funds but does not immediately send cryptocurrency.</p>
        <div class="withdraw-grid">
            <div><small>Available balance</small><p><strong style="font-size:24px">{{ number_format((float) $availableBalance,2) }} {{ $currency }}</strong></p></div>
            <div><small>KYC status</small><p><strong>{{ $kycApproved ? 'Verified / approved' : 'Not verified' }}</strong></p></div>
        </div>
        @if(!$enabled)
            <p role="alert">Withdrawal requests are currently disabled by the operator pending merchant and compliance approval.</p>
        @elseif(!$kycApproved)
            <p role="alert">KYC verification is required. Complete the site's approved identity-verification process before requesting a withdrawal. This form remains locked.</p>
        @endif
        @if($errors->any())
            <div role="alert" style="color:#ffb4aa"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('user.withdrawals.store') }}">
            @csrf
            <div class="withdraw-grid">
                <div class="withdraw-field">
                    <label for="withdraw-amount">Amount ({{ $currency }})</label>
                    <input id="withdraw-amount" type="number" min="{{ $minimum }}" max="{{ $maximum }}" step=".01" name="amount" value="{{ old('amount') }}" required {{ ($enabled && $kycApproved) ? '' : 'disabled' }}>
                </div>
                <div class="withdraw-field">
                    <label for="withdraw-asset">Cryptocurrency</label>
                    <select id="withdraw-asset" name="asset" required {{ ($enabled && $kycApproved) ? '' : 'disabled' }}>
                        <option value="BTC" @selected(old('asset') === 'BTC')>Bitcoin (BTC)</option>
                        <option value="USDT" @selected(old('asset') === 'USDT')>Tether (USDT)</option>
                    </select>
                </div>
                <div class="withdraw-field">
                    <label for="withdraw-network">Network</label>
                    <select id="withdraw-network" name="network" required {{ ($enabled && $kycApproved) ? '' : 'disabled' }}>
                        <option value="bitcoin" @selected(old('network') === 'bitcoin')>Bitcoin mainnet (BTC)</option>
                        <option value="trc20" @selected(old('network') === 'trc20')>Tron TRC20 (USDT)</option>
                        <option value="erc20" @selected(old('network') === 'erc20')>Ethereum ERC20 (USDT)</option>
                    </select>
                </div>
                <div class="withdraw-field">
                    <label for="withdraw-destination">Receiving wallet address</label>
                    <input id="withdraw-destination" name="destination" type="text" autocomplete="off" spellcheck="false" maxlength="128" placeholder="Double-check network and address" required {{ ($enabled && $kycApproved) ? '' : 'disabled' }}>
                </div>
            </div>
            <p>Enter any amount from {{ $minimum }} to {{ $maximum }} {{ $currency }}, within your available balance. The amount is denominated in {{ $currency }}; crypto conversion, network fees and actual transfer must be arranged with the operator. Wrong wallet addresses may cause irreversible loss.</p>
            <button type="submit" class="withdraw-btn" {{ ($enabled && $kycApproved) ? '' : 'disabled' }}>Request withdrawal</button>
        </form>
    </section>
    <section class="withdraw-card">
        <h2 style="font-size:22px;font-weight:800">My withdrawal history</h2>
        <div class="withdraw-table-wrap">
            <table class="withdraw-table">
                <thead><tr><th>Reference</th><th>Amount</th><th>Method</th><th>Status</th><th>Submitted</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td title="{{ $item->reference }}">{{ Str::limit($item->reference, 14) }}</td>
                        <td>{{ $item->amount }} {{ $item->currency }}</td>
                        <td>{{ $item->asset }} / {{ $item->network }}</td>
                        <td><span class="withdraw-tag">{{ str_replace('_',' ',ucfirst($item->status)) }}</span></td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($item->status === 'pending')
                            <form method="POST" action="{{ route('user.withdrawals.cancel', ['reference' => $item->reference]) }}">
                                @csrf
                                <button type="submit" class="withdraw-btn" style="margin:0">Cancel request</button>
                            </form>
                            @elseif($item->status === 'paid_recorded')
                                <span title="Entered by admin, not independently blockchain-verified">Transfer recorded</span>
                            @elseif($item->status === 'rejected')
                                <span title="{{ $item->rejection_reason }}">Rejected</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No withdrawal requests yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px">{{ $requests->links() }}</div>
        <p><strong>Important:</strong> Admin approval is not a payment. “Transfer recorded” means the operator supplied an external transaction reference; this website has not independently verified the blockchain transfer or initiated a crypto payout.</p>
    </section>
</div>
@endsection
