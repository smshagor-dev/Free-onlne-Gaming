@extends('layouts.app')
@section('title', 'My Dashboard | FreeGames')
@section('styles')
@include('user.dashboard-style')
@endsection
@section('content')
@php
    $favoriteGames = $favoriteGames ?? collect(); $recentGames = $recentGames ?? collect();
@endphp
<div class="player-dashboard">
    <section class="player-welcome">
        <div><span class="player-eyebrow">YOUR GAMING SPACE</span><h1>Ready for your next game,<br><span>{{ $userInfo->name }}?</span></h1><p>Pick up where you left off. Find a new favorite. Make time for play.</p><a class="player-primary" href="{{ route('playable.index') }}">Explore free games <span aria-hidden="true">↗</span></a></div>
        <div class="player-art" aria-hidden="true"><div class="art-orbit"></div><svg viewBox="0 0 240 180" fill="none"><path d="M68 50h104c20 0 32 15 37 36l13 49c6 25-20 38-36 21l-28-26H82l-28 26c-16 17-42 4-36-21l13-49c5-21 17-36 37-36Z" fill="#243c66" stroke="#73c5ff" stroke-width="4"/><path d="M66 78v38M47 97h38" stroke="#b2e4ff" stroke-width="9" stroke-linecap="round"/><circle cx="164" cy="84" r="8" fill="#bd9cff"/><circle cx="185" cy="105" r="8" fill="#64d6ca"/><path d="M103 108h13m11 0h13" stroke="#7799cc" stroke-width="5" stroke-linecap="round"/></svg><span class="art-label">FREE TO PLAY. YOUR WAY.</span></div>
    </section>
    <div class="player-stats">
        @foreach([['Favorites', $favoriteCount ?? 0, 'user.games.viewFavorites', '♡', 'pink'], ['Saved games', $libraryCount ?? 0, 'catalog.user.library', '▦', 'purple'], ['Games played', $playedCount ?? 0, 'user.games.lastplay', '▷', 'blue'], ['Lottery tickets', $ticketCount ?? 0, 'user.lottaries.my', '⌁', 'gold']] as [$label, $count, $route, $icon, $tone])
        <a class="player-stat" href="{{ route($route) }}"><span class="stat-icon {{ $tone }}" aria-hidden="true">{{ $icon }}</span><div><strong>{{ number_format($count) }}</strong><span>{{ $label }}</span></div><span class="stat-arrow" aria-hidden="true">↗</span></a>
        @endforeach
    </div>
    <div class="player-columns"><div class="player-feed">
        @foreach([['Continue playing', 'Your most recent games, one click away.', $recentGames, 'user.games.lastplay', 'No games played yet', 'Your recently played games will appear here.'], ['Your favorites', 'The games you keep coming back to.', $favoriteGames, 'user.games.viewFavorites', 'Your next favorite is waiting', 'Save a game to build your own collection.']] as [$heading, $description, $games, $route, $emptyTitle, $emptyText])
        <section class="player-section"><div class="player-section-heading"><div><h2>{{ $heading }}</h2><p>{{ $description }}</p></div><a href="{{ route($route) }}">View all <span aria-hidden="true">→</span></a></div>
        @if($games->isNotEmpty())<div class="player-games">@foreach($games as $entry)<a class="player-game" href="{{ route('games.open', ['id' => $entry->game->id]) }}"><img src="{{ asset($entry->game->image ?: 'default.png') }}" alt="" loading="lazy"><div><h3>{{ $entry->game->name }}</h3><span>Play game <span aria-hidden="true">↗</span></span></div></a>@endforeach</div>
        @else<div class="player-empty"><span class="empty-symbol" aria-hidden="true">{{ $loop->first ? '▷' : '♡' }}</span><div><h3>{{ $emptyTitle }}</h3><p>{{ $emptyText }}</p></div><a href="{{ route('playable.index') }}">Browse games →</a></div>@endif
        </section>
        @endforeach
    </div><aside class="player-sidebar">
        <section class="player-lottery"><span class="player-eyebrow">LOTTERY CORNER</span><h2>A little extra excitement</h2><p>Your tickets and results, all in one place.</p><div class="lottery-summary"><div><strong>{{ $ticketCount ?? 0 }}</strong><span>Tickets</span></div><div><strong>{{ $winCount ?? 0 }}</strong><span>Wins</span></div></div><a class="player-secondary" href="{{ route('user.lottaries.my') }}">My tickets <span aria-hidden="true">→</span></a><a class="lottery-results" href="{{ route('user.lottaries.mywin') }}">View winning results</a></section>
        <section class="player-account"><h2>Your account</h2><a href="{{ route('user.profile') }}"><span>Profile & password<small>Keep your details up to date</small></span><span aria-hidden="true">→</span></a><a href="{{ route('2fa.setup') }}"><span>Account security<small>{{ $userInfo->google2fa_status ? 'Two-factor authentication enabled' : 'Set up two-factor authentication' }}</small></span><span aria-hidden="true">→</span></a><a href="{{ route('catalog.user.settings') }}"><span>Gaming preferences<small>Choose what you love to play</small></span><span aria-hidden="true">→</span></a></section>
    </aside></div>
</div>
@endsection
