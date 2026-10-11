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



<div class="fg-page">


    <section class="fg-hero" aria-labelledby="fg-hero-title">
        <div class="fg-shell">
            <div class="fg-hero-layout">
                <div>
                    <div class="fg-badge"><i class="fa-solid fa-gamepad" aria-hidden="true"></i>FREE BROWSER GAMES</div>
                    <h1 id="fg-hero-title">Find your next<br><span class="fg-blue-text">favorite game.</span></h1>
                    <p>A little break. A new challenge. Play free games right in your browser, whenever you like.</p>
                    <form class="fg-search" method="GET" action="{{ route('playable.index') }}">
                        <label for="fg-search-input">Search games</label>
                        <input id="fg-search-input" name="search" type="search" placeholder="Search games..." value="{{ $search ?? '' }}">
                        <button type="submit" aria-label="Search"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg></button>
                    </form>

                    <div class="fg-hero-actions" style="margin-top:22px">
                        <a class="fg-hero-btn fg-hero-btn-primary" href="{{ route('playable.index') }}">Explore games <span aria-hidden="true">→</span></a>
                        <span style="font-size:13px;color:#9fb1c7">No downloads needed</span>
                    </div>
                </div>

                <div class="fg-hero-art" aria-hidden="true">
                    <svg class="fg-controller" viewBox="0 0 400 280" fill="none" xmlns="http://www.w3.org/2000/svg"><ellipse cx="200" cy="248" rx="125" ry="12" fill="#081322"/><circle cx="200" cy="125" r="115" stroke="#37618c" stroke-opacity=".3"/><circle cx="200" cy="125" r="135" stroke="#37618c" stroke-opacity=".15"/><path d="M109 69C72 71 58 105 44 182C35 231 67 250 101 224L141 191H259L299 224C333 250 365 231 356 182C342 105 328 71 291 69L109 69Z" fill="#213955" stroke="#507498" stroke-width="3"/><path d="M113 94V147M87 120H140" stroke="#90b9df" stroke-width="14" stroke-linecap="round"/><circle cx="291" cy="105" r="12" fill="#74c5fc"/><circle cx="316" cy="130" r="12" fill="#7086ed"/><circle cx="266" cy="130" r="12" fill="#6dc9bb"/><circle cx="291" cy="155" r="12" fill="#e8b476"/><circle cx="161" cy="158" r="22" fill="#111f31" stroke="#456a91" stroke-width="3"/><circle cx="235" cy="158" r="22" fill="#111f31" stroke="#456a91" stroke-width="3"/><rect x="185" y="107" width="28" height="7" rx="3.5" fill="#527696"/></svg>
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
                    <div class="fg-empty">The game collection is temporarily unavailable. Please check back soon.</div>
                @endif
            </section>

            @if($newGames->isNotEmpty())
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
            @endif
            @if($primaryGames->count() > 12)
            <section class="fg-section" aria-labelledby="fg-explore-title">
                <div class="fg-section-head"><h2 class="fg-section-title" id="fg-explore-title">Explore More Games</h2></div>
                <div class="fg-games-grid" id="fg-more-games">
                    @foreach($primaryGames->slice(12) as $game)
                        @include('components.home-game-card', ['game' => $game, 'fallbackTag' => 'Free'])
                    @endforeach
                </div>
                @if(($playableTotal ?? 0) > 30)
                <div id="fg-scroll-sentinel" aria-hidden="true" style="height:1px"></div>
                <p id="fg-load-status" class="fg-load-status" role="status" aria-live="polite"></p>
                @endif
            </section>
            @endif
        </div>
    </div>
</div>
<script>
(() => {
    const sentinel = document.getElementById('fg-scroll-sentinel');
    if (!sentinel) return;
    const grid = document.getElementById('fg-more-games');
    const status = document.getElementById('fg-load-status');
    let page = 2;
    let loading = false;
    let hasMore = true;
    let retryAfter = 0;
    const nearEnd = () => sentinel.getBoundingClientRect().top <= window.innerHeight + 300;
    const loadNext = async () => {
        if (loading || !hasMore || Date.now() < retryAfter || !nearEnd()) return;
        loading = true;
        grid.setAttribute('aria-busy', 'true');
        status.textContent = 'Loading more games…';
        try {
            const url = new URL(@json(route('playable.load')), window.location.origin);
            url.searchParams.set('page', page);
            url.searchParams.set('layout', 'home');
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Unable to load games');
            const data = await response.json();
            if (typeof data.html !== 'string' || typeof data.has_more !== 'boolean') throw new Error('Invalid response');
            grid.insertAdjacentHTML('beforeend', data.html);
            page++;
            hasMore = data.has_more && data.html.trim() !== '';
            status.textContent = hasMore ? '' : 'You have reached the end of the games list.';
            if (!hasMore) observer?.disconnect();
        } catch (error) {
            retryAfter = Date.now() + 3000;
            status.textContent = 'Games could not be loaded. Scroll again to retry.';
        } finally {
            loading = false;
            grid.setAttribute('aria-busy', 'false');
            if (hasMore && Date.now() >= retryAfter) requestAnimationFrame(loadNext);
        }
    };
    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver(entries => {
            if (entries.some(entry => entry.isIntersecting)) loadNext();
        }, {rootMargin: '300px 0px'})
        : null;
    observer?.observe(sentinel);
    window.addEventListener('scroll', loadNext, {passive: true});
    window.addEventListener('online', () => { retryAfter = 0; loadNext(); });
    loadNext();
})();
</script>
@endsection
