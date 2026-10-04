@extends('layouts.app')

@section('content')
<div class="trending-games-container">
    <!-- Header -->
    <div class="games-header">
        <div class="header-content">
            <i class="fas fa-chart-line header-icon"></i>
            <h1 class="header-title">
                Trending Games
                <span class="badge">Top Rated</span>
            </h1>
        </div>
    </div>

    <!-- Casino API Games Grid -->
    <div class="games-grid">
        @foreach($games as $casino)
            <div class="game-card" data-game-id="{{ $casino->id }}">
                <!-- Trending Badge -->
                <div class="trending-badge">
                    Trending #{{ $loop->iteration }}
                </div>
                
                <!-- Game Image -->
                <img src="{{ $casino->img }}" alt="{{ $casino->name }}" class="game-image">
                
                <!-- Overlay (hover/tap) -->
                <div class="game-overlay">
                    <div class="game-title-overlay">{{ $casino->name }}</div>
                    <div class="action-buttons">
                        <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}"
                           class="play-btn">
                           Play
                        </a>
                        <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}"
                           class="demo-btn">
                           Demo
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div class="game-footer">
                    <div class="game-name">{{ $casino->name }}</div>
                    @if(isset($casino->categories))
                        <div class="game-categories">{{ $casino->categories }}</div>
                    @endif
                </div>
                
                <!-- Mobile Play Button (centered on image) -->
                <div class="mobile-play-btn">
                    <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 0]) }}" class="play-now-btn">
                        Play Now
                    </a>
                    <a href="{{ route('casino.play', ['gameId' => $casino->id, 'name' => $casino->name, 'demo' => 1]) }}" class="demo-now-btn">
                        Demo
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Database Trending Games -->
    <div class="database-games-section">
        <div class="games-grid">
            @foreach($dbTrendingGames as $dbGame)
                <div class="game-card db-game-card" data-game-id="{{ $dbGame->id }}">
                    <!-- Rank Badge -->
                    <div class="trending-badge db-badge">
                        Trending #{{ $loop->iteration }}
                    </div>
                    
                    <!-- Game Image -->
                    <img src="{{ $dbGame->image }}" alt="{{ $dbGame->name }}" class="game-image">
                    
                    <!-- Overlay (same as casino games) -->
                    <div class="game-overlay">
                        <div class="game-title-overlay">{{ $dbGame->name }}</div>
                        <div class="action-buttons">
                            <a href="{{ route('games.open', ['id' => $dbGame->id]) }}"
                               class="play-btn">
                               Play
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="game-footer">
                        <div class="game-name">{{ $dbGame->name }}</div>
                        <div class="play-count">Played {{ $dbGame->play_time }} times</div>
                    </div>

                    <!-- Mobile Play Button (centered on image) -->
                    <div class="mobile-play-btn">
                        <a href="{{ route('games.open', ['id' => $dbGame->id]) }}" class="play-now-btn">
                            Play Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $dbTrendingGames->links() }}
        </div>
    </div>
</div>

