@extends('layouts.app')

@section('content')
<style>
    .bookmarks-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-title svg {
        color: #e53e3e;
    }
    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }
    .game-card {
        /* background: white; */
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    .game-image {
        width: 100%;
        height: 140px;
        object-fit: cover;
    }
    .game-info {
        padding: 15px;
        position: relative;
    }
    .game-name {
        font-weight: 600;
        margin-bottom: 10px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 16px;
        padding-right: 30px;
    }
    .bookmark-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        color: #e53e3e;
        font-size: 20px;
        transition: transform 0.2s;
    }
    .bookmark-btn:hover {
        transform: scale(1.2);
    }
    .bookmark-date {
        font-size: 13px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: green;
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .game-card:hover .play-btn {
        opacity: 1;
    }
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        background-color: rgba(55, 65, 81, 0.7); /* bg-gray-800 with 70% opacity */
        backdrop-filter: blur(10px); /* backdrop-blur-lg */
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .empty-icon {
        font-size: 48px;
        color: #cbd5e0;
        margin-bottom: 15px;
    }
    .empty-text {
        font-size: 18px;
        color: #718096;
        margin-bottom: 20px;
    }
    .explore-btn {
        display: inline-block;
        padding: 10px 20px;
        background: #4299e1;
        color: white;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s;
    }
    .explore-btn:hover {
        background: #3182ce;
    }
    .pagination {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .page-title {
            font-size: 24px;
        }
        .game-image {
            height: 120px;
        }
        .play-btn {
            opacity: 1;
            font-size: 14px;
            padding: 6px 12px;
        }
    }
</style>

<div class="bookmarks-container">
    <div class="page-header">
        <h1 class="page-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
            </svg>
            My Bookmarks
        </h1>
    </div>
    
        <h1 class="text-3xl font-bold mt-12 mb-6 text-white border-l-4 border-green-500 pl-3">Casino Games</h1>

    @if($games->count() > 0)
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:20px;">
            @foreach($games as $casino)
                <div class="relative group bg-gray-800 rounded-xl overflow-hidden shadow-lg transition transform hover:-translate-y-1 hover:shadow-2xl">
                    
                    <!-- Game Image -->
                    <img src="{{ $casino->img }}" alt="{{ $casino->name }}" class="w-full h-36 object-cover">
    
                    <!-- Auth Buttons -->
                    @auth
                        <!-- Favourite Icon -->
                        <button 
                            class="absolute top-2 left-2 text-xl fav-btn z-20"
                            data-game="{{ $casino->id }}">
                            <i class="fas fa-star {{ $casino->lastPlay && $casino->lastPlay->is_favourite ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                        </button>
    
                        <!-- Bookmark Icon -->
                        <button 
                            class="absolute top-2 right-2 text-xl bookmark-btn z-20"
                            data-game="{{ $casino->id }}">
                            <i class="fas fa-bookmark {{ $casino->lastPlay && $casino->lastPlay->is_bookmark ? 'text-green-400' : 'text-gray-400' }}"></i>
                        </button>
                    @endauth
    
                    <!-- Card Content -->
                    <div class="p-3">
                        <h2 class="text-white font-semibold truncate">{{ $casino->name }}</h2>
                        <p class="text-gray-400 text-sm">Bookmarked {{ $casino->last_played_at->diffForHumans() }}</p>
                    </div>
    
                    <!-- Hover Play Button -->
                    <div class="absolute inset-0 flex justify-center items-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition gap-2">
                        {{-- Play button --}}
                        <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}" 
                        target="_blank" 
                        class="px-4 py-2 bg-green-500 text-black rounded-full text-sm font-medium opacity-0 group-hover:opacity-100 transition">
                        Play
                        </a>

                        {{-- Demo button --}}
                        <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}" 
                        target="_blank" 
                        class="px-4 py-2 bg-yellow-500 text-black rounded-full text-sm font-medium opacity-0 group-hover:opacity-100 transition">
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

    
    
    <h1 class="text-3xl font-bold mb-6 text-white border-l-4 border-green-500 pl-3">Free Games</h1>
    
    @if($bookmarkedGames->count() > 0)
        <div class="games-grid">
            @foreach($bookmarkedGames as $bookmark)
                <div class="game-card" id="bookmark-{{ $bookmark->id }}">
                    <a href="{{ route('games.open', ['id' => $bookmark->game->id]) }}">
                        <img src="{{ asset($bookmark->game->image) }}" alt="{{ $bookmark->game->name }}" class="game-image">
                        <div class="play-btn">Play</div>
                    </a>
                    <div class="game-info">
                        <div class="game-name">{{ $bookmark->game->name }}</div>
                        <button class="bookmark-btn" onclick="removeBookmark({{ $bookmark->id }})" title="Remove bookmark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
                            </svg>
                        </button>
                        <div class="bookmark-date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            Bookmarked {{ $bookmark->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="pagination">
            {{ $bookmarkedGames->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/>
                </svg>
            </div>
            <h3 class="empty-text">You haven't bookmarked any games yet</h3>
            <a href="{{ route('games.viewIndex') }}" class="explore-btn">Explore Games</a>
        </div>
    @endif
</div>

<script>
    function removeBookmark(bookmarkId) {
        fetch(`{{ url('user/games/bookmarks') }}/${bookmarkId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not OK');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const bookmarkElement = document.getElementById(`bookmark-${bookmarkId}`);
                if (bookmarkElement) {
                    bookmarkElement.style.opacity = '0';
                    setTimeout(() => {
                        bookmarkElement.remove();
                        if (document.querySelectorAll('.game-card').length === 0) {
                            window.location.reload();
                        }
                    }, 300);
                }
            } else {
                console.error(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

</script>

<script>
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
            // Optionally, you can alert the message
            if(res.message) {
                alert(res.message);
            }
            // Reload the page to reflect changes
            window.location.reload();
        })
        .catch(err => console.error("Error:", err));
    }

    // Favourite buttons
    document.querySelectorAll(".fav-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            let gameId = this.dataset.game;
            postAndReload("{{ route('user.game.toggleFavourite') }}", { game_id: gameId });
        });
    });

    // Bookmark buttons
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