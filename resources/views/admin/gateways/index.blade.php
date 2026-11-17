@extends('layouts.admin')
@section('title', 'Payment gateways')
@section('page_title', 'Payment gateways')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">CONFIGURATION ONLY</span>
        <h1 style="margin-top:10px">Payment gateway management</h1>
        <p>Keep a verified inventory of payment provider settings. This screen does not activate checkout, deposits, or payouts.</p>
    </div>
    <a class="cms-button is-primary" href="{{ route('admin.gateways.create') }}">Add gateway configuration</a>
</div>
<div class="cms-card">
    <div class="cms-secret-hint">
        <strong>Provider eligibility first:</strong> A casino or wager-related merchant needs appropriate licensing and an explicit provider agreement before accepting payments. New records are stored inactive. An existing gateway's status is not changed by this configuration screen; an active database flag alone does not make it a working checkout.
        <br>Never enter API secrets or payment card data in these fields. A separate encrypted credentials store and verified webhook integration are required for live processing.
    </div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Gateway</th><th>Currency</th><th>Allowed amount range</th><th>Stored flag</th><th>Checkout</th><th>Manage</th></tr></thead>
            <tbody>
                @forelse($gateways as $gateway)
                    <tr>
                        <td><strong>{{ $gateway->name }}</strong><small style="display:block;color:#7d94ad">ID #{{ $gateway->id }}</small></td>
                        <td>{{ strtoupper($gateway->currency) }} ({{ $gateway->symbol }})</td>
                        <td>{{ number_format((float) $gateway->min_amount, 2) }} – {{ number_format((float) $gateway->max_amount, 2) }}</td>
                        <td>
                            <span class="cms-badge {{ $gateway->status ? 'is-amber' : '' }}">{{ $gateway->status ? 'Existing flag enabled' : 'Inactive' }}</span>
                        </td>
                        <td><span class="cms-badge">Not integrated</span></td>
                        <td><a class="cms-button" href="{{ route('admin.gateways.edit', $gateway) }}">Edit metadata</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="cms-empty">No payment gateway records. Add configuration for a provider after verifying your merchant eligibility.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:14px">{{ $gateways->links() }}</div>
</div>
<div class="cms-card">
    <div class="cms-card-head"><div><h2>Before a live checkout can be enabled</h2><p>Confirm the exact merchant account and intended payment use.</p></div></div>
    <div class="cms-secret-hint">
        Required integration steps: provider-approved business category, country/currency eligibility, test keys,
        provider-hosted checkout, signed webhook verification, idempotent order settlement, refunds/chargebacks,
        reconciliation, and payment-specific audit logs. No user balance or casino transaction is credited from this configuration page.
    </div>
    <a class="cms-button" href="{{ route('admin.finance.index') }}">Open financial records</a>
</div>
@endsection
