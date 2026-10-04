@props(['game'])
@php
    $saved = auth()->check() ? once(fn () => app(\App\Services\CatalogPersonalizationService::class)->savedKeys(auth()->user())) : [];
    $isSaved = isset($saved[strtolower($game->provider).'|'.$game->providerId]);
@endphp
<div class="game-detail-save">
    @auth
        <form method="POST" action="{{ $isSaved ? route('catalog.user.saved.destroy', [$game->provider, $game->providerId]) : route('catalog.user.saved.store', [$game->provider, $game->providerId]) }}">
            @csrf
            @if($isSaved) @method('DELETE') @endif
            <button class="catalog-button catalog-button--secondary" type="submit">{{ $isSaved ? 'Remove from Saved' : 'Save to Watchlist' }}</button>
        </form>
    @else
        <a class="catalog-button catalog-button--secondary" href="{{ route('login.form', ['redirect_to' => request()->fullUrl()]) }}">Save to Watchlist</a>
    @endauth
</div>
