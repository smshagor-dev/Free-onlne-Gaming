@php
    $availableCategories = collect(\Illuminate\Support\Facades\Cache::get('gamemonetize.embeddable.all', []))->pluck('category')->filter()->unique()->sort()->values();
    $navCms = \App\Services\SiteCms::section('navigation');
    $catalogCms = \App\Services\SiteCms::section('catalog');
    $menuCategories = ['Action' => '✦', 'Adventure' => '◇', 'Racing' => '➜', 'Shooting' => '◎', 'Sports' => '◉', 'Puzzle' => '▦', 'Multiplayer' => '♧', 'Simulation' => '◈', 'Arcade' => '▣', 'Strategy' => '♜'];
    $selectedMenuCategories = collect(explode(',', $navCms['category_labels']))
        ->map(fn ($name) => trim($name))->filter()
        ->reject(fn ($name) => preg_match('/casino|slots?|roulette|blackjack|baccarat|poker|gambling/i', $name))
        ->unique(fn ($name) => mb_strtolower($name));
    $menuCategories = collect($menuCategories)
        ->filter(fn ($icon, $name) => $selectedMenuCategories->contains(fn ($item) => strcasecmp($item, $name) === 0))
        ->all();
    $categoryDestination = function ($category) use ($availableCategories) {
        $aliases = ['Shooting' => 'Shooter', 'Simulation' => 'Sim'];
        $actual = $availableCategories->first(fn ($name) => strcasecmp($name, $category) === 0 || strcasecmp($name, $aliases[$category] ?? $category) === 0);
        return route('playable.index', ['category' => $actual ?? $category]);
    };
