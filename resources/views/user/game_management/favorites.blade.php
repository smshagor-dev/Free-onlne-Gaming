@extends('layouts.app')

@section('content')
<style>
    .game-card {
        transition: all 0.3s ease;
    }
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
</style>

<div class="w-full p-5 box-border bg-gray-900 min-h-screen">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="p-2 bg-red-600 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 16 16">
                <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
            </svg>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white">My Favorite Games</h1>
    </div>

    <!-- Paid Games Section -->
    <div class="mb-10">
        <h2 class="text-xl md:text-2xl font-bold mb-4 text-white border-l-4 border-green-500 pl-3">Paid Games</h2>
        
        @if($games->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($games as $casino)
                    <div class="game-card relative group bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                        <!-- Game Image -->
                        <img src="{{ $casino->img }}" alt="{{ $casino->name }}" class="w-full h-32 md:h-36 object-cover">

                        <!-- Auth Buttons -->
                        @auth
                            <!-- Favourite Icon -->
                            <button 
                                class="absolute top-2 left-2 text-xl fav-btn z-20 bg-gray-900 bg-opacity-60 rounded-full p-1"
                                data-game="{{ $casino->id }}">
                                <i class="fas fa-star {{ $casino->lastPlay && $casino->lastPlay->is_favourite ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                            </button>

                            <!-- Bookmark Icon -->
                            <button 
                                class="absolute top-2 right-2 text-xl bookmark-btn z-20 bg-gray-900 bg-opacity-60 rounded-full p-1"
                                data-game="{{ $casino->id }}">
                                <i class="fas fa-bookmark {{ $casino->lastPlay && $casino->lastPlay->is_bookmark ? 'text-green-400' : 'text-gray-400' }}"></i>
                            </button>
                        @endauth

                        <!-- Card Content -->
                        <div class="p-3">
                            <h2 class="text-white font-semibold text-sm truncate">{{ $casino->name }}</h2>
                            <p class="text-gray-400 text-xs mt-1">Added: {{ $casino->last_played_at->diffForHumans() }}</p>
                        </div>

                        <!-- Play / Demo Buttons -->
                        <div class="game-overlay absolute inset-0 flex justify-center items-center gap-2 bg-black bg-opacity-40 md:bg-opacity-0 transition opacity-0 md:opacity-0 group-hover:opacity-100">
                            <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}" 
                            target="_blank" 
                            class="px-4 py-2 bg-green-500 text-black rounded-full text-sm font-medium">
                            Play
                            </a>
                            <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}" 
                            target="_blank" 
                            class="px-4 py-2 bg-yellow-500 text-black rounded-full text-sm font-medium">
                            Demo
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-gray-800 rounded-xl">
                <div class="mb-4 text-gray-400">
                    <i class="fas fa-gamepad text-4xl"></i>
                </div>
                <h3 class="text-gray-300 text-lg mb-4">No paid games available yet</h3>
                <a href="{{ route('casino.index') }}" class="inline-block px-5 py-2 bg-green-500 text-black font-semibold rounded-lg hover:bg-green-600 transition">Explore Games</a>
            </div>
        @endif
    </div>

    <!-- Free Games Section -->
    <div class="mb-10">
        <h2 class="text-xl md:text-2xl font-bold mb-4 text-white border-l-4 border-green-500 pl-3">Free Games</h2>
        
        @if($favorites->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                @foreach($favorites as $favorite)
                    <div id="game-card-{{ $favorite->game->id }}" class="game-card bg-gray-800 rounded-lg overflow-hidden shadow-lg border border-gray-700 transition-all duration-300">
                        <a href="{{ route('games.open', ['id' => $favorite->game->id]) }}" class="block relative">
                            <img src="{{ asset($favorite->game->image) }}" alt="{{ $favorite->game->name }}" class="w-full h-32 md:h-36 object-cover">
                            <div class="play-btn absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 md:bg-opacity-0 transition-all duration-300 opacity-100 md:opacity-0">
                                <span class="bg-green-500 text-black px-4 py-2 rounded-full text-sm font-medium">Play</span>
                            </div>
                        </a>

                        <div class="p-3 relative">
                            <h3 class="text-white font-medium text-sm truncate pr-8">{{ $favorite->game->name }}</h3>
                            <button data-game-id="{{ $favorite->game->id }}" title="Remove from favorites" class="favorite-btn absolute top-3 right-3 text-red-500 hover:text-red-400 transition-colors">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                {{ $favorites->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-gray-800 rounded-xl">
                <div class="mb-4 text-gray-400">
                    <i class="fas fa-clock text-4xl"></i>
                </div>
                <h3 class="text-gray-300 text-lg mb-4">No recently played games yet</h3>
                <a href="{{ route('games.viewIndex') }}" class="inline-block px-5 py-2 bg-green-500 text-black font-semibold rounded-lg hover:bg-green-600 transition">Explore Games</a>
            </div>
        @endif
    </div>
</div>

<script>

document.querySelectorAll('.game-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Avoid triggering when clicking favorite/bookmark
        if (e.target.closest('.fav-btn') || e.target.closest('.bookmark-btn')) return;

        let overlay = this.querySelector('.game-overlay');

        if (overlay) {
            // First, hide all other overlays
            document.querySelectorAll('.game-overlay').forEach(o => {
                o.classList.add('opacity-0');
                o.classList.remove('opacity-100');
            });

            // Then show only the current one
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
        }
    });
});


function unfavoriteGame(gameId) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = tokenMeta ? tokenMeta.content : '';

    return fetch(`/user/games/${gameId}/unfavorite`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json());
}

function handleUnfavoriteResponse(gameId, data) {
    if (data.status) {
        const gameCard = document.getElementById(`game-card-${gameId}`);
        if (gameCard) {
            gameCard.style.opacity = '0';
            setTimeout(() => {
                gameCard.remove();
                if (document.querySelectorAll('[id^="game-card-"]').length === 0) {
                    window.location.reload();
                }
            }, 300);
        }
    }
}

document.querySelectorAll('.favorite-btn').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        const gameId = this.getAttribute('data-game-id') || this.dataset.gameId;
        unfavoriteGame(gameId).then(data => handleUnfavoriteResponse(gameId, data));
    });
});

// Toggle favorite and bookmark functionality
document.addEventListener("DOMContentLoaded", function () {
    function postAndReload(url, data) {
        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if(res.message) console.log(res.message);
            window.location.reload();
        })
        .catch(err => console.error("Error:", err));
    }

    document.querySelectorAll(".fav-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            let gameId = this.dataset.game;
            postAndReload("{{ route('user.game.toggleFavourite') }}", { game_id: gameId });
        });
    });

    document.querySelectorAll(".bookmark-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            let gameId = this.dataset.game;
            postAndReload("{{ route('user.game.toggleBookmark') }}", { game_id: gameId });
        });
    });
});
</script>

@endsection
