@extends('layouts.admin')
@section('title', 'Crypto Payments')
@section('page_title', 'Crypto payment invoices')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">READ-ONLY PAYMENT RECORDS</span>
        <h1 style="margin-top:10px">Crypto payment invoices</h1>
        <p>Review BTCPay invoices and verified states without changing casino balances, financial ledgers or payment status manually.</p>
    </div>
</div>
<div class="cms-card">
    <div class="cms-card-head">
        <div><h2>Payment records</h2><p>Filter invoices by provider-verified state.</p></div>
    </div>
    <form method="GET" action="{{ route('admin.crypto.index') }}" class="cms-form-grid">
        <div class="cms-field"><label for="crypto-status">Payment status</label>
            <select name="status" id="crypto-status">
                <option value="">All statuses</option>
                @foreach(['pending','processing','settled','expired','invalid','failed','review'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }} ({{ number_format((int) ($stats[$status] ?? 0)) }})</option>
                @endforeach
            </select>
        </div>
        <div style="align-self:end"><button class="cms-button is-primary" type="submit">Filter</button></div>
    </form>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Reference</th><th>User</th><th>Amount</th><th>Status</th><th>Provider invoice</th><th>Created</th></tr></thead>
            <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><code>{{ $payment->reference }}</code></td>
                    <td>{{ $payment->user?->name ?: $payment->user?->email ?: 'Deleted user' }}</td>
                    <td>{{ $payment->amount }} {{ $payment->currency }}</td>
                    <td><span class="cms-badge {{ $payment->status === 'settled' ? 'is-green' : ($payment->status === 'review' ? 'is-amber' : '') }}">{{ ucfirst($payment->status) }}</span></td>
                    <td>{{ $payment->invoice_id ?: 'Not created' }}</td>
                    <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="cms-empty">No crypto payments recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $payments->links() }}</div>
    <div class="cms-secret-hint">This list shows invoice records only. It does not control wallets or betting deposits. Exceptional invoice statuses require operator review; don't treat a gross crypto payment as settled gambling funds.</div>
</div>
@endsection
