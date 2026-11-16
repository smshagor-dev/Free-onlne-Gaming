@extends('layouts.admin')
@section('title', 'User activity reports')
@section('page_title', 'User activity')
@section('content')
<div class="cms-page-heading">
    <div><span class="cms-badge">REAL ACTIVITY RECORDS</span>
        <h1 style="margin-top:9px">User activity reports</h1>
        <p>Review member sign-ins and game activity from recorded events. Events that were not logged cannot be reconstructed.</p>
    </div>
    <a class="cms-button" href="{{ route('admin.users.index') }}">Manage members</a>
</div>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Report filters</h2><p>Select an optional user ID and up to 90 days of history.</p></div></div>
    <form method="GET" action="{{ route('admin.user-activity.index') }}" class="cms-form-grid">
        <div class="cms-field">
            <label for="activity-user">Member database ID (optional)</label>
            <input id="activity-user" type="number" name="user_id" min="1" value="{{ $filters['user_id'] ?? '' }}" placeholder="All members">
            @error('user_id')<small role="alert">{{ $message }}</small>@enderror
        </div>
        <div class="cms-field">
            <label for="activity-from">Start date</label>
            <input id="activity-from" type="date" name="from" value="{{ $from }}" max="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="cms-field">
            <label for="activity-to">End date</label>
            <input id="activity-to" type="date" name="to" value="{{ $to }}" max="{{ now()->format('Y-m-d') }}">
        </div>
        <div style="align-self:end">
            <button class="cms-button is-primary" type="submit">Apply filters</button>
            <a class="cms-button" href="{{ route('admin.user-activity.index') }}">Reset</a>
        </div>
    </form>
</section>
<div class="cms-kpis">
    <div class="cms-kpi"><span class="cms-kpi-label">Recorded sign-ins</span><strong class="cms-kpi-number">{{ number_format($counts['logins']) }}</strong><span class="cms-kpi-note">Within selected dates</span></div>
    <div class="cms-kpi"><span class="cms-kpi-label">Game activity records</span><strong class="cms-kpi-number">{{ number_format($counts['plays']) }}</strong><span class="cms-kpi-note">Not all plays are independently tracked</span></div>
    <div class="cms-kpi"><span class="cms-kpi-label">Members with logins</span><strong class="cms-kpi-number">{{ number_format($counts['members']) }}</strong><span class="cms-kpi-note">Distinct registered members</span></div>
</div>
<div class="cms-two-col">
    @foreach([['logins','Recorded sign-ins',''],['plays','Game activity','is-violet']] as [$metric,$title,$modifier])
        @php($peak = max(1,max(array_column($days,$metric))))
        <section class="cms-card" aria-label="{{ $title }}">
            <div class="cms-card-head">
                <div><h2>{{ $title }}</h2><p>Daily recorded events</p></div>
                <span class="cms-badge">{{ number_format($counts[$metric]) }} total</span>
            </div>
            <div class="cms-chart {{ $modifier }}" role="img" aria-label="Daily {{ $title }} from {{ $from }} through {{ $to }}">
                @foreach($days as $day)
                    <div class="cms-chart-col">
                        <span tabindex="0" class="cms-chart-bar"
                            style="height:{{ max(2,round(100*$day[$metric]/$peak,2)) }}%"
                            aria-label="{{ $day['label'] }}: {{ $day[$metric] }}"
                            title="{{ $day['date'] }}: {{ $day[$metric] }}"></span>
                    </div>
                @endforeach
            </div>
            <div class="cms-chart-axis"><span>{{ $days[0]['label'] }}</span><span>{{ $days[count($days)-1]['label'] }}</span></div>
        </section>
    @endforeach
</div>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Recent sign-ins</h2><p>Member, browser, date and recorded location</p></div></div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Member</th><th>Date</th><th>Browser / device</th><th>Location</th><th>IP address</th></tr></thead>
            <tbody>
                @forelse($logins as $entry)
                <tr>
                    <td>@if($entry->user)<a href="{{ route('admin.users.view', $entry->user_id) }}">{{ $entry->user->name ?: $entry->user->email }}</a>@else Unknown user @endif</td>
                    <td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ implode(' / ',array_filter([$entry->browser,$entry->device_type])) ?: 'Unrecorded' }}</td>
                    <td>{{ implode(', ',array_filter([$entry->city,$entry->country])) ?: 'Unknown' }}</td>
                    <td>{{ $entry->ip_address }}</td>
                </tr>
                @empty
                    <tr><td colspan="5" class="cms-empty">No sign-in records in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:15px">{{ $logins->links() }}</div>
</section>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Recent game activity</h2><p>Activity on locally managed games with persisted records</p></div></div>
    <div class="cms-table-wrap">
        <table class="cms-table">
            <thead><tr><th>Member</th><th>Game</th><th>Recorded at</th><th>Points</th></tr></thead>
            <tbody>
                @forelse($plays as $entry)
                <tr>
                    <td>@if($entry->user)<a href="{{ route('admin.users.view', $entry->user_id) }}">{{ $entry->user->name ?: $entry->user->email }}</a>@else Unknown user @endif</td>
                    <td>{{ $entry->game?->name ?? 'Game no longer available' }}</td>
                    <td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ number_format((float)$entry->points,0) }}</td>
                </tr>
                @empty
                    <tr><td colspan="4" class="cms-empty">No game records in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:15px">{{ $plays->links() }}</div>
    <div class="cms-secret-hint">This report intentionally excludes account passwords, reset tokens, verification codes and 2FA secrets. Historical events that were never stored are not included.</div>
</section>
@endsection
