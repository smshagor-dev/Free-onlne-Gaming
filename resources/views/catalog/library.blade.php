@extends('catalog.layout')
@section('content')
<div class="catalog-shell"><section class="catalog-section"><x-catalog.section-header title="My Games" subtitle="Saved catalog titles and recently viewed history." :href="route('catalog.user.settings')" />
<h2>Saved Games</h2>@if($saved)<div class="game-grid">@foreach($saved as $game)<x-catalog.game-card :game="$game" />@endforeach</div>@else<x-catalog.state title="Your watchlist is empty" message="Save any catalog game to find it here." />@endif
<div class="catalog-library-heading"><h2>Recently Viewed</h2>@if($recent)<form method="POST" action="{{ route('catalog.user.recent.clear') }}">@csrf @method('DELETE')<button type="submit">Clear history</button></form>@endif</div>
@if($recent)<div class="game-grid game-grid--compact">@foreach($recent as $game)<x-catalog.game-card :game="$game" />@endforeach</div>@else<x-catalog.state title="No recent games yet" message="Viewed catalog games will appear here." />@endif
</section></div>
@endsection
