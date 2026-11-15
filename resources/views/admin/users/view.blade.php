@extends('layouts.admin')
@section('title','User profile')
@section('page_title','Member profile')
@section('content')
<div class="cms-page-heading">
    <div>
        <span class="cms-badge">MEMBER #{{ $user->id }}</span>
        <h1 style="margin-top:9px">{{ $user->name ?: ($user->username ?: 'Member') }}</h1>
        <p>{{ $user->email }} · {{ $user->is_banned ? 'Suspended' : 'Account active' }}</p>
    </div>
    <div class="cms-shortcuts">
        <a href="{{ route('admin.users.edit', $user->id) }}">Edit profile</a>
        <a href="{{ route('admin.users.logins', $user->id) }}">Sign-in history</a>
        <a href="{{ route('admin.users.index') }}">All members</a>
    </div>
</div>
<div class="cms-kpis">
    @foreach([
        ['Games played',$user->game_opens_count,'Recorded game opens'],
        ['Favorites',$user->favorites_count,'Saved games'],
        ['Login records',$user->user_logins_count,'Recorded sign-ins'],
        ['Deposits',$user->deposits_count,'Deposit records'],
    ] as [$label,$count,$note])
        <div class="cms-kpi">
            <div class="cms-kpi-label">{{ $label }}</div>
            <strong class="cms-kpi-number">{{ number_format($count) }}</strong>
            <div class="cms-kpi-note">{{ $note }}</div>
        </div>
    @endforeach
</div>
<section class="cms-card">
    <div class="cms-card-head">
        <div><h2>Identity & account</h2><p>Authorized administrator view of the member's saved profile fields.</p></div>
        <span class="cms-badge {{ $user->is_verified ? 'is-green' : 'is-amber' }}">{{ $user->is_verified ? 'Email verified' : 'Verification pending' }}</span>
    </div>
    @php
        $fields = [
            'Internal ID' => $user->id,
            'Public user ID' => $user->user_id,
            'Full name' => $user->name,
            'Username' => $user->username,
            'Email' => $user->email,
            'Mobile number' => $user->mobile_number,
            'Date of birth' => $user->date_of_birth?->format('Y-m-d'),
            'Registered via' => $user->registration_type,
            'Email verified at' => $user->email_verified_at?->format('Y-m-d H:i'),
            'Account verified' => $user->is_verified ? 'Yes' : 'No',
            'KYC status' => $user->kyc_verified ? 'Verified' : 'Not verified',
            'KYC submissions' => $user->kyc_submissions_count,
            'Banned' => $user->is_banned ? 'Yes' : 'No',
            'Ban reason' => $user->ban_reason,
            'Two-factor authentication' => $user->google2fa_status ? 'Enabled' : 'Disabled',
            'Country' => $user->country,
            'Currency' => $user->currency,
            'Country (device)' => $user->user_country,
            'Region' => $user->user_region,
            'City' => $user->user_city,
            'Location' => $user->user_location,
            'Last IP address' => $user->user_ip,
            'Browser' => $user->user_browser,
            'Device type' => $user->device_type,
            'Device name' => $user->device_name,
            'Last login' => $user->last_login_at?->format('Y-m-d H:i'),
            'Referral code' => $user->referral_code,
            'Referred by' => $user->referrer,
            'Created at' => $user->created_at?->format('Y-m-d H:i'),
            'Last updated' => $user->updated_at?->format('Y-m-d H:i'),
        ];
    @endphp
    <dl class="cms-profile-grid">
        @foreach($fields as $name=>$value)
            <div class="cms-info"><dt>{{ $name }}</dt><dd>{{ filled($value) || $value === 0 ? $value : 'Not provided' }}</dd></div>
        @endforeach
    </dl>
</section>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Wallet, points & bonuses</h2><p>Stored balances only. Financial edits must use existing approved workflows.</p></div></div>
    <dl class="cms-profile-grid">
        @foreach([
            'Balance' => $user->balance,
            'Available balance' => $user->available_balance,
            'Bonus balance' => $user->bonus_balance,
            'Game points' => $user->points,
            'Available points' => $user->available_points,
            'VIP bonus' => $user->vip_bonus,
            'Cashback' => $user->cashback,
            'Level ID' => $user->level_id,
        ] as $name=>$value)
            <div class="cms-info"><dt>{{ $name }}</dt><dd>{{ $value ?? 'Not set' }}</dd></div>
        @endforeach
    </dl>
</section>
<section class="cms-card">
    <div class="cms-card-head"><div><h2>Recent account sign-ins</h2><p>Last six recorded login events.</p></div><a href="{{ route('admin.users.logins',$user->id) }}">Full history</a></div>
    <div class="cms-table-wrap"><table class="cms-table">
        <thead><tr><th>Date</th><th>Location</th><th>Device</th><th>IP address</th></tr></thead>
        <tbody>
        @forelse($recentLogins as $login)
            <tr>
                <td>{{ $login->created_at?->format('Y-m-d H:i') }}</td>
                <td>{{ implode(', ', array_filter([$login->city,$login->region,$login->country])) ?: 'Unknown' }}</td>
                <td>{{ implode(' · ', array_filter([$login->browser,$login->device_type,$login->device_name])) ?: 'Unknown' }}</td>
                <td>{{ $login->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="cms-empty">No recorded login events yet.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <p class="cms-secret-hint">Passwords, reset tokens, email verification codes and 2FA recovery secrets are intentionally never displayed.</p>
</section>
@endsection
