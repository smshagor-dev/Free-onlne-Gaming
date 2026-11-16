@php
    $player = auth()->user();
    $portalGroups = [
        'Overview' => [
            ['Dashboard', 'user.dashboard', 'user.dashboard', '◫'],
        ],
        'My games' => [
            ['Browse games', 'playable.index', 'playable.*', '▣'],
            ['Casino games', 'casino.games.index', 'casino.games.*', '♠'],
            ['My casino activity', 'user.casino.activity', 'user.casino.*', '◷'],
            ['Favorites', 'user.games.viewFavorites', 'user.games.viewFavorites', '♡'],
            ['Saved library', 'catalog.user.library', 'catalog.user.library', '▦'],
            ['Recently played', 'user.games.lastplay', 'user.games.lastplay', '▷'],
            ['Bookmarks', 'user.games.bookmarks', 'user.games.bookmarks', '☆'],
        ],
        'Rewards & lottery' => [
            ['My tickets', 'user.lottaries.my', 'user.lottaries.my', '◈'],
            ['Winning results', 'user.lottaries.mywin', 'user.lottaries.mywin', '✦'],
            ['Achievements', 'user.achievement', 'user.achievement', '♜'],
            ['My levels', 'user.levels', 'user.levels', '⬡'],
        ],
        'My account' => [
            ['Profile', 'user.profile', 'user.profile', '♙'],
            ['Edit profile', 'user.profile.edit', 'user.profile.edit', '✎'],
            ['Notifications', 'user.notifications.index', 'user.notifications.*', '✉'],
            ['Gaming preferences', 'catalog.user.settings', 'catalog.user.settings', '⚙'],
            ['Account security', '2fa.setup', '2fa.setup', '⚿'],
        ],
    ];
@endphp
<a class="skip-link" href="#main-content">Skip to content</a>
<div class="cms-shell">
    <button class="cms-mobile-backdrop" type="button" data-cms-backdrop aria-label="Close navigation"></button>
    @include('layouts.partials.portal-sidebar', [
        'portalRole' => 'user',
        'portalHomeRoute' => 'user.dashboard',
        'portalTitle' => 'Free Games',
        'portalSubtitle' => 'PLAYER COMMAND CENTER',
        'portalLogoutRoute' => 'logout',
        'portalGroups' => $portalGroups,
    ])
    <div class="cms-stage">
        <header class="cms-topbar">
            <div class="cms-top-title">
                <button class="cms-menu-toggle" type="button"
                    data-cms-menu-toggle aria-label="Toggle sidebar"
                    aria-controls="cms-sidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i> Menu
                </button>
                <div>
                    <strong>@if(request()->routeIs('user.dashboard')) My Dashboard @else My Account @endif</strong>
                    <small>Your Free Games account and activities</small>
                </div>
            </div>
            <div class="cms-top-actions">
                <a class="cms-website-link" href="{{ route('games.viewIndex') }}">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View site
                </a>
                <a class="cms-top-user" href="{{ route('user.profile') }}" aria-label="Your profile">
                    <span class="cms-avatar">{{ mb_strtoupper(mb_substr($player?->name ?: $player?->username ?: 'P', 0, 1)) }}</span>
                    <span><strong>{{ $player?->name ?: $player?->username ?: 'Player' }}</strong><small>Player account</small></span>
                </a>
            </div>
        </header>
        <main id="main-content" class="site-main cms-content">
            @yield('workspace-navigation')
            @if(session('success'))<p class="status-message" role="status">{{ session('success') }}</p>@endif
            @if(session('error'))<p class="status-message" role="alert">{{ session('error') }}</p>@endif
            @yield('content')
        </main>
        @php($cmsFooter = \App\Services\SiteCms::section('footer'))
        <footer class="cms-footer">
            <span>Free Games · Player account</span>
            <span aria-hidden="true"> · </span>
            <a href="{{ route('games.viewIndex') }}">Website</a>
            @if($cmsFooter['show_contact'])
                <span aria-hidden="true"> · </span>
                <a href="{{ route('contacts.create') }}">{{ $cmsFooter['contact_label'] }}</a>
            @endif
        </footer>
    </div>
</div>
