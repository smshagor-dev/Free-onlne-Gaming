@extends('layouts.app')

@section('content')
@php
    $localGames = collect($categories ?? [])->pluck('games')->flatten()->filter()->values();
    $primaryGames = collect($playableGames ?? [])->values();
    $newGames = $primaryGames->take(6);
    $trendingGames = $primaryGames->slice(6, 6)->values();

    if ($trendingGames->count() < 6 && $localGames->isNotEmpty()) {
        $trendingGames = $trendingGames->concat($localGames->take(6 - $trendingGames->count()))->values();
    }

    if ($newGames->count() < 6 && $localGames->isNotEmpty()) {
        $newGames = $newGames->concat($localGames->slice(6, 6 - $newGames->count()))->values();
    }

    $heroGame = $primaryGames->first() ?: $localGames->first();
    $heroImage = is_array($heroGame)
        ? ($heroGame['image'] ?? asset('default.png'))
        : ($heroGame?->image ? asset($heroGame->image) : asset('default.png'));

    $categoryLinks = collect($playableCategories ?? []);
    $categoryTiles = [
        ['name' => 'Action', 'icon' => 'fa-crosshairs', 'class' => 'cat-action'],
        ['name' => 'Adventure', 'icon' => 'fa-mountain-sun', 'class' => 'cat-adventure'],
        ['name' => 'Racing', 'icon' => 'fa-car-side', 'class' => 'cat-racing'],
        ['name' => 'Shooting', 'icon' => 'fa-gun', 'class' => 'cat-shooting'],
        ['name' => 'Sports', 'icon' => 'fa-futbol', 'class' => 'cat-sports'],
        ['name' => 'Puzzle', 'icon' => 'fa-puzzle-piece', 'class' => 'cat-puzzle'],
        ['name' => 'Multiplayer', 'icon' => 'fa-users', 'class' => 'cat-multiplayer'],
        ['name' => 'Simulation', 'icon' => 'fa-gamepad', 'class' => 'cat-simulation'],
        ['name' => 'More', 'icon' => 'fa-ellipsis', 'class' => 'cat-more'],
    ];

    $findCategoryUrl = function (string $name) use ($categoryLinks) {
        $aliases = ['Shooting' => 'Shooter', 'Simulation' => 'Sim', 'Sports' => 'Sports'];
        $match = $categoryLinks->first(fn ($category) => strcasecmp($category, $aliases[$name] ?? $name) === 0);
        return route('playable.index', ['category' => $match ?? $name]);
    };
@endphp

