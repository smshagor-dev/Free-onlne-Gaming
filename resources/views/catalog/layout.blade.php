<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? ('Games | '.config('app.name')) }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover games, giveaways and deals.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle ?? ('Games | '.config('app.name')) }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Discover games, giveaways and deals.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($ogImage) && filter_var($ogImage, FILTER_VALIDATE_URL) && in_array(parse_url($ogImage, PHP_URL_SCHEME), ['http', 'https'], true))
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @vite('resources/css/catalog.css')
</head>
<body class="catalog-body">
    <a class="skip-link" href="#main-content">Skip to content</a>

    <header class="catalog-header">
        <div class="catalog-shell catalog-header__inner">
            <a href="{{ route('catalog.index') }}" class="catalog-brand" aria-label="{{ config('app.name') }} games home">
                <span class="catalog-brand__mark" aria-hidden="true">G</span>
                <span>
                    <strong>{{ config('app.name') }}</strong>
                    <small>Game Catalog</small>
                </span>
            </a>

            <nav class="catalog-nav" aria-label="Game catalog">
                <a href="{{ route('catalog.index') }}" @class(['is-active' => request()->routeIs('catalog.index')])>Discover</a>
                <a href="{{ route('catalog.free') }}" @class(['is-active' => request()->routeIs('catalog.free')])>Free</a>
                <a href="{{ route('catalog.giveaways') }}" @class(['is-active' => request()->routeIs('catalog.giveaways')])>Giveaways</a>
                <a href="{{ route('catalog.deals') }}" @class(['is-active' => request()->routeIs('catalog.deals')])>Deals</a>
                <a href="{{ route('casino.index') }}">Casino</a>
            </nav>

            <form action="{{ route('catalog.search') }}" method="GET" class="catalog-search catalog-search--header" role="search" data-search-form>
                <label class="sr-only" for="header-game-search">Search games</label>
                <input id="header-game-search" name="q" type="search" value="{{ request('q') }}" placeholder="Search games..." maxlength="120">
                <button type="submit">Search</button>
                <span class="catalog-search__loading" data-loading hidden aria-live="polite">Searching…</span>
            </form>
        </div>
    </header>

    <main id="main-content" class="catalog-main">
        @yield('content')
    </main>

    <footer class="catalog-footer">
        <div class="catalog-shell catalog-footer__inner">
            <p>Games and offer data are provided by RAWG, FreeToGame, GamerPower and CheapShark where applicable.</p>
            <div class="catalog-footer__links">
                <a href="/">Main site</a>
                <a href="{{ route('free.games.index') }}">Legacy free games</a>
                <a href="{{ route('casino.index') }}">Casino</a>
            </div>
        </div>
    </footer>

    <script>
        document.querySelectorAll('[data-search-form]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var loading = form.querySelector('[data-loading]');
                if (loading) {
                    loading.hidden = false;
                }
                form.classList.add('is-loading');
            });
        });
    </script>
</body>
</html>
