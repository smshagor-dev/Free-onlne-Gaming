@extends('layouts.app')

@section('content')
<style>
    /* Common styles for both desktop and mobile */
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #0f172a;
        color: white;
        line-height: 1.6;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 15px;
    }

    /* Points System Section */
    .points-system {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        position: relative;
        overflow: hidden;
    }

    .points-system::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
        border-radius: 8px 8px 0 0;
    }

    .points-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .points-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .points-stats {
        display: flex;
        gap: 20px;
    }

    .stat-box {
        background: rgba(30, 41, 59, 0.7);
        padding: 12px 20px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 14px;
        color: #94a3b8;
    }

    /* Search and count section */
    .search-count-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
    }

    .games-count {
        background: #1e293b;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
    }

    .search-container {
        display: flex;
        gap: 10px;
        width: 100%;
        max-width: 500px;
    }

    .search-input, .search-button {
        padding: 12px 16px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
    }

    .search-input {
        flex: 1;
        background: #1e293b;
        color: white;
        min-width: 0;
    }

    .search-input::placeholder {
        color: #94a3b8;
    }

    .search-button {
        background: #3b82f6;
        color: white;
        cursor: pointer;
        font-weight: 500;
        white-space: nowrap;
        transition: background 0.2s;
    }

    .search-button:hover {
        background: #2563eb;
    }

    /* Games grid */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    @media (min-width: 640px) {
        .games-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 768px) {
        .games-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
    }

    @media (min-width: 1024px) {
        .games-grid {
            grid-template-columns: repeat(5, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .games-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    @media (min-width: 1536px) {
        .games-grid {
            grid-template-columns: repeat(8, 1fr);
        }
    }

    /* Game card */
    .game-card {
        background: #1e293b;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
        cursor: pointer;
    }

    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }

    .game-image {
        position: relative;
        width: 100%;
        aspect-ratio: 1/1;
        overflow: hidden;
    }

    .game-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .game-card:hover .game-image img {
        transform: scale(1.05);
    }

    .favorite-btn {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 10;
        font-size: 20px;
        cursor: pointer;
        background: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .favorite-btn:hover {
        background: rgba(0, 0, 0, 0.7);
    }

    .game-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }

    .game-card:hover .game-overlay,
    .game-card.active .game-overlay {
        opacity: 1;
        pointer-events: auto;
    }

    .game-name {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 12px;
        text-align: center;
        padding: 0 10px;
    }

    .play-btn {
        padding: 8px 16px;
        background: #22c55e;
        color: white;
        border: none;
        border-radius: 20px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
        pointer-events: auto;
    }

    .play-btn:hover {
        background: #16a34a;
    }

    .game-title {
        padding: 10px;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Loading indicator */
    .loading-indicator {
        text-align: center;
        padding: 30px;
        color: #94a3b8;
        display: none;
    }

    .loading-indicator.active {
        display: block;
    }

    .loading-spinner {
        border: 3px solid #334155;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* No more games message */
    .no-more-games {
        text-align: center;
        padding: 20px;
        color: #94a3b8;
        display: none;
    }

    /* Notification style */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        background: #10b981;
        color: white;
        border-radius: 8px;
        z-index: 1000;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: opacity 0.5s;
    }

    /* No results styling */
    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px 20px;
    }
    
    .no-results h3 {
        font-size: 24px;
        margin-bottom: 10px;
        color: #e2e8f0;
    }
    
    .no-results p {
        font-size: 16px;
        color: #94a3b8;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .game-card {
            transform: none !important;
            box-shadow: none !important;
        }
        
        .game-card .game-image img {
            transform: none !important;
        }
        
        .game-card.active {
            box-shadow: 0 0 0 2px #3b82f6;
        }

        .points-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
        
        .points-stats {
            width: 100%;
            justify-content: space-between;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .search-count-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .games-count {
            text-align: center;
            order: 2;
        }
        
        .search-container {
            order: 1;
            max-width: 100%;
        }
    }
</style>

<!-- Yandex.RTB -->
<script>window.yaContextCb=window.yaContextCb||[]</script>
<script src="https://yandex.ru/ads/system/context.js" async></script>

<!-- Yandex.RTB R-A-17125537-4 -->
<div id="yandex_rtb_R-A-17125537-4"></div>
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-4",
        "renderTo": "yandex_rtb_R-A-17125537-4"
    })
})
</script>