<style>
    :root {
        --fg-page: #06111c;
        --fg-header: #040d16;
        --fg-panel: #102131;
        --fg-panel-2: #14293c;
        --fg-blue: #10a8ff;
        --fg-blue-hover: #0589db;
        --fg-text: #ffffff;
        --fg-muted: #9eb0c3;
        --fg-border: rgba(255, 255, 255, 0.08);
        --fg-shadow: 0 22px 45px rgba(0, 0, 0, 0.32);
    }

    html,
    body {
        margin: 0;
        height: auto !important;
        min-height: 100%;
        overflow: auto !important;
        background: var(--fg-page) !important;
        color: var(--fg-text);
        font-family: Inter, Manrope, "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    }

    body > header,
    #sidebar,
    #mobileOverlay,
    body > .flex.h-screen.overflow-hidden > aside {
        display: none !important;
    }

    body > .flex.h-screen.overflow-hidden {
        display: block !important;
        height: auto !important;
        min-height: 100vh;
        overflow: visible !important;
    }

    body > .flex.h-screen.overflow-hidden > main {
        width: 100% !important;
        height: auto !important;
        min-height: 100vh;
        padding: 0 !important;
        overflow: visible !important;
        background:
            radial-gradient(circle at 75% 12%, rgba(16, 168, 255, 0.13), transparent 30rem),
            linear-gradient(180deg, #06111c 0%, #071521 58%, #050d16 100%);
    }

    .fg-page,
    .fg-page * {
        box-sizing: border-box;
        letter-spacing: 0;
    }

    .fg-page {
        width: 100%;
        overflow-x: hidden;
    }

    .fg-page a {
        color: inherit;
        text-decoration: none;
    }

    .fg-shell {
        width: min(100%, 1500px);
        margin: 0 auto;
        padding: 0 38px;
    }

    .fg-topbar {
        position: sticky;
        top: 0;
        z-index: 40;
        min-height: 70px;
        background: rgba(4, 13, 22, 0.96);
        border-bottom: 1px solid var(--fg-border);
        backdrop-filter: blur(18px);
    }

    .fg-nav {
        min-height: 70px;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 28px;
        align-items: center;
    }

    .fg-brand {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 27px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .fg-brand-icon {
        width: 45px;
        height: 36px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        color: #03101b;
        background: linear-gradient(135deg, #25b8ff, #0879ec);
        box-shadow: 0 0 24px rgba(16, 168, 255, 0.36);
    }

    .fg-brand-word {
        display: inline-flex;
    }

    .fg-brand-word span:first-child {
        color: #fff;
    }

    .fg-brand-word span:last-child {
        color: var(--fg-blue);
    }

    .fg-nav-links {
        display: flex;
        align-items: stretch;
        justify-content: center;
        gap: 26px;
        min-height: 70px;
    }

    .fg-nav-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #c9d4e2;
        font-size: 14px;
        font-weight: 700;
        transition: color 0.2s ease;
        white-space: nowrap;
    }

    .fg-nav-link:hover,
    .fg-nav-link.is-active {
        color: var(--fg-blue);
    }

    .fg-nav-link.is-active::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        border-radius: 99px 99px 0 0;
        background: var(--fg-blue);
        box-shadow: 0 0 14px rgba(16, 168, 255, 0.85);
    }

    .fg-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 14px;
    }

    .fg-search {
        position: relative;
        width: 240px;
    }

    .fg-search label {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }

    .fg-search input {
        width: 100%;
        height: 39px;
        padding: 0 44px 0 20px;
        border: 1px solid var(--fg-border);
        border-radius: 999px;
        outline: none;
        background: #162737;
        color: #fff;
        font-size: 14px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .fg-search input::placeholder {
        color: #8ba1b6;
    }

    .fg-search input:focus {
        border-color: rgba(16, 168, 255, 0.65);
        box-shadow: 0 0 0 3px rgba(16, 168, 255, 0.18);
    }

    .fg-search button {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        display: grid;
        place-items: center;
        border: 0;
        color: #f2f7ff;
        cursor: pointer;
        background: transparent;
    }

    .fg-auth {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 39px;
        min-width: 84px;
        padding: 0 18px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid transparent;
        transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
    }

    .fg-auth:hover {
        transform: translateY(-1px);
    }

    .fg-auth-primary {
        background: linear-gradient(135deg, #18adff, #0788ef);
        color: #fff;
        box-shadow: 0 10px 22px rgba(16, 168, 255, 0.26);
    }

    .fg-auth-ghost {
        border-color: rgba(16, 168, 255, 0.55);
        color: #d8e9ff;
        background: rgba(8, 24, 39, 0.72);
    }

    .fg-hero {
        position: relative;
        min-height: 414px;
        overflow: hidden;
        border-bottom: 1px solid var(--fg-border);
        background:
            linear-gradient(90deg, rgba(6, 17, 28, 0.98) 0%, rgba(6, 17, 28, 0.88) 31%, rgba(6, 17, 28, 0.18) 70%, rgba(6, 17, 28, 0.34) 100%),
            linear-gradient(180deg, rgba(6, 17, 28, 0.1), var(--fg-page)),
            url("{{ $heroImage }}") center right / cover no-repeat;
    }

    .fg-hero::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 130px;
        background: linear-gradient(180deg, transparent, #06111c 88%);
        pointer-events: none;
    }

    .fg-hero-layout {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(450px, 0.78fr) minmax(400px, 1fr);
        min-height: 414px;
        align-items: center;
        gap: 28px;
    }

    .fg-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding: 8px 15px;
        border-radius: 10px;
        border: 1px solid rgba(27, 227, 117, 0.42);
        background: rgba(16, 150, 82, 0.24);
        color: #d8ffe9;
        box-shadow: 0 0 24px rgba(16, 222, 118, 0.15);
        font-size: 14px;
        font-weight: 900;
    }

    .fg-badge i {
        color: #72ff8f;
    }

    .fg-hero h1 {
        max-width: 620px;
        margin: 0;
        color: #fff;
        font-size: clamp(42px, 5vw, 64px);
        line-height: 1.02;
        font-weight: 950;
    }

    .fg-hero h1 .fg-blue-text {
        color: var(--fg-blue);
        text-shadow: 0 0 28px rgba(16, 168, 255, 0.42);
    }

    .fg-mobile-only {
        display: none;
    }

    .fg-desktop-only {
        display: block;
    }

    .fg-hero p {
        max-width: 530px;
        margin: 14px 0 24px;
        color: #c6d6e5;
        font-size: 19px;
        line-height: 1.34;
    }

    .fg-hero-actions {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .fg-hero-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 11px;
        min-width: 197px;
        height: 58px;
        padding: 0 28px;
        border-radius: 11px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        font-size: 18px;
        font-weight: 900;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .fg-hero-btn:hover {
        transform: translateY(-2px);
    }

    .fg-hero-btn-primary {
        background: linear-gradient(135deg, #1ab2ff, #068bf0);
        box-shadow: 0 18px 34px rgba(16, 168, 255, 0.33);
    }

    .fg-hero-btn-secondary {
        background: rgba(14, 30, 45, 0.78);
        border-color: rgba(255, 255, 255, 0.22);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
    }

    .fg-hero-art {
        position: relative;
        height: 360px;
        align-self: end;
    }

    .fg-art-card {
        position: absolute;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 18px;
        background: #152538;
        box-shadow: var(--fg-shadow);
    }

    .fg-art-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .fg-art-main {
        right: 14%;
        bottom: 18px;
        width: 43%;
        height: 78%;
        transform: rotate(-3deg);
    }

    .fg-art-side {
        right: 0;
        top: 24px;
        width: 35%;
        height: 60%;
        transform: rotate(5deg);
    }

    .fg-art-low {
        right: 31%;
        bottom: 0;
        width: 34%;
        height: 42%;
        transform: rotate(4deg);
    }

    .fg-hero-script {
        position: absolute;
        right: 10px;
        bottom: 34px;
        max-width: 190px;
        color: #fff;
        font-size: 29px;
        font-weight: 950;
        line-height: 0.98;
        text-align: right;
        text-transform: uppercase;
        text-shadow: 0 3px 16px #000;
        transform: rotate(-9deg);
    }

    .fg-hero-script::after {
        content: "";
        display: block;
        width: 94px;
        height: 7px;
        margin: 12px 0 0 auto;
        border-radius: 99px;
        background: var(--fg-blue);
        box-shadow: 0 0 16px rgba(16, 168, 255, 0.75);
    }

    .fg-dots {
        position: absolute;
        z-index: 3;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%);
        display: flex;
        gap: 12px;
    }

    .fg-dot {
        width: 12px;
        height: 12px;
        border: 0;
        border-radius: 999px;
        background: rgba(142, 164, 187, 0.52);
    }

    .fg-dot.is-active {
        background: var(--fg-blue);
        box-shadow: 0 0 16px rgba(16, 168, 255, 0.8);
    }

    .fg-main {
        padding: 17px 0 42px;
    }

    .fg-categories {
        display: grid;
        grid-template-columns: repeat(9, minmax(116px, 1fr));
        gap: 15px;
        margin-bottom: 24px;
    }

    .fg-category {
        min-height: 83px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 8px;
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.11);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .fg-category:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.24), 0 0 24px rgba(16, 168, 255, 0.14);
    }

    .fg-category i {
        font-size: 25px;
    }

    .fg-category span {
        font-size: 14px;
        font-weight: 900;
    }

    .cat-action { background: linear-gradient(135deg, #f44343, #c8232f); }
    .cat-adventure { background: linear-gradient(135deg, #10a852, #057842); }
    .cat-racing { background: linear-gradient(135deg, #106bff, #0752c8); }
    .cat-shooting { background: linear-gradient(135deg, #692dff, #4811b7); }
    .cat-sports { background: linear-gradient(135deg, #f18809, #d34b00); }
    .cat-puzzle { background: linear-gradient(135deg, #c9940a, #9c6f02); }
    .cat-multiplayer { background: linear-gradient(135deg, #d8378a, #a91c68); }
    .cat-simulation { background: linear-gradient(135deg, #0aa9bd, #037483); }
    .cat-more { background: linear-gradient(135deg, #1d3348, #101c2a); }

    .fg-section {
        margin-top: 22px;
    }

    .fg-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 14px;
    }

    .fg-section-title {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        color: #fff;
        font-size: 26px;
        font-weight: 950;
    }

    .fg-section-title i {
        color: var(--fg-blue);
        filter: drop-shadow(0 0 10px rgba(16, 168, 255, 0.6));
    }

    .fg-section-title .star {
        color: #ffbc2e;
        filter: drop-shadow(0 0 10px rgba(255, 188, 46, 0.42));
    }

    .fg-view-all {
        color: var(--fg-blue);
        font-size: 15px;
        font-weight: 800;
        white-space: nowrap;
    }

    .fg-view-all:hover {
        color: #66caff;
    }

    .fg-page [hidden] { display: none !important; }
    .fg-nav > * { min-width: 0; }
    .fg-more-actions { display: flex; justify-content: center; margin-top: 24px; }
    .fg-load-status { color: var(--fg-muted); text-align: center; }

    .fg-games-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 15px;
    }

    .fg-empty {
        padding: 26px;
        border-radius: 12px;
        border: 1px solid var(--fg-border);
        background: var(--fg-panel);
        color: var(--fg-muted);
    }

    @media (max-width: 1320px) {
        .fg-nav {
            grid-template-columns: auto 1fr;
            gap: 14px;
        }

        .fg-actions {
            grid-column: 1 / -1;
            padding-bottom: 13px;
            justify-content: stretch;
        }

        .fg-search {
            flex: 1;
            width: auto;
        }

        .fg-categories {
            grid-template-columns: repeat(5, minmax(128px, 1fr));
        }

        .fg-games-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 1024px) {
        .fg-shell {
            padding: 0 22px;
        }

        .fg-nav-links {
            gap: 15px;
            justify-content: flex-end;
        }

        .fg-hero-layout {
            grid-template-columns: 1fr;
            min-height: auto;
            padding: 44px 0 60px;
        }

        .fg-hero-art {
            display: none;
        }

        .fg-hero {
            min-height: auto;
        }

        .fg-categories {
            display: flex;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }

        .fg-category {
            min-width: 150px;
        }

        .fg-games-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .fg-shell {
            padding: 0 16px;
        }

        .fg-topbar {
            position: relative;
        }

        .fg-nav {
            min-height: auto;
            padding: 13px 0;
            grid-template-columns: 1fr;
        }

        .fg-brand {
            font-size: 24px;
        }

        .fg-nav-links {
            min-width: 0;
            order: 3;
            min-height: auto;
            overflow-x: auto;
            justify-content: flex-start;
            padding-bottom: 6px;
        }

        .fg-nav-link {
            min-height: 35px;
        }

        .fg-nav-link.is-active::after {
            bottom: -6px;
        }

        .fg-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .fg-search {
            grid-column: 1 / -1;
        }

        .fg-hero-layout {
            padding: 34px 0 58px;
        }

        .fg-hero h1 {
            font-size: clamp(34px, 10.5vw, 42px);
        }

        .fg-hero h1 .fg-blue-text {
            display: block;
        }

        .fg-mobile-only {
            display: block;
        }

        .fg-desktop-only {
            display: none;
        }

        .fg-hero p {
            font-size: 16px;
        }

        .fg-hero-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }

        .fg-hero-btn {
            width: 100%;
            min-width: 0;
        }

        .fg-categories {
            margin-bottom: 18px;
        }

        .fg-section-title {
            font-size: 22px;
        }

        .fg-games-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
    }

    @media (max-width: 430px) {
        .fg-games-grid {
            grid-template-columns: 1fr;
        }

        .fg-actions {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="fg-page">
    <header class="fg-topbar">
        <div class="fg-shell">
            <nav class="fg-nav" aria-label="Primary navigation">
                <a class="fg-brand" href="{{ route('games.viewIndex') }}" aria-label="FreeGames home">
                    <span class="fg-brand-icon" aria-hidden="true"><i class="fa-solid fa-gamepad"></i></span>
                    <span class="fg-brand-word"><span>Free</span><span>Games</span></span>
                </a>

                <div class="fg-nav-links">
                    <a class="fg-nav-link is-active" href="{{ route('games.viewIndex') }}"><i class="fa-solid fa-house" aria-hidden="true"></i>Home</a>
                    <a class="fg-nav-link" href="{{ route('playable.index') }}">Games</a>
                    <a class="fg-nav-link" href="{{ route('playable.index') }}">Categories</a><a class="fg-nav-link" href="{{ route('user.lottaries.view') }}">Lottery</a>
                    <a class="fg-nav-link" href="{{ route('playable.index') }}">New</a>
                    <a class="fg-nav-link" href="{{ route('playable.index') }}"><i class="fa-solid fa-fire-flame-curved" aria-hidden="true"></i>Featured</a>
                    <a class="fg-nav-link" href="{{ route('contacts.create') }}">About</a>
                </div>

                <div class="fg-actions">
                    <form class="fg-search" method="GET" action="{{ route('playable.index') }}">
                        <label for="fg-search-input">Search games</label>
                        <input id="fg-search-input" name="search" type="search" placeholder="Search games..." value="{{ $search ?? '' }}">
                        <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>

                    @guest
                        <a class="fg-auth fg-auth-primary" href="{{ route('login.form') }}">Login</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('register.form') }}">Sign Up</a>
                    @else
                        <a class="fg-auth fg-auth-primary" href="{{ route('user.dashboard') }}">Dashboard</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('user.games.viewFavorites') }}">Favorites</a>
                    @endguest
                </div>
            </nav>
        </div>
    </header>

    <section class="fg-hero" aria-labelledby="fg-hero-title">
        <div class="fg-shell">
            <div class="fg-hero-layout">
                <div>
                    <div class="fg-badge"><i class="fa-solid fa-gamepad" aria-hidden="true"></i>100% FREE TO PLAY</div>
                    <h1 id="fg-hero-title">Play <span class="fg-blue-text">Free Games</span><br>Online <br class="fg-mobile-only">Anytime</h1>
                    <p>Discover free browser games. No download required.<br class="fg-desktop-only">Just pure fun!</p>
                    <div class="fg-hero-actions">
                        <a class="fg-hero-btn fg-hero-btn-primary" href="{{ route('playable.index') }}"><i class="fa-solid fa-play" aria-hidden="true"></i>Play Now</a>
                        <a class="fg-hero-btn fg-hero-btn-secondary" href="{{ route('playable.index') }}"><i class="fa-solid fa-grip" aria-hidden="true"></i>Browse Games</a>
                    </div>
                </div>

                <div class="fg-hero-art" aria-hidden="true">
                    @foreach($primaryGames->take(3) as $index => $artGame)
                        <div class="fg-art-card {{ $index === 0 ? 'fg-art-main' : ($index === 1 ? 'fg-art-side' : 'fg-art-low') }}">
                            <img src="{{ $artGame['image'] ?: asset('default.png') }}" alt="">
                        </div>
                    @endforeach
                    <div class="fg-hero-script">Games<br>For Everyone</div>
                </div>
            </div>
        </div>
    </section>

    <div class="fg-main">
        <div class="fg-shell">
            <span class="sr-only">Explore Games</span>
            <section class="fg-categories" aria-label="Game categories">
                @foreach($categoryTiles as $tile)
                    <a class="fg-category {{ $tile['class'] }}" href="{{ $tile['name'] === 'More' ? route('playable.index') : $findCategoryUrl($tile['name']) }}">
                        <i class="fa-solid {{ $tile['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $tile['name'] }}</span>
                    </a>
                @endforeach
            </section>

            <section class="fg-section" aria-labelledby="fg-trending-title">
                <div class="fg-section-head">
                    <h2 class="fg-section-title" id="fg-trending-title"><i class="fa-solid fa-star star" aria-hidden="true"></i>Featured Games</h2>
                    <a class="fg-view-all" href="{{ route('playable.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                @if($trendingGames->isNotEmpty())
                    <div class="fg-games-grid">
                        @foreach($trendingGames as $game)
                            @include('components.home-game-card', ['game' => $game, 'fallbackTag' => 'Featured'])
                        @endforeach
                    </div>
                @else
                    <div class="fg-empty">Games are temporarily unavailable. Please try again later.</div>
                @endif
            </section>

            <section class="fg-section" aria-labelledby="fg-new-title">
                <div class="fg-section-head">
                    <h2 class="fg-section-title" id="fg-new-title"><i class="fa-solid fa-gamepad" aria-hidden="true"></i>New Games</h2>
                    <a class="fg-view-all" href="{{ route('playable.index') }}">View All <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>

                @if($newGames->isNotEmpty())
                    <div class="fg-games-grid">
                        @foreach($newGames as $game)
                            @include('components.home-game-card', ['game' => $game, 'fallbackTag' => 'New'])
                        @endforeach
                    </div>
                @else
                    <div class="fg-empty">No new games are available right now. Please try again later.</div>
                @endif
            </section>
            @if($primaryGames->count() > 12)
            <section class="fg-section" aria-labelledby="fg-explore-title">
                <div class="fg-section-head"><h2 class="fg-section-title" id="fg-explore-title">Explore More Games</h2></div>
                <div class="fg-games-grid" id="fg-more-games">
                    @foreach($primaryGames->slice(12) as $game)
                        @include('components.home-game-card', ['game' => $game, 'fallbackTag' => 'Free'])
                    @endforeach
                </div>
                @if(($playableTotal ?? 0) > 30)
                <div class="fg-more-actions"><button id="fg-load-more" type="button" class="fg-hero-btn fg-hero-btn-primary">Load More Games</button></div>
                <p id="fg-load-status" class="fg-load-status" role="status" aria-live="polite"></p>
                @endif
            </section>
            @endif
        </div>
    </div>
</div>
<script>
(() => {
    const button = document.getElementById('fg-load-more');
    if (!button) return;
    let page = 2;
    const status = document.getElementById('fg-load-status');
    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Loading...';
        status.textContent = '';
        try {
            const url = new URL(@json(route('playable.load')), window.location.origin);
            url.searchParams.set('page', page);
            url.searchParams.set('layout', 'home');
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Unable to load games');
            const data = await response.json();
            document.getElementById('fg-more-games').insertAdjacentHTML('beforeend', data.html);
            page++;
            button.hidden = !data.has_more;
            if (!data.has_more) status.textContent = 'You have reached the end of the games list.';
        } catch (error) {
            status.textContent = 'Games could not be loaded. Please try again.';
        } finally {
            button.disabled = false;
            button.textContent = 'Load More Games';
        }
    });
})();
</script>
@endsection
