<aside class="cms-sidebar" id="cms-sidebar" data-cms-sidebar aria-label="{{ $portalRole === 'admin' ? 'CMS navigation' : 'Player navigation' }}">
    <a href="{{ route($portalHomeRoute) }}" class="cms-sidebar-head" aria-label="{{ $portalTitle }} dashboard">
        <span class="cms-mark" aria-hidden="true"><i class="fa-solid fa-gamepad"></i></span>
        <span class="cms-brand">{{ $portalTitle }}<small>{{ $portalSubtitle }}</small></span>
    </a>
    <div class="cms-sidebar-scroll">
        <nav aria-label="{{ $portalRole === 'admin' ? 'Admin sections' : 'User sections' }}">
            @foreach($portalGroups as $groupTitle => $entries)
                <section class="cms-nav-group" aria-label="{{ $groupTitle }}">
                    <div class="cms-nav-group-title">{{ $groupTitle }}</div>
                    @foreach($entries as [$label, $routeName, $active, $glyph])
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
        <form method="POST" action="{{ route($portalLogoutRoute) }}">
            @csrf
            <button class="cms-logout" type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Sign out securely</button>
        </form>
    </div>
</aside>
