@extends('catalog.layout')

@section('content')
<div class="catalog-shell">
    <section class="catalog-hero">
        <div class="catalog-hero__content">
            <x-catalog.badge tone="accent">Multi-provider catalog</x-catalog.badge>
            <h1>Find your next game without digging through four different sites.</h1>
            <p>Explore popular releases, free-to-play games, active giveaways and current deals from one clean catalog.</p>

            <form action="{{ route('catalog.search') }}" method="GET" class="catalog-search catalog-search--hero" role="search" data-search-form>
                <label class="sr-only" for="hero-game-search">Search the game catalog</label>
                <input id="hero-game-search" name="q" type="search" placeholder="Search by game title..." maxlength="120">
                <button type="submit">Search games</button>
                <span class="catalog-search__loading" data-loading hidden aria-live="polite">Searching…</span>
            </form>

            <div class="catalog-hero__quicklinks" aria-label="Quick links">
                <a href="{{ route('catalog.free') }}">Free-to-play</a>
                <a href="{{ route('catalog.giveaways') }}">Giveaways</a>
                <a href="{{ route('catalog.deals') }}">Best deals</a>
            </div>
        </div>
        <div class="catalog-hero__visual" aria-hidden="true">
            <span class="hero-orb hero-orb--one"></span>
            <span class="hero-orb hero-orb--two"></span>
            <div class="hero-controller">🎮</div>
        </div>
    </section>

    <section class="catalog-section">
        <x-catalog.section-header
            title="Trending & discover"
            subtitle="Highly rated picks from the RAWG catalog."
        />

        @if($discover)
            <div class="game-grid">
                @foreach($discover as $game)
                    <x-catalog.game-card :game="$game" />
                @endforeach
            </div>
        @else
            <x-catalog.state
                title="Discover games are temporarily unavailable"
                message="The catalog provider may be unavailable or not configured. Other sections can still work independently."
                type="error"
            />
        @endif
    </section>

    @if($genres || $platforms)
        <section class="catalog-filter-panel" aria-labelledby="browse-filters-title">
            <div>
                <h2 id="browse-filters-title">Browse by genre or platform</h2>
                <p>These filters apply to the discover section without changing the rest of the catalog.</p>
            </div>

            @if($genres)
                <div class="catalog-chip-group">
                    <strong>Genres</strong>
                    <div>
                        @foreach($genres as $genre)
                            <a @class(['catalog-chip', 'is-active' => $activeGenre === ($genre['slug'] ?? $genre['id'])])
                               href="{{ route('catalog.index', ['genre' => $genre['slug'] ?? $genre['id']]) }}">
                                {{ $genre['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($platforms)
                <div class="catalog-chip-group">
                    <strong>Platforms</strong>
                    <div>
                        @foreach($platforms as $platform)
                            <a @class(['catalog-chip', 'is-active' => $activePlatform === ($platform['slug'] ?? $platform['id'])])
                               href="{{ route('catalog.index', ['platform' => $platform['slug'] ?? $platform['id']]) }}">
                                {{ $platform['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    <section class="catalog-section">
        <x-catalog.section-header
            title="Free-to-play"
            subtitle="Games you can start without an upfront purchase."
            :href="route('catalog.free')"
        />
        @if($free)
            <div class="game-grid game-grid--compact">
                @foreach($free as $game)
                    <x-catalog.game-card :game="$game" />
                @endforeach
            </div>
        @else
            <x-catalog.state title="No free-to-play games available right now" message="Try again later; cached data will be used automatically when available." />
        @endif
    </section>

    <section class="catalog-section">
        <x-catalog.section-header
            title="Active giveaways"
            subtitle="Limited-time offers sourced from GamerPower."
            :href="route('catalog.giveaways')"
        />
        @if($giveaways)
            <div class="giveaway-grid">
                @foreach($giveaways as $game)
                    <x-catalog.giveaway-card :game="$game" />
                @endforeach
            </div>
        @else
            <x-catalog.state title="No giveaways available right now" message="This section fails safely when the giveaway provider is unavailable." />
        @endif
    </section>

    <section class="catalog-section">
        <x-catalog.section-header
            title="Game deals"
            subtitle="Recent discounts from CheapShark."
            :href="route('catalog.deals')"
        />
        @if($deals)
            <div class="deal-list">
                @foreach($deals as $game)
                    <x-catalog.price-deal-card :game="$game" />
                @endforeach
            </div>
        @else
            <x-catalog.state title="Deals are temporarily unavailable" message="Other catalog sections remain available if this provider is down." />
        @endif
    </section>
</div>
@endsection
