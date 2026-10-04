@extends('catalog.layout')

@section('content')
<div class="catalog-shell catalog-page">
    <header class="catalog-page-header">
        <x-catalog.badge tone="free">Free-to-play</x-catalog.badge>
        <h1>Play without an upfront purchase</h1>
        <p>Browse normalized free-to-play titles with platform, genre and publisher information where available.</p>
    </header>

    @if($games->count())
        <div class="game-grid">
            @foreach($games as $game)
                <x-catalog.game-card :game="$game" />
            @endforeach
        </div>
        <x-catalog.pagination :paginator="$games" />
    @else
        <x-catalog.state
            title="Free-to-play catalog unavailable"
            message="The provider may be temporarily unavailable. Cached data is used automatically when possible."
            type="error"
            :actionHref="route('catalog.index')"
            actionLabel="Back to discover"
        />
    @endif
</div>
@endsection