<div class="container">
    <!-- Points system section -->
    <div class="points-system">
        <div class="points-header">
            <h2 class="points-title">Play Games, Earn Points & Level Up!</h2>
            <div class="points-stats">
                <div class="stat-box">
                    <div class="stat-value">{{ $userData->get('points') }}</div>
                    <div class="stat-label">Your Points</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">Level {{ $userData->get('level_id') ?? '0' }}</div>
                    <div class="stat-label">Current Level</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Yandex.RTB -->
    <script>window.yaContextCb=window.yaContextCb||[]</script>
    <script src="https://yandex.ru/ads/system/context.js" async></script>
    
    <!-- Yandex.RTB R-A-17125537-4 -->
    <div id="yandex_rtb_R-A-17125537-4"></div>
    <script>
    window.yaContextCb.push(() => {
        Ya.Context.AdvManager.render({
            "blockId": "R-A-17125537-4",
            "renderTo": "yandex_rtb_R-A-17125537-4"
        })
    })
    </script>

    <!-- Search section -->
    <div class="search-count-container">
        <div class="games-count">Total Games: <span id="total-games">{{ $totalGames }}</span></div>
        
        <form method="GET" action="/free-games" class="search-container" id="search-form">
            <input 
                type="text" 
                name="search" 
                placeholder="Search games..." 
                value="{{ $search }}"
                class="search-input"
                id="search-input"
            >
            <button type="submit" class="search-button">Search</button>
            
            @if(!empty($search))
            <a href="/free-games" class="search-button" style="background: #ef4444;">Clear</a>
            @endif
        </form>
    </div>

    <!-- Games container -->
    <div class="games-grid" id="games-container">
        @if($games->count() > 0)
            @foreach($games as $game)
            <div class="game-card" id="game-card-{{ $game->id }}">
                <div class="game-image">
                    <img src="{{ asset($game->image) }}" alt="{{ $game->name }}">
                    
                    @auth
                    <div class="favorite-btn" id="fav-{{ $game->id }}" onclick="toggleFavorite({{ $game->id }})">
                        {{ auth()->user()->hasFavouriteGame($game->id) ? '★' : '☆' }}
                    </div>
                    @endauth
                    
                    <div class="game-overlay">
                        <div class="game-name">{{ $game->name }}</div>
                        @auth
                        <a href="{{ route('games.open', ['id' => $game->id]) }}" class="play-btn">Play</a>
                        @else
                        <a href="/login" class="play-btn">Login to Play</a>
                        @endauth
                    </div>
                </div>
                <div class="game-title">{{ $game->name }}</div>
            </div>
            @endforeach
        @else
            <div class="no-results">
                <h3>No games found</h3>
                <p>Try a different search term or <a href="/free-games" style="color: #3b82f6;">browse all games</a></p>
            </div>
        @endif
    </div>

    <!-- Loading indicator -->
    <div class="loading-indicator" id="loading-indicator">
        <div class="loading-spinner"></div>
        Loading more games...
    </div>

    <!-- No more games message -->
    <div class="no-more-games" id="no-more-games">
        <p>No more games to load</p>
    </div>
</div>

<!-- Yandex.RTB -->
<script>window.yaContextCb=window.yaContextCb||[]</script>
<script src="https://yandex.ru/ads/system/context.js" async></script>

<!-- Yandex.RTB R-A-17125537-4 -->
<div id="yandex_rtb_R-A-17125537-4"></div>
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-4",
        "renderTo": "yandex_rtb_R-A-17125537-4"
    })
})
</script>

