<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d2236">
    <title>@yield('title', 'Admin CMS') | Free Games</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite('resources/js/admin-cms.js')
    @stack('head')
    @yield('styles')
</head>
<body class="cms-body">
@php
    $admin = auth('admin')->user();
    $groups = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', '◫'],
        ],
        'Content' => [
            ['Games', 'admin.games.index', 'admin.games.*', '▣'],
            ['Categories', 'admin.games_categories.index', 'admin.games_categories.*', '▦'],
            ['Tags', 'admin.tags.index', 'admin.tags.*', '#'],
            ['Banners', 'admin.banners.index', 'admin.banners.*', '▧'],
            ['Pages', 'admin.pages.index', 'admin.pages.*', '▤'],
        ],
        'Community' => [
            ['All users', 'admin.users.index', 'admin.users.index', '♙'],
            ['Active users', 'admin.users.active', 'admin.users.active', '✓'],
            ['Pending users', 'admin.users.pending', 'admin.users.pending', '◷'],
            ['Banned users', 'admin.users.banned', 'admin.users.banned', '⊘'],
            ['KYC / ban reviews', 'admin.ban_documents.users', 'admin.ban_documents.*', '☷'],
            ['Messages', 'admin.contacts.index', 'admin.contacts.*', '✉'],
        ],
        'Games & rewards' => [
            ['Game points', 'admin.points.index', 'admin.points.*', '✦'],
            ['Player levels', 'admin.levels.index', 'admin.levels.*', '♜'],
            ['Lottery', 'admin.lottaries.index', 'admin.lottaries.*', '◈'],
            ['Lottery tickets', 'admin.lottaries.transactions', 'admin.lottaries.transactions*', '▤'],
        ],
        'Finance' => [
            ['Financial records', 'admin.finance.index', 'admin.finance.*', '¤'],
        ],
        'Management' => [
            ['Countries & currencies', 'admin.countries.index', 'admin.countries.*', '◎'],
            ['Site / SMTP / SEO', 'admin.setting.edit', 'admin.setting.*', '⚙'],
            ['Application logs', 'admin.logs.index', 'admin.logs.*', '≡'],
            ['Scheduler logs', 'admin.scheduler.logs.index', 'admin.scheduler.logs.*', '⌁'],
        ],
        'Account security' => [
            ['Admin profile', 'admin.profile', 'admin.profile', '♙'],
            ['Change password', 'admin.editPassword', 'admin.editPassword', '⚿'],
        ],
    ];
@endphp
<div class="cms-shell">
    <button class="cms-mobile-backdrop" type="button" data-cms-backdrop aria-label="Close navigation"></button>
    <aside class="cms-sidebar" id="cms-sidebar" data-cms-sidebar aria-label="CMS navigation">
        <a href="{{ route('admin.dashboard') }}" class="cms-sidebar-head" aria-label="Free Games CMS dashboard">
            <span class="cms-mark" aria-hidden="true"><i class="fa-solid fa-gamepad"></i></span>
            <span class="cms-brand">Free Games<small>ADMIN COMMAND CENTER</small></span>
        </a>
        <div class="cms-sidebar-scroll">
            <nav aria-label="Admin sections">
                @foreach($groups as $groupTitle => $entries)
                    <section class="cms-nav-group" aria-label="{{ $groupTitle }}">
                        <div class="cms-nav-group-title">{{ $groupTitle }}</div>
                        @foreach($entries as [$label,$routeName,$active,$glyph])
                            @if(Route::has($routeName))
                                <a href="{{ route($routeName) }}"
                                    class="cms-nav-link {{ request()->routeIs($active) ? 'is-active' : '' }}"
                                    @if(request()->routeIs($active)) aria-current="page" @endif>
                                    <span class="cms-nav-icon" aria-hidden="true">{{ $glyph }}</span>
                                    <span>{{ $label }}</span>
                                </a>
                            @endif
                        @endforeach
                    </section>
                @endforeach
            </nav>
        </div>
        <div class="cms-sidebar-foot">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="cms-logout" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Sign out securely</button>
            </form>
        </div>
    </aside>
    <div class="cms-stage">
        <header class="cms-topbar">
            <div class="cms-top-title">
                <button type="button" class="cms-menu-toggle" data-cms-menu-toggle aria-expanded="false" aria-controls="cms-sidebar" aria-label="Toggle sidebar">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i> Menu
                </button>
                <div>
                    <strong>@yield('page_title', 'Control center')</strong>
                    <small>Manage Free Games operations</small>
                </div>
            </div>
            <div class="cms-top-actions">
                <a class="cms-website-link" href="{{ route('games.viewIndex') }}" target="_blank" rel="noopener noreferrer">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View site
                </a>
                <a class="cms-top-user" href="{{ route('admin.profile') }}" aria-label="Admin profile">
                    <span class="cms-avatar">{{ strtoupper(substr($admin?->name ?: 'A', 0, 1)) }}</span>
                    <span><strong>{{ $admin?->name ?: 'Administrator' }}</strong><small>Administrator</small></span>
                </a>
            </div>
        </header>
        <main class="cms-content" id="cms-main">
            @if(session('success'))<div class="cms-flash" role="status">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="cms-flash is-error" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="cms-flash is-error" role="alert">
                    <strong>Please review these fields.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
        <footer class="cms-footer">Free Games CMS · Administrator access only</footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
