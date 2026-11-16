@extends('layouts.app')
@section('title', 'My Dashboard | Free Games')
@section('styles')
@include('user.dashboard-style')
@endsection

@section('content')
@php
    $favoriteGames = $favoriteGames ?? collect();
    $recentGames = $recentGames ?? collect();
    $displayName = $userInfo->name ?: ($userInfo->username ?: 'Player');
    $stats = [
        ['Favorites', $favoriteCount ?? 0, 'user.games.viewFavorites', 'fa-regular fa-heart', 'pink'],
        ['Saved games', $libraryCount ?? 0, 'catalog.user.library', 'fa-solid fa-border-all', 'purple'],
        ['Games played', $playedCount ?? 0, 'user.games.lastplay', 'fa-solid fa-play', 'blue'],
        ['Lottery tickets', $ticketCount ?? 0, 'user.lottaries.my', 'fa-solid fa-bolt', 'gold'],
    ];
    $collections = [
        [
            'heading' => 'Continue playing',
            'description' => 'Your most recent games, one click away.',
            'games' => $recentGames,
            'route' => 'user.games.lastplay',
            'emptyTitle' => 'No games played yet',
            'emptyDescription' => 'Your recently played games will appear here.',
            'icon' => 'fa-regular fa-square-caret-right',
            'tone' => 'blue',
        ],
        [
            'heading' => 'Your favorites',
            'description' => 'The games you keep coming back to.',
            'games' => $favoriteGames,
            'route' => 'user.games.viewFavorites',
            'emptyTitle' => 'Your next favorite is waiting',
            'emptyDescription' => 'Save a game to build your own collection.',
            'icon' => 'fa-regular fa-heart',
            'tone' => 'purple',
        ],
    ];
@endphp

<div class="player-dashboard">
    <section class="player-welcome" aria-labelledby="player-welcome-title">
        <div class="player-welcome-copy">
            <span class="player-eyebrow">YOUR GAMING SPACE</span>
            <h1 id="player-welcome-title">Ready for your next<br>game, <span>{{ $displayName }}?</span></h1>
            <p>Pick up where you left off. Find a new favorite. Make time for play.</p>
            <a class="player-primary" href="{{ route('playable.index') }}">
                Explore free games <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            </a>
        </div>
        <div class="player-art" aria-hidden="true">
            <img src="{{ asset('images/player-dashboard-controller.svg') }}" width="600" height="300" alt="" decoding="async">
            <span class="art-label">FREE TO PLAY. YOUR WAY.</span>
        </div>
    </section>

    <section class="player-stats" aria-label="Your gaming statistics">
        @foreach($stats as [$label, $count, $route, $icon, $tone])
            <a class="player-stat" href="{{ route($route) }}" aria-label="{{ $label }}: {{ number_format($count) }}">
                <span class="stat-icon {{ $tone }}" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                <span class="stat-copy">
                    <strong>{{ number_format($count) }}</strong>
                    <span>{{ $label }}</span>
                </span>
                <i class="fa-solid fa-arrow-up-right-from-square stat-arrow" aria-hidden="true"></i>
            </a>
        @endforeach
    </section>

    <div class="player-columns">
        <div class="player-feed">
            @foreach($collections as $section)
                <section class="player-section" aria-labelledby="player-section-{{ $loop->index }}">
                    <div class="player-section-heading">
                        <div>
                            <h2 id="player-section-{{ $loop->index }}">{{ $section['heading'] }}</h2>
                            <p>{{ $section['description'] }}</p>
                        </div>
                        <a href="{{ route($section['route']) }}">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    @if($section['games']->isNotEmpty())
                        <div class="player-games">
                            @foreach($section['games'] as $entry)
                                @if($entry->game)
                                    <a class="player-game" href="{{ route('games.open', ['id' => $entry->game->id]) }}">
                                        <img src="{{ asset($entry->game->image ?: 'default.png') }}" alt="" loading="lazy" decoding="async">
                                        <div>
                                            <h3>{{ $entry->game->name }}</h3>
                                            <span>Play game <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
                                        </div>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="player-empty">
                            <i class="empty-symbol {{ $section['tone'] }} {{ $section['icon'] }}" aria-hidden="true"></i>
                            <h3>{{ $section['emptyTitle'] }}</h3>
                            <p>{{ $section['emptyDescription'] }}</p>
                            <a href="{{ route('playable.index') }}">Browse games <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>

        <aside class="player-sidebar" aria-label="Lottery and account shortcuts">
            <section class="player-lottery" aria-labelledby="player-lottery-heading">
                <i class="fa-solid fa-crown player-lottery-watermark" aria-hidden="true"></i>
                <span class="player-eyebrow"><i class="fa-solid fa-gift" aria-hidden="true"></i> LOTTERY CORNER</span>
                <h2 id="player-lottery-heading">A little extra excitement</h2>
                <p>Your tickets and results, all in one place.</p>
                <div class="lottery-summary">
                    <div class="lottery-metric">
                        <i class="fa-solid fa-ticket" aria-hidden="true"></i>
                        <span><strong>{{ number_format($ticketCount ?? 0) }}</strong><small>Tickets</small></span>
                    </div>
                    <div class="lottery-metric">
                        <i class="fa-solid fa-trophy" aria-hidden="true"></i>
                        <span><strong>{{ number_format($winCount ?? 0) }}</strong><small>Wins</small></span>
                    </div>
                </div>
                <a class="player-secondary" href="{{ route('user.lottaries.my') }}">
                    My tickets <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
                <a class="lottery-results" href="{{ route('user.lottaries.mywin') }}">View winning results</a>
            </section>

            <section class="player-account" aria-labelledby="player-account-title">
                <h2 id="player-account-title"><i class="fa-regular fa-user" aria-hidden="true"></i> Your account</h2>
                <a href="{{ route('user.profile') }}">
                    <i class="fa-regular fa-user account-link-icon" aria-hidden="true"></i>
                    <span>Profile &amp; password<small>Keep your details up to date.</small></span>
                    <i class="fa-solid fa-arrow-right account-arrow" aria-hidden="true"></i>
                </a>
                <a href="{{ route('2fa.setup') }}">
                    <i class="fa-solid fa-shield-halved account-link-icon" aria-hidden="true"></i>
                    <span>Account security
                        <small>{{ $userInfo->google2fa_status ? 'Two-factor authentication enabled.' : 'Set up two-factor authentication.' }}</small>
                    </span>
                    <i class="fa-solid fa-arrow-right account-arrow" aria-hidden="true"></i>
                </a>
                <a href="{{ route('catalog.user.settings') }}">
                    <i class="fa-solid fa-gamepad account-link-icon" aria-hidden="true"></i>
                    <span>Gaming preferences<small>Choose what you love to play.</small></span>
                    <i class="fa-solid fa-arrow-right account-arrow" aria-hidden="true"></i>
                </a>
            </section>
        </aside>
    </div>
</div>
@endsection
