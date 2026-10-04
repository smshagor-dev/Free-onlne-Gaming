@extends('catalog.layout')

@section('content')
<div class="catalog-shell catalog-page">
    <header class="catalog-page-header">
        <x-catalog.badge tone="deal">Deals</x-catalog.badge>
        <h1>Current game deals</h1>
        <p>Compare sale and normal prices from CheapShark without exposing provider internals to the view layer.</p>
    </header>

    @if($games->count())
        <div class="deal-list">
            @foreach($games as $game)
                <x-catalog.price-deal-card :game="$game" />
            @endforeach
        </div>
        <x-catalog.pagination :paginator="$games" />
    @else
        <x-catalog.state
            title="Deals are temporarily unavailable"
            message="The provider may be down or rate-limited. Cached data is used when safe."
            type="error"
            :actionHref="route('catalog.index')"
            actionLabel="Back to discover"
        />
    @endif
</div>
@endsection
