@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-white">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold">Play Games</h1>
        <form class="flex flex-wrap gap-2" action="{{ route('playable.index') }}" method="GET">
            <label for="game-search" class="sr-only">Search games</label>
            <input id="game-search" name="search" type="search" value="{{ $search }}" placeholder="Search games..." class="bg-slate-800 border border-slate-600 rounded px-3 py-2 text-white">
            <button type="submit" class="bg-blue-600 rounded px-3 py-2">Search</button>
            <label for="game-category" class="sr-only">Category</label>
            <select id="game-category" name="category" onchange="this.form.submit()" class="bg-slate-800 border border-slate-600 rounded px-3 py-2 text-white">
                <option value="">All categories</option>
                @foreach($categories as $name)
                    <option value="{{ $name }}" @selected($category === $name)>{{ $name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @if($games->isEmpty())
        <p class="text-slate-300">No games are available right now.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            @foreach($games as $game)
                <a href="{{ route('playable.play', $game['id']) }}" class="block bg-slate-800 rounded overflow-hidden border border-slate-700 hover:border-blue-400">
                    @if($game['image'])<img src="{{ $game['image'] }}" alt="{{ $game['name'] }}" class="w-full aspect-[3/2] object-cover" loading="lazy">@endif
                    <span class="block px-3 pt-2 text-sm font-semibold truncate">{{ $game['name'] }}</span>
                    <span class="block px-3 pb-2 text-xs text-slate-400 truncate">{{ $game['category'] }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $games->links() }}</div>
    @endif
</div>
@endsection