@endphp
    <header class="fg-topbar">
        <div class="fg-shell">
            <nav class="fg-nav" aria-label="Primary navigation">
                @php($brandTitle = filled($setting->name ?? null) ? $setting->name : 'Free Games')
                <a class="fg-brand" href="{{ route('games.viewIndex') }}" aria-label="{{ $brandTitle }} home">
                    <span class="fg-brand-icon {{ filled($setting?->logo ?? null) ? 'has-custom-logo' : 'has-fallback-logo' }}" aria-hidden="true">
                        @if(filled($setting?->logo ?? null))
                            <img class="fg-brand-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($setting->logo) }}" alt="" width="42" height="42" decoding="async">
                        @else
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.7"><path d="M7 6h10c3 0 4 3 5 9s-2 6-5 3l-2-2H9l-2 2c-3 3-6 2-5-3S4 6 7 6Z"/><path d="M7 9v5M4.5 11.5h5"/><circle cx="16" cy="10" r=".8" fill="white"/><circle cx="18" cy="13" r=".8" fill="white"/></svg>
                        @endif
                    </span>
                    <span class="fg-brand-word" style="max-width:min(40vw,280px);overflow:hidden;text-overflow:ellipsis">
                        @if(trim($brandTitle) === 'Free Games')<span>Free</span><span>Games</span>
                        @else {{ $brandTitle }}
                        @endif
                    </span>
                </a>

                <div class="fg-nav-links" id="fg-menu">
                    @if($navCms['show_home'])<a class="fg-nav-link {{ request()->routeIs('games.viewIndex') ? 'is-active' : '' }}" @if(request()->routeIs('games.viewIndex')) aria-current="page" @endif href="{{ route('games.viewIndex') }}">{{ $navCms['home_label'] }}</a>@endif
                    @if($navCms['show_games'])<a class="fg-nav-link {{ request()->routeIs('playable.*', 'catalog.*') ? 'is-active' : '' }}" href="{{ route('playable.index') }}">{{ $navCms['games_label'] }}</a>@endif
                    <a class="fg-nav-link {{ request()->routeIs('casino.games.index') ? 'is-active' : '' }}"
                       @if(request()->routeIs('casino.games.index')) aria-current="page" @endif
                       href="{{ route('casino.games.index') }}">Casino games</a>
                    @if($navCms['show_categories'])
                    <details class="fg-category-menu">
                        <summary class="fg-nav-link"><svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="currentColor" aria-hidden="true"><rect x="2" y="2" width="6" height="6" rx="1"/><rect x="12" y="2" width="6" height="6" rx="1"/><rect x="2" y="12" width="6" height="6" rx="1"/><rect x="12" y="12" width="6" height="6" rx="1"/></svg> {{ $navCms['categories_label'] }} <span class="fg-chevron" aria-hidden="true"><svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="m4 6 4 4 4-4"/></svg></span></summary>
                        <div class="fg-category-dropdown">
                            <div class="fg-dropdown-heading"><strong>Explore categories</strong><a href="{{ route('playable.index') }}">All games →</a></div>
                            <div class="fg-category-options">
                                @foreach($menuCategories as $category => $icon)
                                    <a href="{{ $categoryDestination($category) }}"><span aria-hidden="true">{{ $icon }}</span>{{ $category }}</a>
                                @endforeach
                                @foreach($availableCategories->reject(fn ($category) =>
                                    preg_match('/casino|slots?|roulette|blackjack|baccarat|poker|gambling/i', $category)
                                    || !$selectedMenuCategories->contains(fn ($name) =>
                                        strcasecmp($name, $category) === 0 ||
                                        (strcasecmp($name, 'Shooting') === 0 && strcasecmp($category, 'Shooter') === 0) ||
                                        (strcasecmp($name, 'Simulation') === 0 && strcasecmp($category, 'Sim') === 0)
                                    )
                                    || array_key_exists($category, $menuCategories)
                                ) as $category)
                                    @if(!in_array($category, ['Shooter', 'Sim']))<a href="{{ $categoryDestination($category) }}"><span aria-hidden="true">◇</span>{{ $category }}</a>@endif
                                @endforeach
                            </div>
                        </div>
                    </details>
                    @endif
                    @if($navCms['show_lottery'])<a class="fg-nav-link {{ request()->routeIs('user.lottaries.*') ? 'is-active' : '' }}" href="{{ route('user.lottaries.view') }}">{{ $navCms['lottery_label'] }}</a>@endif
                    @if($navCms['show_contact'])<a class="fg-nav-link {{ request()->routeIs('contacts.create') ? 'is-active' : '' }}" href="{{ route('contacts.create') }}">{{ $navCms['contact_label'] }}</a>@endif
                </div>

                <div class="fg-header-controls">
                    <button class="fg-search-toggle" type="button" aria-controls="fg-header-search" aria-expanded="false" aria-label="Open game search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg>
                    </button>
                    <form id="fg-header-search" class="fg-nav-search" action="{{ route('playable.index') }}" method="GET" role="search" data-suggest-url="{{ route('playable.suggest') }}" autocomplete="off">
                    <label class="sr-only" for="navigation-search">Search games</label>
                    <input id="navigation-search" name="search" type="search" placeholder="Search games..." value="{{ request('search') }}" maxlength="80" enterkeyhint="search" aria-autocomplete="list" aria-controls="fg-search-results" aria-expanded="false" role="combobox">
                    <button type="submit" aria-label="Search games"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg></button>
                    <div class="fg-search-results" id="fg-search-results" role="listbox" aria-label="Matching games" hidden></div>
                </form>
                <div class="fg-actions">
                    @guest
                        <a class="fg-auth fg-auth-primary" href="{{ route('login.form') }}" data-auth-open="login" aria-haspopup="dialog" aria-controls="fg-auth-dialog">Login</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('register.form') }}" data-auth-open="register" aria-haspopup="dialog" aria-controls="fg-auth-dialog">Sign Up</a>
                    @else
                        <a class="fg-auth fg-auth-primary" href="{{ route('user.dashboard') }}">Account</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('user.games.viewFavorites') }}">Favorites</a>
                    @endguest
                </div>
                    <button class="fg-menu-toggle" type="button" aria-expanded="false" aria-controls="fg-menu">Menu</button>
                </div>
            </nav>
            @if($navCms['show_categories'])
            <nav class="fg-category-bar" aria-label="Popular game categories">
                <span class="fg-category-label">EXPLORE</span>
                @foreach(array_slice($menuCategories, 0, $catalogCms['category_bar_count'], true) as $category => $icon)
                    <a class="{{ request('category') === $category ? 'is-active' : '' }}" href="{{ $categoryDestination($category) }}"><span aria-hidden="true">{{ $icon }}</span>{{ $category }}</a>
                @endforeach
                <a href="{{ route('playable.index') }}" class="fg-all-games">All games →</a>
            </nav>
            @endif
        </div>
    </header>