<style>
    /* Base Styles */
    .trending-games-container {
        width: 100%;
        padding: 20px;
        background-color: #111827;
        box-sizing: border-box;
        min-height: 100vh;
    }
    
    .games-header {
        margin-bottom: 25px;
    }
    
    .header-content {
        display: flex;
        justify-content: start;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .header-icon {
        color: #3b82f6;
        font-size: 24px;
    }
    
    .header-title {
        font-size: 28px;
        font-weight: 600;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    
    .badge {
        background-color: #3b82f6;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    
    /* Games Grid */
    .games-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
        width: 100%;
    }
    
    /* Game Card */
    .game-card {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        background-color: #1f2937;
    }
    
    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .trending-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: #ef4444;
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        z-index: 10;
    }
    
    .db-badge {
        background-color: #f97316;
    }
    
    .game-image {
        width: 100%;
        height: 160px;
        object-fit: cover;
        display: block;
    }
    
    /* Overlay */
    .game-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.6));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        padding: 15px;
        box-sizing: border-box;
        z-index: 15;
    }
    
    .game-card:hover .game-overlay {
        opacity: 1;
    }
    
    .game-title-overlay {
        color: white;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .play-btn, .demo-btn {
        padding: 8px 16px;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .play-btn {
        background-color: #22c55e;
        color: black;
    }
    
    .play-btn:hover {
        background-color: #16a34a;
        transform: scale(1.05);
    }
    
    .demo-btn {
        background-color: #facc15;
        color: black;
    }
    
    .demo-btn:hover {
        background-color: #eab308;
        transform: scale(1.05);
    }
    
    /* Game Footer */
    .game-footer {
        padding: 12px;
        background-color: #1f2937;
        color: white;
        text-align: center;
    }
    
    .game-name {
        font-weight: 600;
        font-size: 14px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-bottom: 4px;
    }
    
    .game-categories, .play-count {
        font-size: 12px;
        color: #d1d5db;
    }
    
    /* Mobile Play Button - Centered on image */
    .mobile-play-btn {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 20;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 0 20px;
        box-sizing: border-box;
    }
    
    .play-now-btn, .demo-now-btn {
        background: #3b82f6;
        color: white;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
        text-align: center;
        width: 100%;
        max-width: 140px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
    
    .demo-now-btn {
        background: #facc15;
        color: black;
    }
    
    .play-now-btn:hover, .demo-now-btn:hover {
        transform: scale(1.05);
    }
    
    .play-now-btn:hover {
        background: #2563eb;
    }
    
    .demo-now-btn:hover {
        background: #eab308;
    }
    
    /* Database Games Section */
    .database-games-section {
        margin-top: 60px;
    }
    
    /* Pagination */
    .pagination-container {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }
    
    /* Mobile Responsive Styles */
    @media (max-width: 768px) {
        .trending-games-container {
            padding: 15px;
        }
        
        .games-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .header-title {
            font-size: 24px;
        }
        
        .game-card {
            cursor: default;
        }
        
        .game-card:hover {
            transform: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .game-overlay {
            display: none;
        }
        
        .mobile-play-btn.active {
            display: flex;
        }
        
        .game-card.active {
            box-shadow: 0 0 0 2px #3b82f6;
        }
        
        .game-card.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 160px;
            background: rgba(0, 0, 0, 0.7);
            z-index: 15;
        }
    }
    
    @media (max-width: 480px) {
        .games-grid {
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
        }
        
        .game-image {
            height: 140px;
        }
        
        .game-card.active::before {
            height: 140px;
        }
        
        .play-now-btn, .demo-now-btn {
            padding: 8px 16px;
            font-size: 13px;
            max-width: 120px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gameCards = document.querySelectorAll('.game-card');
    const isMobile = window.innerWidth <= 768;
    
    // Handle game card interactions
    gameCards.forEach(card => {
        if (isMobile) {
            // Mobile behavior - toggle play button on tap
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking on a link
                if (e.target.tagName === 'A' || e.target.closest('a')) return;
                
                const gameId = this.dataset.gameId;
                const playButton = this.querySelector('.mobile-play-btn');
                
                // Toggle active state for this card
                const isActive = this.classList.contains('active');
                
                // Remove active state from all cards
                gameCards.forEach(c => {
                    c.classList.remove('active');
                    c.querySelector('.mobile-play-btn')?.classList.remove('active');
                });
                
                // If it wasn't active, activate it
                if (!isActive && playButton) {
                    this.classList.add('active');
                    playButton.classList.add('active');
                }
            });
        } else {
            // Desktop behavior - show overlay on hover
            card.addEventListener('mouseenter', function() {
                const overlay = this.querySelector('.game-overlay');
                if (overlay) overlay.style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                const overlay = this.querySelector('.game-overlay');
                if (overlay) overlay.style.opacity = '0';
            });
        }
    });
    
    // Close mobile buttons when clicking outside
    document.addEventListener('click', function(e) {
        if (isMobile && !e.target.closest('.game-card')) {
            gameCards.forEach(card => {
                card.classList.remove('active');
                card.querySelector('.mobile-play-btn')?.classList.remove('active');
            });
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Refresh interactions if crossing the mobile/desktop threshold
            const newIsMobile = window.innerWidth <= 768;
            if (newIsMobile !== isMobile) {
                window.location.reload();
            }
        }, 250);
    });
});
</script>
@endsection