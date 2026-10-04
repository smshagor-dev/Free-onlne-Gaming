@props(['game'])

@php
    $imageCandidate = $game->image ?? $game->backgroundImage;
    $imageUrl = filter_var($imageCandidate, FILTER_VALIDATE_URL)
        && in_array(parse_url($imageCandidate, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $imageCandidate
            : null;
    $dealUrl = filter_var($game->dealUrl, FILTER_VALIDATE_URL)
        && in_array(parse_url($game->dealUrl, PHP_URL_SCHEME), ['http', 'https'], true)
            ? $game->dealUrl
            : null;
@endphp

<article class="giveaway-card">
    <div class="giveaway-card__media">
        <span class="game-card__fallback" aria-hidden="true">🎁</span>
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $game->title }} giveaway artwork" loading="lazy" decoding="async" onerror="this.hidden=true">
        @endif
    </div>
    <div class="giveaway-card__body">
        <div class="game-card__meta">
            <x-catalog.badge tone="free">{{ $game->giveaway['status'] ?? 'Giveaway' }}</x-catalog.badge>
            @if($game->normalPrice !== null)
                <span class="giveaway-card__worth">Worth ${{ number_format($game->normalPrice, 2) }}</span>
            @endif
        </div>
        <h3><a href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}">{{ $game->title }}</a></h3>
        @if(!empty($game->giveaway['end_date']))
            <p>Ends {{ $game->giveaway['end_date'] }}</p>
        @elseif($game->platforms)
            <p>{{ implode(' · ', array_slice($game->platforms, 0, 3)) }}</p>
        @endif
        <div class="giveaway-card__actions">
            <a class="catalog-text-link" href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}">Details</a>
            @if($dealUrl)
                <a class="catalog-button" href="{{ $dealUrl }}" target="_blank" rel="noopener noreferrer nofollow">Claim offer</a>
            @endif
        </div>
    </div>
</article>
