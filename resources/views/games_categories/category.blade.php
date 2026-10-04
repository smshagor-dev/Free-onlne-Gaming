@extends('layouts.app')

@section('content')

<div style="font-family: Arial, sans-serif; background:#0b141d; padding:20px; min-height:100vh; color:white;">

    <!-- Category Banner -->
    <div style="position:relative; width:100%; height:250px; 
                background:linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                url('{{ asset('storage/' . $category->image) }}'); 
                background-size:cover; background-position:center; border-radius:12px; 
                display:flex; align-items:center; justify-content:center; text-align:center; margin-bottom:30px;">
        <div style="color:white; text-shadow:0 2px 4px rgba(0,0,0,0.7);">
            <h1 style="font-size:36px; margin-bottom:10px;">{{ $category->name }}</h1>
            <p style="font-size:16px; margin:0;">{{ $category->title }}</p>
            <p style="font-size:14px; margin:0;">{{ $category->subtitle }}</p>
        </div>
    </div>

    <!-- Search Box -->
    <form method="GET" id="search-form" style="margin-bottom:20px; text-align:center;">
        <input type="text" name="search" placeholder="Search games..." 
               value="{{ $search ?? '' }}" 
               style="padding:10px 12px; width:60%; max-width:400px; border-radius:6px; border:1px solid #444; background:#1c1f26; color:white;">
        <button type="submit" style="padding:10px 16px; background:#0d6efd; color:white; border:none; border-radius:6px;">Search</button>
    </form>
    
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

    <!-- Games Container -->
    <div id="games-container" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:20px;">
        @foreach($games as $game)
            <div class="game-card" style="background:#1c1f26; border-radius:12px; overflow:hidden; border:1px solid #333; position:relative; transition:0.3s;">
                <img src="{{ asset($game->image) }}" alt="{{ $game->name }}" style="width:100%; height:120px; object-fit:cover;">

                @auth
                <span id="fav-{{ $game->id }}" onclick="toggleFavorite({{ $game->id }})"
                      style="position:absolute; top:8px; left:8px; font-size:20px; cursor:pointer; color:{{ auth()->user()->hasFavouriteGame($game->id) ? 'yellow' : 'white' }};">
                    {{ auth()->user()->hasFavouriteGame($game->id) ? '★' : '☆' }}
                </span>
                @endauth

                <div style="padding:12px; text-align:center;">
                    <h3 style="font-size:14px; font-weight:500; margin-bottom:8px; color:white;">{{ $game->name }}</h3>
                </div>

                <div class="play-btn" style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); display:none;">
                    @auth
                    <a href="{{ route('games.open', ['id' => $game->id]) }}" style="background:green; color:white; padding:6px 12px; border-radius:20px; font-weight:500; text-decoration:none;">Play</a>
                    @else
                    <a href="/login" style="background:green; color:white; padding:6px 12px; border-radius:20px; font-weight:500; text-decoration:none;">Login</a>
                    @endauth
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    <div style="margin-top:30px; text-align:center;">
        {{ $games->links('pagination::tailwind') }}
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
</div>

<script>
    // Hover to show Play button
    document.querySelectorAll('.game-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            const btn = card.querySelector('.play-btn');
            if(btn) btn.style.display = 'block';
        });
        card.addEventListener('mouseleave', () => {
            const btn = card.querySelector('.play-btn');
            if(btn) btn.style.display = 'none';
        });
    });

    // Toggle Favorite
    function toggleFavorite(gameId) {
        const star = document.getElementById('fav-' + gameId);
        const isFav = star.textContent === '★';
        const url = `/user/games/${gameId}/${isFav ? 'unfavorite' : 'favorite'}`;
        const method = isFav ? 'DELETE' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            star.textContent = isFav ? '☆' : '★';
            star.style.color = isFav ? 'white' : 'yellow';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
</script>

@endsection
