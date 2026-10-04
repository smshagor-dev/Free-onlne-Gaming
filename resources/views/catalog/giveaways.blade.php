@extends('catalog.layout')

@section('content')
<div class="catalog-shell catalog-page">
    <header class="catalog-page-header">
        <x-catalog.badge tone="free">Giveaways</x-catalog.badge>
        <h1>Active game giveaways</h1>
        <p>Limited-time free offers and claim links. Availability can change quickly, so always confirm the offer on the provider page.</p>
    </header>

    @if($games->count())
        <div class="giveaway-grid">
            @foreach($games as $game)
                <x-catalog.giveaway-card :game="$game" />
            @endforeach
        </div>
        <x-catalog.pagination :paginator="$games" />
    @else
        <x-catalog.state
            title="No giveaways available"
            message="There may be no active offers, or the provider may be temporarily unavailable."
            :actionHref="route('catalog.index')"
            actionLabel="Browse other games"
        />
    @endif
</div>
@endsection
