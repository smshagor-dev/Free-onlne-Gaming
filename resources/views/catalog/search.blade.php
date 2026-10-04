@extends('catalog.layout')

@section('content')
<div class="catalog-shell catalog-page">
    <header class="catalog-page-header">
        <x-catalog.badge tone="accent">Unified search</x-catalog.badge>
        <h1>Search games</h1>
        <p>Search RAWG, FreeToGame, GamerPower and CheapShark through one normalized catalog.</p>
    </header>

    <form action="{{ route('catalog.search') }}" method="GET" class="catalog-search catalog-search--page" role="search" data-search-form>
        <label for="page-game-search">Game title</label>
        <div>
            <input id="page-game-search" name="q" type="search" value="{{ $query }}" placeholder="e.g. Cyberpunk, Fortnite, Hades" maxlength="120" autofocus>
            <button type="submit">Search</button>
        </div>
        <span class="catalog-search__loading" data-loading hidden aria-live="polite">Searching providers…</span>
    </form>

    @if($query === '')
        <x-catalog.state
            title="Start with a game title"
            message="Results from supported providers will appear here."
        />
    @elseif($games->count())
        <div class="catalog-results-summary">
            <h2>Results for “{{ $query }}”</h2>
            <p>{{ number_format($games->total()) }} normalized {{ \Illuminate\Support\Str::plural('result', $games->total()) }}</p>
        </div>

        <div class="game-grid">
            @foreach($games as $game)
                <x-catalog.game-card :game="$game" />
            @endforeach
        </div>

        <x-catalog.pagination :paginator="$games" />
    @else
        <x-catalog.state
            title="No matching games found"
            message="Try a shorter title or different spelling. A provider outage may also temporarily reduce results."
            :actionHref="route('catalog.index')"
            actionLabel="Browse discover"
        />
    @endif
</div>
@endsection
