@extends('layouts.app')

@section('content')
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f8f9fa;
    }
    .tag-banner {
        position: relative;
        width: 100%;
        height: 250px;
        background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url("{{ asset('storage/' . $category->image) }}");
        background-size: cover;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin-bottom: 30px;
        overflow: hidden;
    }
    .tag-info {
        max-width: 900px;
        padding: 20px;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
    .tag-info h1 {
        font-size: 36px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .breadcrumb {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        font-size: 16px;
        margin-bottom: 15px;
    }
    .breadcrumb a {
        color: #e0e0e0;
        text-decoration: none;
    }
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    .breadcrumb-separator {
        color: #e0e0e0;
    }
    .tags-container {
        margin: 30px 0;
    }
    .tag-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .tag-info-selected {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .tag-info-selected img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
    }
    .tag-name-selected {
        font-weight: 600;
        font-size: 18px;
    }
    .tag-count-selected {
        color: #6c757d;
        font-size: 14px;
    }
    .view-all {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }
    .view-all:hover {
        text-decoration: underline;
    }
    .games-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        margin-top: 30px;
    }
    .game-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
        color: inherit;
        position: relative;
    }
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .game-card img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }
    .game-info {
        padding: 12px;
        text-align: center;
    }
    .game-name {
        font-weight: 500;
        margin-bottom: 8px;
    }
    .play-btn {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: green;
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        z-index: 2;
    }
    .game-card:hover .play-btn {
        display: block;
    }
    .sort-container {
        text-align: right;
        margin: 20px 0;
    }
    select {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #ced4da;
        background: white;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .tag-banner {
            height: 180px;
        }
        .tag-info h1 {
            font-size: 24px;
        }
        .breadcrumb {
            font-size: 14px;
        }
        .games-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .play-btn {
            display: block;
            background: rgba(0,0,0,0.5);
            font-size: 14px;
            padding: 6px 12px;
        }
        .tag-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
    }

    .back-to-category-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background-color: #f8f9fa;
        color: #0d6efd;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .back-to-category-btn:hover {
        background-color: #e9ecef;
        border-color: #ced4da;
    }
    .back-to-category-btn svg {
        margin-right: 4px;
    }
    
    /* Mobile styles */
    @media (max-width: 768px) {
        .back-to-category-btn {
            padding: 6px 12px;
            font-size: 14px;
        }
    }
</style>

<div class="tag-banner">
    <div class="tag-info">
        <div class="breadcrumb">
            <a href="{{ route('games.viewIndex') }}">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('category.view', $category->id) }}">{{ $category->name }}</a>
            <span class="breadcrumb-separator">/</span>
            <span>{{ $tags->firstWhere('id', $tag_id)->tag_name }}</span>
        </div>
        <h1>{{ $tags->firstWhere('id', $tag_id)->tag_name }} Games</h1>
        <p>{{ $tags->firstWhere('id', $tag_id)->title }} Games</p>
        <p>{{ $tags->firstWhere('id', $tag_id)->subtitle }} Games</p>
    </div>
</div>

<div class="tags-container">
    <div class="tag-row">
        <div class="tag-info-selected">
            <img src="{{ asset('storage/' . $tags->firstWhere('id', $tag_id)->photo) }}" alt="{{ $tags->firstWhere('id', $tag_id)->tag_name }}">
            <span class="tag-name-selected">{{ $tags->firstWhere('id', $tag_id)->tag_name }}</span>
            <span class="tag-count-selected">({{ $games->count() }} games)</span>
        </div>
        <a href="{{ route('category.view', $category->id) }}" class="back-to-category-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Back to Category
        </a>
    </div>
</div>

<div class="games-grid">
    @foreach($games as $game)
        <div class="game-card">
            <!-- Game image -->
            <img src="{{ asset($game->image) }}" alt="{{ $game->name }}">

            <!-- Favourite star -->
            @auth
            <span id="fav-{{ $game->id }}" 
                onclick="toggleFavorite({{ $game->id }})" 
                style="position: absolute; top: 8px; left: 8px; font-size: 20px; cursor: pointer; 
                       color: {{ auth()->user()->hasFavouriteGame($game->id) ? 'yellow' : 'white' }}">
                {{ auth()->user()->hasFavouriteGame($game->id) ? '★' : '☆' }}
            </span>
            @endauth

            <!-- Game info -->
            <div class="game-info">
                <div class="game-name">{{ $game->name }}</div>
            </div>

            <!-- Play/Login button -->
            <div class="play-btn">
                @auth
                    <a href="{{ route('games.open', ['id' => $game->id]) }}" style="color:white; text-decoration:none;">Play</a>
                @else
                    <a href="/login" style="color:white; text-decoration:none;">Login</a>
                @endauth
            </div>
        </div>
    @endforeach
</div>


<div class="mt-4">
    {{ $games->links() }}
</div>


<script>
function toggleFavorite(gameId) {
    const star = document.getElementById('fav-' + gameId);
    const isFav = star.textContent === '★';
    const url = `/user/games/${gameId}/${isFav ? 'unfavorite' : 'favorite'}`;
    const method = isFav ? 'DELETE' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        star.textContent = isFav ? '☆' : '★';
        star.style.color = isFav ? 'white' : 'yellow';
    });
}
</script>


@endsection