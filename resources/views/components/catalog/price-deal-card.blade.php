@props(['game'])

@php
    $dealUrl = filter_var($game->dealUrl, FILTER_VALIDATE_URL)
        && in_array(parse_url($game->dealUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $game->dealUrl
            : null;
@endphp

<article class="deal-card">
    <div>
        <x-catalog.badge tone="deal">Deal</x-catalog.badge>
        <h3><a href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}">{{ $game->title }}</a></h3>
        <div class="deal-card__pricing">
            @if($game->price !== null)
                <strong>{{ $game->price <= 0 ? 'Free' : '$'.number_format($game->price, 2) }}</strong>
            @endif
            @if($game->normalPrice !== null && $game->normalPrice > ($game->price ?? -1))
                <del>${{ number_format($game->normalPrice, 2) }}</del>
            @endif
            @if($game->discount !== null && $game->discount > 0)
                <span>{{ (int) round($game->discount) }}% off</span>
            @endif
        </div>
    </div>
    <div class="deal-card__actions">
        <a class="catalog-button catalog-button--secondary" href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}">Details</a>
        @if($dealUrl)
            <a class="catalog-button" href="{{ $dealUrl }}" target="_blank" rel="noopener noreferrer nofollow">View deal</a>
        @endif
    </div>
</article>
