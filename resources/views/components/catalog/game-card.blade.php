@props(['game'])
@php
    $imageCandidate = $game->image ?? $game->backgroundImage;
    $imageUrl = filter_var($imageCandidate, FILTER_VALIDATE_URL) && in_array(parse_url($imageCandidate, PHP_URL_SCHEME), ['http', 'https'], true) ? $imageCandidate : null;
    $description = trim(preg_replace('/\s+/', ' ', strip_tags($game->description ?? '')) ?? '');
    $providerLabel = match ($game->provider) { 'rawg' => 'RAWG', 'freetogame' => 'FreeToGame', 'gamerpower' => 'GamerPower', 'cheapshark' => 'CheapShark', default => ucfirst($game->provider) };
    $saved = auth()->check() ? once(fn () => app(\App\Services\CatalogPersonalizationService::class)->savedKeys(auth()->user())) : [];
    $isSaved = isset($saved[strtolower($game->provider).'|'.$game->providerId]);
@endphp
<article class="game-card">
    <a class="game-card__media" href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}" aria-label="View {{ $game->title }}">
        <span class="game-card__fallback" aria-hidden="true">🎮</span>
        @if($imageUrl)<img src="{{ $imageUrl }}" alt="{{ $game->title }} cover" loading="lazy" decoding="async" onerror="this.hidden=true">@endif
        <span class="game-card__source">{{ $providerLabel }}</span>
        @if($game->discount !== null && $game->discount > 0)<span class="game-card__discount">-{{ (int) round($game->discount) }}%</span>@elseif($game->isFree)<span class="game-card__discount">FREE</span>@endif
    </a>
    <div class="game-card__body">
        <div class="game-card__meta">@if($game->genres)<x-catalog.badge>{{ $game->genres[0] }}</x-catalog.badge>@endif @if($game->rating !== null)<span class="game-rating">★ {{ number_format($game->rating, 1) }}</span>@endif</div>
        <h3><a href="{{ route('catalog.show', ['provider' => $game->provider, 'id' => $game->providerId]) }}">{{ $game->title }}</a></h3>
        @if($description !== '')<p class="game-card__description">{{ \Illuminate\Support\Str::limit($description, 105) }}</p>@endif
        <div class="game-card__footer"><span class="game-card__platforms">{{ $game->platforms ? \Illuminate\Support\Str::limit(implode(' · ', array_slice($game->platforms, 0, 3)), 50) : 'Platform info unavailable' }}</span>@if($game->price !== null)<strong>{{ $game->price <= 0 ? 'Free' : '$'.number_format($game->price, 2) }}</strong>@endif</div>
        <div class="catalog-save-action">
            @auth
                <form method="POST" action="{{ $isSaved ? route('catalog.user.saved.destroy', [$game->provider, $game->providerId]) : route('catalog.user.saved.store', [$game->provider, $game->providerId]) }}">@csrf @if($isSaved) @method('DELETE') @endif<button type="submit">{{ $isSaved ? 'Saved ✓' : '♡ Save' }}</button></form>
            @else
                <a href="{{ route('login.form', ['redirect_to' => request()->fullUrl()]) }}">♡ Save</a>
            @endauth
        </div>
    </div>
</article>
