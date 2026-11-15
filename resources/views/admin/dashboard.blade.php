@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Analytics dashboard')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">LIVE DATABASE METRICS</span>
        <h1 style="margin-top:10px">Dashboard overview</h1>
        <p>Monitor your members, game engagement, content and operational activity in one place.</p>
    </div>
    <a class="cms-button" href="{{ route('admin.setting.edit') }}"><i class="fa-solid fa-gear" aria-hidden="true"></i> Site settings</a>
</div>
<div class="cms-kpis">
    @foreach([
        ['Registered users', $stats['users'], number_format($stats['new_users']).' joined in the last 14 days'],
        ['Game opens', $stats['game_plays'], number_format($stats['games']).' managed games'],
        ['Email verified', $stats['verified_users'], number_format($stats['pending_users']).' awaiting verification'],
        ['Contact messages', $stats['messages'], number_format($stats['unread_messages']).' unread'],
        ['Pending deposits', $stats['deposits_pending'], 'Requires financial review'],
        ['Pending withdrawals', $stats['withdrawals_pending'], 'Requires financial review'],
        ['Lottery tickets', $stats['lottery_tickets'], number_format($stats['lotteries']).' lotteries'],
        ['Content pages', $stats['pages'], number_format($stats['banned_users']).' banned users'],
    ] as [$label,$number,$note])
        <div class="cms-kpi">
            <span class="cms-kpi-label">{{ $label }}</span>
            <strong class="cms-kpi-number">{{ number_format($number) }}</strong>
            <span class="cms-kpi-note">{{ $note }}</span>
        </div>
    @endforeach
</div>
<div class="cms-two-col">
    @foreach([
        ['registrations', 'Daily registrations', 'New accounts created', ''],
        ['plays', 'Game opens', 'Recorded game sessions', 'is-violet'],
        ['deposits', 'Deposit amounts', 'Recorded deposits, not revenue', 'is-teal'],
        ['withdrawals', 'Withdrawal amounts', 'Recorded withdrawals', 'is-amber'],
    ] as [$key,$heading,$description,$modifier])
        @php
            $maxValue = max(1, max(array_column($daily, $key)));
        @endphp
        <section class="cms-card" aria-label="{{ $heading }} chart">
            <div class="cms-card-head">
                <div><h2>{{ $heading }}</h2><p>{{ $description }} · Last 14 days</p></div>
                <span class="cms-badge">{{ number_format(array_sum(array_column($daily, $key)), in_array($key,['deposits','withdrawals']) ? 2 : 0) }} total</span>
            </div>
            <div class="cms-chart {{ $modifier }}" role="img" aria-label="{{ $heading }} for the last 14 days. Hover or focus a bar for a daily value.">
                @foreach($daily as $day)
                    <div class="cms-chart-col">
                        <span tabindex="0" class="cms-chart-bar"
                            style="height:{{ max(2, round(100 * $day[$key] / $maxValue, 2)) }}%"
                            aria-label="{{ $day['label'] }}: {{ number_format($day[$key], in_array($key,['deposits','withdrawals']) ? 2 : 0) }}"
                            title="{{ $day['label'] }}: {{ number_format($day[$key], in_array($key,['deposits','withdrawals']) ? 2 : 0) }}"></span>
                    </div>
                @endforeach
            </div>
            <div class="cms-chart-axis"><span>{{ $daily[0]['label'] }}</span><span>{{ $daily[6]['label'] }}</span><span>{{ $daily[13]['label'] }}</span></div>
            <p class="cms-chart-caption">Each bar represents one calendar day (server timezone).</p>
        </section>
    @endforeach
</div>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Quick operations</h2><p>Direct access to your main CMS workflows</p></div></div>
    <div class="cms-shortcuts">
        <a href="{{ route('admin.games.create') }}">+ Add game</a>
        <a href="{{ route('admin.pages.create') }}">+ Create page</a>
        <a href="{{ route('admin.banners.create') }}">+ Add banner</a>
        <a href="{{ route('admin.users.index') }}">Manage members</a>
        <a href="{{ route('admin.contacts.index') }}">Contact inbox</a>
        <a href="{{ route('admin.setting.edit', ['tab'=>'mail']) }}">SMTP configuration</a>
        <a href="{{ route('admin.logs.index') }}">System logs</a>
    </div>
</section>
<div class="cms-two-col">
    <section class="cms-card">
        <div class="cms-card-head">
            <div><h2>Newest members</h2><p>Latest registered user accounts</p></div>
            <a href="{{ route('admin.users.index') }}">View all</a>
        </div>
        <div class="cms-table-wrap">
            <table class="cms-table">
                <thead><tr><th>User</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                @forelse($recentUsers as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->name ?: ($user->username ?: 'User #'.$user->id) }}</a>
                            <div style="font-size:11px;color:#8b9cb1">{{ $user->email }}</div>
                        </td>
                        <td><span class="cms-badge {{ $user->is_banned ? 'is-red' : ($user->is_verified ? 'is-green' : 'is-amber') }}">
                            {{ $user->is_banned ? 'Banned' : ($user->is_verified ? 'Verified' : 'Pending') }}
                        </span></td>
                        <td style="white-space:nowrap">{{ $user->created_at?->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="cms-empty">No members yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="cms-card">
        <div class="cms-card-head"><div><h2>Popular managed games</h2><p>Top games based on recorded opens</p></div><a href="{{ route('admin.games.index') }}">Manage games</a></div>
        <div class="cms-table-wrap">
            <table class="cms-table">
                <thead><tr><th>Game</th><th>Opens</th></tr></thead>
                <tbody>
                    @forelse($topGames as $entry)
                        <tr>
                            <td>{{ $entry->game?->name ?? 'Deleted game #'.$entry->game_id }}</td>
                            <td>{{ number_format($entry->total_plays) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="cms-empty">No recorded game opens.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="cms-secret-hint">Recorded deposit total: {{ number_format($stats['deposits_total'], 2) }} · Recorded withdrawal total: {{ number_format($stats['withdrawals_total'], 2) }}. These are not recognized revenue or profit.</div>
    </section>
</div>
@endsection
