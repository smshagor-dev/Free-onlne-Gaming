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
                    <a class="fg-nav-link {{ request()->routeIs('user.lottaries.*') ? 'is-active' : '' }}" href="{{ route('user.lottaries.view') }}">Lottery</a>
                    <a class="fg-nav-link {{ request()->routeIs('contacts.create') ? 'is-active' : '' }}" href="{{ route('contacts.create') }}">Contact</a>
                </div>

                <div class="fg-actions">
                    @guest
                        <a class="fg-auth fg-auth-primary" href="{{ route('login.form') }}">Login</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('register.form') }}">Sign Up</a>
                    @else
                        <a class="fg-auth fg-auth-primary" href="{{ route('user.dashboard') }}">Account</a>
                        <a class="fg-auth fg-auth-ghost" href="{{ route('user.games.viewFavorites') }}">Favorites</a>
                    @endguest
                </div>
            </nav>
        </div>
    </header>
