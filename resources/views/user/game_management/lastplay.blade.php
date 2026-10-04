@extends('layouts.app')

@section('content')
<div class="max-w-full px-4 py-8 bg-gray-900">
    
    <!-- Casino Games Section -->
    <h1 class="text-3xl font-bold mt-12 mb-6 text-white border-l-4 border-green-500 pl-3">Casino Games</h1>
    @if($games->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($games as $casino)
            <div class="relative game-card bg-gray-800 rounded-xl overflow-hidden shadow-lg transition transform hover:-translate-y-1 hover:shadow-2xl">
                <img src="{{ $casino->img }}" alt="{{ $casino->name }}" class="w-full h-36 object-cover">
                <div class="p-3">
                    <h2 class="text-white font-semibold truncate">{{ $casino->name }}</h2>
                    <p class="text-gray-400 text-sm">Last Played: {{ $casino->last_played_at->diffForHumans() }}</p>
                </div>

                <!-- Overlay -->
                <div class="overlay absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 transition gap-2">
                    <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}" 
                       target="_blank" 
                       class="px-4 py-2 bg-green-500 text-black rounded-full text-sm font-medium opacity-0 transition">
                       Play
                    </a>
                    <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}" 
                       target="_blank" 
                       class="px-4 py-2 bg-yellow-500 text-black rounded-full text-sm font-medium opacity-0 transition">
                       Demo
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-20 bg-gray-800 bg-opacity-50 rounded-xl">
            <h3 class="text-gray-300 text-xl mb-4">No Casino Games available yet</h3>
            <a href="{{ route('casino.index') }}" class="px-5 py-2 bg-green-500 text-black font-semibold rounded-lg hover:bg-green-600 transition">Explore Games</a>
        </div>
    @endif

    <!-- Recently Played Games -->
    <h1 class="text-3xl font-bold mb-6 text-white border-l-4 border-green-500 pl-3 mt-12">Recently Played Games</h1>
    @if($gameOpens->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @foreach($gameOpens as $gameOpen)
            <a href="{{ route('games.open', ['id' => $gameOpen->game->id]) }}" 
               class="relative game-card bg-gray-800 rounded-xl overflow-hidden shadow-lg transition transform hover:-translate-y-1 hover:shadow-2xl">
                <img src="{{ asset($gameOpen->game->image) }}" alt="{{ $gameOpen->game->name }}" class="w-full h-36 object-cover">
                <div class="p-3">
                    <h2 class="text-white font-semibold truncate">{{ $gameOpen->game->name }}</h2>
                    <p class="text-gray-400 text-sm">Played {{ $gameOpen->created_at->diffForHumans() }}</p>
                </div>
                <div class="overlay absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 transition">
                    <span class="px-4 py-2 bg-green-500 text-black rounded-full text-sm font-medium opacity-0 transition">Play Again</span>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-8 flex justify-center">
            {{ $gameOpens->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-gray-800 bg-opacity-50 rounded-xl">
            <h3 class="text-gray-300 text-xl mb-4">No recently played games yet</h3>
            <a href="{{ route('games.viewIndex') }}" class="px-5 py-2 bg-green-500 text-black font-semibold rounded-lg hover:bg-green-600 transition">Explore Games</a>
        </div>
    @endif

</div>

<!-- JS for mobile tap overlay -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".game-card").forEach(card => {
        const overlay = card.querySelector(".overlay");
        const buttons = overlay.querySelectorAll('a, span');

        card.addEventListener("click", function () {
            // Toggle overlay opacity
            if (overlay.style.opacity === "1") {
                overlay.style.opacity = "0";
                buttons.forEach(btn => btn.classList.add("opacity-0"));
            } else {
                overlay.style.opacity = "1";
                buttons.forEach(btn => btn.classList.remove("opacity-0"));
            }
        });

        // Desktop hover effect
        card.addEventListener("mouseover", function () {
            overlay.style.opacity = "1";
            buttons.forEach(btn => btn.classList.remove("opacity-0"));
        });
        card.addEventListener("mouseout", function () {
            overlay.style.opacity = "0";
            buttons.forEach(btn => btn.classList.add("opacity-0"));
        });
    });
});
</script>
@endsection
