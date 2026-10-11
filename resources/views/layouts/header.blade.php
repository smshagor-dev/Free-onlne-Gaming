@php
    $availableCategories = collect(\Illuminate\Support\Facades\Cache::get('gamemonetize.embeddable.all', []))->pluck('category')->filter()->unique()->sort()->values();
    $menuCategories = ['Action' => '✦', 'Adventure' => '◇', 'Racing' => '➜', 'Shooting' => '◎', 'Sports' => '◉', 'Puzzle' => '▦', 'Multiplayer' => '♧', 'Simulation' => '◈', 'Arcade' => '▣', 'Strategy' => '♜'];
    $categoryDestination = function ($category) use ($availableCategories) {
        $aliases = ['Shooting' => 'Shooter', 'Simulation' => 'Sim'];
        $actual = $availableCategories->first(fn ($name) => strcasecmp($name, $category) === 0 || strcasecmp($name, $aliases[$category] ?? $category) === 0);
        return route('playable.index', ['category' => $actual ?? $category]);
    };
@endphp
    <header class="fg-topbar">
        <div class="fg-shell">
            <nav class="fg-nav" aria-label="Primary navigation">
                <a class="fg-brand" href="{{ route('games.viewIndex') }}" aria-label="FreeGames home">
                    <span class="fg-brand-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.7"><path d="M7 6h10c3 0 4 3 5 9s-2 6-5 3l-2-2H9l-2 2c-3 3-6 2-5-3S4 6 7 6Z"/><path d="M7 9v5M4.5 11.5h5"/><circle cx="16" cy="10" r=".8" fill="white"/><circle cx="18" cy="13" r=".8" fill="white"/></svg></span>
                    <span class="fg-brand-word"><span>Free</span><span>Games</span></span>
                </a>

                <button class="fg-menu-toggle" type="button" aria-expanded="false" aria-controls="fg-menu">Menu</button>
                <div class="fg-nav-links" id="fg-menu">
                    <a class="fg-nav-link {{ request()->routeIs('games.viewIndex') ? 'is-active' : '' }}" @if(request()->routeIs('games.viewIndex')) aria-current="page" @endif href="{{ route('games.viewIndex') }}">Home</a>
                    <a class="fg-nav-link {{ request()->routeIs('playable.*', 'catalog.*') ? 'is-active' : '' }}" href="{{ route('playable.index') }}">Games</a>
                    <details class="fg-category-menu">
                        <summary class="fg-nav-link"><svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true"><rect x="2" y="2" width="6" height="6" rx="1"/><rect x="12" y="2" width="6" height="6" rx="1"/><rect x="2" y="12" width="6" height="6" rx="1"/><rect x="12" y="12" width="6" height="6" rx="1"/></svg> Categories <span class="fg-chevron" aria-hidden="true">⌄</span></summary>
                        <div class="fg-category-dropdown">
                            <div class="fg-dropdown-heading"><strong>Explore categories</strong><a href="{{ route('playable.index') }}">All games →</a></div>
                            <div class="fg-category-options">
                                @foreach($menuCategories as $category => $icon)
                                    <a href="{{ $categoryDestination($category) }}"><span aria-hidden="true">{{ $icon }}</span>{{ $category }}</a>
                                @endforeach
                                @foreach($availableCategories->reject(fn ($category) => array_key_exists($category, $menuCategories) || preg_match('/casino|slots?|roulette|blackjack|baccarat|poker|gambling/i', $category)) as $category)
                                    @if(!in_array($category, ['Shooter', 'Sim']))<a href="{{ $categoryDestination($category) }}"><span aria-hidden="true">◇</span>{{ $category }}</a>@endif
                                @endforeach
                            </div>
                        </div>
                    </details>
                    <a class="fg-nav-link {{ request()->routeIs('user.lottaries.*') ? 'is-active' : '' }}" href="{{ route('user.lottaries.view') }}">Lottery</a>
                    <a class="fg-nav-link {{ request()->routeIs('contacts.create') ? 'is-active' : '' }}" href="{{ route('contacts.create') }}">Contact</a>
                </div>

                <div class="fg-actions">
                    <form class="fg-nav-search" action="{{ route('playable.index') }}" method="GET" role="search">
                        <label class="sr-only" for="navigation-search">Search games</label>
                        <input id="navigation-search" name="search" type="search" placeholder="Find a game…" value="{{ request('search') }}">
                        <button type="submit" aria-label="Search games"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg></button>
                    </form>
                    @guest
                        <a class="fg-auth fg-auth-primary" href="{{ route('login.form') }}">Login</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('register.form') }}">Sign Up</a>
                    @else
                        <a class="fg-auth fg-auth-primary" href="{{ route('user.dashboard') }}">Account</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('user.games.viewFavorites') }}">Favorites</a>
                    @endguest
                </div>
            </nav>
            <nav class="fg-category-bar" aria-label="Popular game categories">
                <span class="fg-category-label">EXPLORE</span>
                @foreach(array_slice($menuCategories, 0, 8, true) as $category => $icon)
                    <a class="{{ request('category') === $category ? 'is-active' : '' }}" href="{{ $categoryDestination($category) }}"><span aria-hidden="true">{{ $icon }}</span>{{ $category }}</a>
                @endforeach
                <a href="{{ route('playable.index') }}" class="fg-all-games">All games →</a>
            </nav>
        </div>
    </header>
