@extends('layouts.app')

@section('content')
<main class="w-full px-3 py-3 text-white">
    <div class="flex items-center justify-between gap-3 mb-3">
        <div class="min-w-0">
            <a href="{{ route('playable.index') }}" class="text-sm text-blue-300 hover:text-white">&larr; All games</a>
            <h1 class="text-lg font-semibold truncate">{{ $game['name'] }}</h1>
        </div>
        <button type="button" onclick="document.getElementById('game-frame').requestFullscreen()" class="shrink-0 px-3 py-2 bg-slate-700 rounded text-sm" title="Fullscreen game"><i class="fas fa-expand mr-1"></i> Fullscreen</button>
    </div>
    <iframe id="game-frame" src="{{ $game['url'] }}" title="Play {{ $game['name'] }}" class="block w-full border-0 bg-black" style="height: min(78vh, 900px); min-height: 420px" allow="autoplay; fullscreen; gamepad" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
</main>
@endsection
