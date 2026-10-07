<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $isPrivateCatalogPage = request()->is('user/catalog*');
        $isDemoUser = auth()->check() && auth()->user()->registration_type === 'demo';
        $robots = $robots ?? (($isPrivateCatalogPage || $isDemoUser) ? 'noindex, nofollow' : 'index, follow');
        $title = $pageTitle ?? ('Games | '.config('app.name'));
        $description = $metaDescription ?? 'Discover games, giveaways and deals.';
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="#0b141d">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($ogImage) && filter_var($ogImage, FILTER_VALIDATE_URL) && in_array(parse_url($ogImage, PHP_URL_SCHEME), ['http','https'], true))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if(!empty($structuredData))
        <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
    @vite(['resources/css/catalog.css','resources/css/personalization.css'])
</head>
<body class="catalog-body">
<a class="skip-link" href="#main-content">Skip to content</a>
@if($isDemoUser)
    <div class="demo-banner" role="status">Demo mode: financial actions are disabled. <a href="{{ route('register.form') }}">Create a real account</a></div>
@endif
<header class="catalog-header">
    <div class="catalog-shell catalog-header__inner">
        <a href="{{ route('catalog.index') }}" class="catalog-brand"><span class="catalog-brand__mark">G</span><span><strong>{{ config('app.name') }}</strong><small>Game Catalog</small></span></a>
        <nav class="catalog-nav" aria-label="Game catalog">
            <a href="{{ route('catalog.index') }}">Discover</a>
            <a href="{{ route('catalog.free') }}">Free</a>
            <a href="{{ route('catalog.giveaways') }}">Giveaways</a>
            <a href="{{ route('catalog.deals') }}">Deals</a>
            @auth
                <a href="{{ route('catalog.user.library') }}">My Games</a>
                <a href="{{ route('catalog.user.settings') }}">Preferences</a>
            @endauth
            <a href="{{ route('casino.index') }}">Casino</a>
        </nav>
        <form action="{{ route('catalog.search') }}" method="GET" class="catalog-search catalog-search--header" role="search">
            <label class="sr-only" for="header-game-search">Search games</label>
            <input id="header-game-search" name="q" type="search" value="{{ request('q') }}" placeholder="Search games..." maxlength="120">
            <button type="submit">Search</button>
        </form>
        @guest
            <form method="POST" action="{{ route('demo.login') }}">@csrf<button type="submit" class="demo-button">Try Demo</button></form>
        @endguest
    </div>
</header>
<main id="main-content" class="catalog-main">@yield('content')</main>
<footer class="catalog-footer">
    <div class="catalog-shell catalog-footer__inner">
        <p>Games and offer data are provided by RAWG, FreeToGame, GamerPower and CheapShark where applicable.</p>
        <div class="catalog-footer__links"><a href="/">Main site</a><a href="{{ route('free.games.index') }}">Legacy free games</a><a href="{{ route('casino.index') }}">Casino</a></div>
    </div>
</footer>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
</script>
</body>
</html>
