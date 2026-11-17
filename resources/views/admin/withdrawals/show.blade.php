@extends('layouts.admin')
@section('title','Review withdrawal')
@section('page_title','Withdrawal review')
@section('content')
<div class="cms-page-heading">
    <div><span class="cms-badge">FINANCIAL REQUEST • MANUAL PAYOUT</span>
        <h1 style="margin-top:10px">Review withdrawal</h1>
        <p>Verify account eligibility and beneficiary address before taking an irreversible action.</p>
    </div>
    <a class="cms-button" href="{{ route('admin.withdrawals.index') }}">All withdrawal requests</a>
</div>
<div class="cms-card">
    <div class="cms-card-head"><div><h2>{{ $withdrawal->reference }}</h2><p>Current status: {{ str_replace('_',' ',ucwords($withdrawal->status)) }}</p></div></div>
    <div class="cms-form-grid">
        <div class="cms-field"><label>User</label><p>{{ $withdrawal->user?->name }} ({{ $withdrawal->user?->email }})</p></div>
        <div class="cms-field"><label>KYC</label><p>{{ $withdrawal->user?->kyc_verified ? 'Verified / approved' : 'NOT VERIFIED — Do not approve' }}</p></div>
        <div class="cms-field"><label>Requested amount</label><p>{{ $withdrawal->amount }} {{ $withdrawal->currency }}</p></div>
        <div class="cms-field"><label>Asset / chain</label><p>{{ $withdrawal->asset }} / {{ $withdrawal->network }}</p></div>
        <div class="cms-field"><label>Member wallet address (sensitive)</label>
            <p style="overflow-wrap:anywhere;user-select:all"><code>{{ $withdrawal->destination }}</code></p>
            <small>Only shown to authenticated administrators. Address is encrypted at rest.</small>
        </div>
        <div class="cms-field"><label>Operator-recorded transfer reference</label><p>{{ $withdrawal->payout_txid ?: 'Not recorded' }}</p></div>
        <div class="cms-field"><label>Rejection reason</label><p>{{ $withdrawal->rejection_reason ?: 'None' }}</p></div>
        <div class="cms-field"><label>Reviewer</label><p>{{ $withdrawal->reviewer?->name ?: 'Not reviewed' }}</p></div>
    </div>
</div>
@if($withdrawal->status === 'pending')
<div class="cms-card">
    <div class="cms-card-head"><h2>Approve for manual processing</h2></div>
    <p class="cms-secret-hint">Approval keeps the requested amount reserved. It NEVER triggers an automatic NOWPayments payout or transfers cryptocurrency.</p>
    <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
        @csrf
        <button type="submit" class="cms-button is-primary">Approve withdrawal request</button>
    </form>
</div>
@endif
@if(in_array($withdrawal->status, ['pending','approved'], true))
<div class="cms-card">
    <div class="cms-card-head"><h2>Reject and release reserved funds</h2></div>
    <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
        @csrf
        <div class="cms-field"><label for="withdraw-reason">Reason for rejection</label>
            <textarea id="withdraw-reason" name="reason" rows="3" maxlength="500" required>{{ old('reason') }}</textarea>
        </div>
        <button type="submit" class="cms-button">Reject withdrawal</button>
    </form>
</div>
@endif
@if($withdrawal->status === 'approved')
<div class="cms-card">
    <div class="cms-card-head"><h2>Record completed external crypto transfer</h2></div>
    <p class="cms-secret-hint">Use ONLY after independently sending funds outside this application and checking chain/asset/address. Admin entry is an attestation, not cryptographic verification. This action finalizes the held amount in the local wallet.</p>
    <form method="POST" action="{{ route('admin.withdrawals.paid', $withdrawal) }}">
        @csrf
        <div class="cms-field"><label for="withdraw-txid">Blockchain transaction hash / unique transfer reference</label>
            <input id="withdraw-txid" name="txid" type="text" minlength="12" maxlength="160" required value="{{ old('txid') }}" autocomplete="off">
        </div>
        <label style="display:block;margin:12px 0"><input type="checkbox" name="confirm_external_transfer" value="1" required>
            I confirm that this payout was actually sent externally and the beneficiary, network and amount were independently checked.
        </label>
        <button class="cms-button is-primary" type="submit">Record manual transfer</button>
    </form>
</div>
@endif
@endsection
