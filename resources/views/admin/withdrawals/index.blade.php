@extends('layouts.admin')
@section('title','Withdrawal review')
@section('page_title','Withdrawal requests')
@section('content')
<div class="cms-page-heading">
    <div><span class="cms-badge">KYC-GATED • ADMIN APPROVAL</span>
        <h1 style="margin-top:10px">Withdrawal requests</h1>
        <p>Review identity status, reserved funds and transfer instructions before recording any manual payout.</p>
    </div>
</div>
<div class="cms-card">
    <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="cms-form-grid">
        <div class="cms-field"><label for="withdraw-status">Status</label>
            <select name="status" id="withdraw-status">
                <option value="">All requests</option>
                @foreach(['pending','approved','rejected','cancelled','paid_recorded'] as $status)
                    <option value="{{ $status }}" @selected($selected === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="align-self:end"><button type="submit" class="cms-button is-primary">Filter</button></div>
    </form>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Reference</th><th>User</th><th>Amount</th><th>Asset/network</th><th>KYC</th><th>Status</th><th>Submitted</th><th>Review</th></tr></thead>
            <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td>{{ Str::limit($item->reference,15) }}</td>
                        <td>{{ $item->user?->name ?: $item->user?->email ?: 'Unknown user' }}</td>
                        <td>{{ $item->amount }} {{ $item->currency }}</td>
                        <td>{{ $item->asset }} / {{ $item->network }}</td>
                        <td>{{ $item->user?->kyc_verified ? 'Approved' : 'Not approved' }}</td>
                        <td>{{ str_replace('_',' ',ucfirst($item->status)) }}</td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                        <td><a class="cms-button" href="{{ route('admin.withdrawals.show', $item) }}">Review</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="cms-empty">No withdrawal requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px">{{ $requests->links() }}</div>
    <div class="cms-secret-hint">Approvals do not initiate a transfer. The operator must independently verify funding source, KYC, licensing, applicable rules and actual external transfer before recording the payout reference.</div>
</div>
@endsection