<script>
// Toggle favorite function
function toggleFavorite(gameId) {
    event.stopPropagation();
    
    const star = document.getElementById('fav-' + gameId);
    const isFav = star.innerHTML.trim() === '★';
    const url = `/user/games/${gameId}/${isFav ? 'unfavorite' : 'favorite'}`;
    const method = isFav ? 'DELETE' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            star.innerHTML = isFav ? '☆' : '★';
            showNotification(data.message, true);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Show notification
function showNotification(message, isSuccess = true) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.style.background = isSuccess ? '#10b981' : '#ef4444';
    notification.textContent = message;
    
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// Infinite scroll implementation
document.addEventListener('DOMContentLoaded', function() {
    let nextPage = 2;
    let isLoading = false;
    let hasMore = true;
    const container = document.getElementById('games-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noMoreGames = document.getElementById('no-more-games');
    const totalGamesElement = document.getElementById('total-games');
    
    // Get search parameter
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search') || '';
    
    // Check if we even need infinite scroll
    if ({{ $totalGames }} <= 50) {
        hasMore = false;
        return;
    }

    // Mobile card click handling
    let activeCard = null;
    
    function handleCardClick(event) {
        if (window.innerWidth <= 768) {
            if (this === activeCard) {
                this.classList.remove('active');
                activeCard = null;
                return;
            }

            if (activeCard) {
                activeCard.classList.remove('active');
            }

            this.classList.add('active');
            activeCard = this;
        }
    }
    
    // Add event listeners to initial game cards
    const gameCards = document.querySelectorAll('.game-card');
    gameCards.forEach(card => {
        card.addEventListener('click', handleCardClick);
    });
    
    // Infinite scroll event listener
    window.addEventListener('scroll', checkScrollPosition);
    
    function checkScrollPosition() {
        if (isLoading || !hasMore) return;
        
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        
        // Load more when 500px from the bottom
        if (scrollTop + windowHeight >= documentHeight - 500) {
            loadMoreGames();
        }
    }
    
    // Also check on page load in case content doesn't fill the screen
    setTimeout(checkScrollPosition, 1000);
    
    function loadMoreGames() {
        if (isLoading || !hasMore) return;
        
        isLoading = true;
        loadingIndicator.classList.add('active');
        
        // Create the URL
        let url = `/free-games?page=${nextPage}`;
        if (searchParam) {
            url += `&search=${encodeURIComponent(searchParam)}`;
        }
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.html && data.html.trim() !== '') {
                // Append new games
                container.insertAdjacentHTML('beforeend', data.html);
                
                // Add event listeners to new cards
                const newCards = container.querySelectorAll('.game-card');
                newCards.forEach(card => {
                    if (!card.hasAttribute('data-listener-added')) {
                        card.setAttribute('data-listener-added', 'true');
                        card.addEventListener('click', handleCardClick);
                    }
                });
                
                // Update total count
                if (totalGamesElement && data.total) {
                    totalGamesElement.textContent = data.total;
                }
            }
            
            // Check if more pages available
            if (data.next_page) {
                nextPage = data.next_page;
                
                // Check if we need to load more immediately (if page not filled)
                setTimeout(checkScrollPosition, 100);
            } else {
                hasMore = false;
                noMoreGames.style.display = 'block';
                window.removeEventListener('scroll', checkScrollPosition);
            }
        })
        .catch(error => {
            console.error('Error loading more games:', error);
            hasMore = false;
            noMoreGames.style.display = 'block';
            noMoreGames.innerHTML = '<p>Error loading more games</p>';
            showNotification('Error loading more games', false);
            window.removeEventListener('scroll', checkScrollPosition);
        })
        .finally(() => {
            isLoading = false;
            loadingIndicator.classList.remove('active');
        });
    }
    
    // Handle search form
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Form will submit normally and reload the page
        });
    }
    
    // Handle document clicks for mobile
    document.addEventListener('click', function(event) {
        if (activeCard && !activeCard.contains(event.target)) {
            activeCard.classList.remove('active');
            activeCard = null;
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && activeCard) {
            activeCard.classList.remove('active');
            activeCard = null;
        }
    });
});
</script>
@endsection
