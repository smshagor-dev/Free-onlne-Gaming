@extends('layouts.app')
@section('title', 'Casino-style Games | Free Games')
@section('styles')
<style>
.casino-public {max-width:1320px;margin:0 auto;padding:24px 0 40px;color:#eef6ff}
.casino-public-hero {padding:26px 30px;border:1px solid #305477;border-radius:16px;background:linear-gradient(125deg,#102b49,#101b32)}
.casino-public-hero h1 {font-size:clamp(27px,4vw,42px);font-weight:850;line-height:1.2;margin:0 0 7px}
.casino-public-hero p {color:#bed1e8;max-width:760px;font-size:14px;margin:0}
.casino-public-tools {display:flex;align-items:center;gap:12px;margin:20px 0 18px;flex-wrap:wrap}
.casino-public-tools input {min-width:220px;flex:1;background:#152b44;border:1px solid #426082;border-radius:10px;color:#fff;padding:11px 15px}
.casino-public-tools button {background:#1397f6;color:white;font-weight:800;padding:12px 22px;border:0;border-radius:10px;cursor:pointer}
.casino-public-grid {display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
.casino-public-card {display:flex;flex-direction:column;overflow:hidden;border:1px solid #31506e;border-radius:13px;background:#102339;color:#f4f9ff}
.casino-public-image {width:100%;aspect-ratio:16/10;object-fit:cover;background:#193650}
.casino-public-fallback {display:grid;place-items:center;width:100%;aspect-ratio:16/10;background:#193650;color:#8ab4d9;font-size:35px}
.casino-public-card-body {padding:15px;display:flex;flex-direction:column;gap:9px;flex:1}
.casino-public-card h2 {font-size:16px;line-height:1.3;margin:0;font-weight:800}
.casino-public-card p {font-size:12px;color:#b5c9db;margin:0}
.casino-public-play {margin-top:auto;display:inline-flex;justify-content:center;background:#1687e7;color:#fff!important;font-weight:800;border-radius:9px;padding:9px}
.casino-public-empty {padding:38px;border:1px dashed #44698c;border-radius:13px;text-align:center;color:#bdd2e7}
@media(max-width:950px){.casino-public-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:690px){.casino-public-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.casino-public-hero{padding:22px 18px}}
@media(max-width:340px){.casino-public-grid{grid-template-columns:1fr}}
</style>
@endsection
@section('content')
<div class="casino-public">
    <div class="casino-public-hero">
        <h1>Casino-style games</h1>
        <p>Explore games managed by Free Games. Availability and features depend on the game publisher. This website does not provide a new betting or cash-out service through this listing.</p>
    </div>
    <form method="GET" action="{{ route('casino.games.index') }}" class="casino-public-tools" role="search">
        <label class="sr-only" for="casino-public-search">Find casino-style games</label>
        <input id="casino-public-search" name="search" type="search" maxlength="80" value="{{ $search }}" placeholder="Search games by name">
        <button type="submit">Search</button>
    </form>
    <div class="casino-public-grid">
        @forelse($games as $game)
            <article class="casino-public-card">
                @php($validImage = $game->image && filter_var($game->image, FILTER_VALIDATE_URL) && in_array(parse_url($game->image, PHP_URL_SCHEME), ['http', 'https'], true))
                @if($validImage)
                    <img class="casino-public-image" src="{{ $game->image }}" alt="" loading="lazy" decoding="async">
                @else
                    <div class="casino-public-fallback" aria-hidden="true"><i class="fa-solid fa-gamepad"></i></div>
                @endif
                <div class="casino-public-card-body">
                    <h2>{{ $game->name }}</h2>
                    <p>{{ $game->category?->name ?: 'Games' }} · {{ number_format($game->play_time ?? 0) }} recorded opens</p>
                    <a class="casino-public-play" href="{{ route('games.open', ['id' => $game->id]) }}">{{ auth()->check() ? 'Open game' : 'Sign in to play' }}</a>
                </div>
            </article>
        @empty
            <div class="casino-public-empty">No matching casino-style games have been added to this site's managed catalog.</div>
        @endforelse
    </div>
    <div style="margin-top:22px">{{ $games->links() }}</div>
</div>
@endsection
