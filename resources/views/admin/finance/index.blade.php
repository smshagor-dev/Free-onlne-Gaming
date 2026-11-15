@extends('layouts.admin')
@section('title','Financial oversight')
@section('page_title','Financial records')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">READ-ONLY LEDGER VIEW</span>
        <h1 style="margin-top:10px">Financial oversight</h1>
        <p>Review recorded deposits, withdrawals and transaction references without bypassing settlement or audit workflows.</p>
    </div>
</div>
<div class="cms-kpis">
    @foreach([
        ['Deposit records', $totals['deposits']['count'], number_format($totals['deposits']['amount'],2).' recorded amount'],
        ['Withdrawal records', $totals['withdrawals']['count'], number_format($totals['withdrawals']['amount'],2).' recorded amount'],
        ['Ledger transactions', $totals['transactions']['count'], number_format($totals['transactions']['amount'],2).' recorded amount'],
    ] as [$label,$number,$note])
        <div class="cms-kpi"><span class="cms-kpi-label">{{ $label }}</span><strong class="cms-kpi-number">{{ number_format($number) }}</strong><span class="cms-kpi-note">{{ $note }}</span></div>
    @endforeach
</div>
<div class="cms-card">
    <div class="cms-settings-tabs" aria-label="Financial record type">
        @foreach(['deposits'=>'Deposits','withdrawals'=>'Withdrawals','transactions'=>'Transactions'] as $tab=>$label)
            <a class="cms-button {{ $kind === $tab ? 'is-primary' : '' }}" href="{{ route('admin.finance.index', ['kind'=>$tab]) }}">{{ $label }}</a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.finance.index') }}" class="cms-form-grid" style="margin-bottom:17px">
        <input type="hidden" name="kind" value="{{ $kind }}">
        <div class="cms-field"><label for="finance-search">Transaction reference</label>
            <input type="search" name="search" id="finance-search" maxlength="80" value="{{ $search }}" placeholder="Search exact or partial reference">
        </div>
        <div style="display:flex;align-items:end"><button type="submit" class="cms-button is-primary">Search records</button></div>
    </form>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Reference</th><th>Member</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($records as $item)
                    <tr>
                        <td>{{ $item->transaction_number ?: ('#'.$item->id) }}</td>
                        <td>
                            @if($item->user)
                                <a href="{{ route('admin.users.view', $item->user->id) }}">{{ $item->user->name ?: $item->user->email }}</a>
                            @else
                                <span style="color:#8497aa">Unknown / removed member</span>
                            @endif
                        </td>
                        <td style="font-variant-numeric:tabular-nums">{{ number_format((float) $item->amount, 2) }}</td>
                        <td><span class="cms-badge {{ in_array($item->status, ['approved','completed','success']) ? 'is-green' : (($item->status ?? '') === 'pending' ? 'is-amber' : 'is-red') }}">{{ $item->status ?: 'Not specified' }}</span></td>
                        <td>{{ $item->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="cms-empty">No matching records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $records->links() }}</div>
    <div class="cms-secret-hint">Amounts are unadjusted database totals. This screen intentionally cannot approve, reject, refund or alter a financial record.</div>
</div>
@endsection
