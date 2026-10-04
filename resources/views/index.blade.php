@extends('layouts.app')

@section('content')
<style>
    /* Common styles for both desktop and mobile */
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f8f8f8;
    }

    .banner-container {
        display: flex;
        transition: transform 0.5s ease-in-out;
    }

    .banner-slide {
        flex: 0 0 auto;
        position: relative;
    }

    /* Smooth hover zoom effect */
    .banner-slide img {
        transition: transform 5s ease-out;
    }

    .banner-slide:hover img {
        transform: scale(1.03);
    }

    /* Ensure proper image display */
    .banner-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Desktop styles */
    @media (min-width: 768px) {
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
        }

        .tags-container {
            display: flex;
            overflow-x: auto;
            padding: 15px;
            gap: 12px;
            scroll-behavior: smooth;
            margin-bottom: 20px;
        }

        .tags-container::-webkit-scrollbar {
            height: 8px;
        }

        .tags-container::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        .tag-item {
            flex: 0 0 auto;
            width: 80px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .tag-item:hover {
            transform: scale(1.05);
        }

        .tag-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto;
            display: block;
            border: 2px solid #ddd;
        }

        .tag-name {
            margin-top: 5px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tag-item.active .tag-photo {
            border-color: #4CAF50;
        }

        .category-row {
            display: flex;
            overflow-x: auto;
            padding: 15px;
            gap: 12px;
            scroll-behavior: smooth;
            margin-bottom: 30px;
        }

        .category-row::-webkit-scrollbar {
            height: 8px;
        }

        .category-row::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 4px;
        }

        /* Big Category Card */
        .category-card {
            flex: 0 0 auto;
            width: 250px;
            height: 140px;
            border-radius: 12px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 15px;
            background-size: cover;
            background-position: center;
            position: relative;
            font-weight: bold;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            pointer-events: none;
        }

        .category-card .title {
            font-size: 20px;
            position: relative;
            z-index: 1;
        }

        .category-card .count {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .category-card .explore-btn {
            padding: 6px 12px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            color: black;
            font-size: 14px;
            border-radius: 20px;
            cursor: pointer;
            width: fit-content;
            position: relative;
            z-index: 2;
            text-decoration: none; 
            display: inline-block; 
        }
        .category-card a {
            position: relative;
            z-index: 2; 
        }

        /* Game Thumbnail */
        .game-thumb {
            flex: 0 0 auto;
            width: 150px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .game-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: filter 0.3s;
        }

        .game-thumb:hover img {
            filter: blur(2px);
        }

        .game-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
            text-align: center;
            padding: 10px;
        }

        .game-thumb:hover .game-overlay {
            opacity: 1;
        }

        .game-name {
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .play-button {
            padding: 5px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
        }
    }

    /* Mobile styles */
    @media (max-width: 767px) {
        /* ... (keep other mobile styles) ... */

        .mobile-category {
            margin-bottom: 25px;
            padding: 10px;
            background: transparent; /* Remove white background */
            border: none; /* Remove black border */
        }

        .mobile-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Keep center alignment vertically */
            margin-bottom: 10px;
            font-size: 14px;
            color: white;
            text-align: left; /* Add this to align text to left */
        }

        .mobile-category-header a {
            text-decoration: none;
            font-weight: bold;
            color: #4CAF50; /* Match desktop button color */
        }

        .mobile-games-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .mobile-game-card {
            background: rgba(255, 255, 255, 0.1); /* Semi-transparent background */
            border-radius: 10px; /* Rounded corners */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden; /* For rounded corners */
        }

        .mobile-game-image {
            position: relative;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .mobile-game-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mobile-play-btn {
            position: absolute;
            padding: 5px 15px;
            background: #4CAF50; /* Match desktop button color */
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            z-index: 2;
        }

        .mobile-game-name {
            padding: 8px;
            text-align: center;
            font-size: 14px;
            color: white; /* White text */
            background: transparent; /* Remove white background */
        }

        /* Add category icon before name */
        .mobile-category-header::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 24px;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }
    }
    /* Animations - common for both */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container">
    <!-- Banner Section -->
    <div class="banner-section w-full h-[250px] sm:h-[300px] lg:h-[400px] xl:h-[400px] relative overflow-hidden group">
        <div class="banner-container flex h-full w-full" style="width: {{ count($banners) * 100 }}%;">
            @foreach($banners as $banner)
            <div class="banner-slide flex-shrink-0 w-full relative rounded-xl overflow-hidden 
                        aspect-square sm:aspect-auto sm:h-full"
                 style="width: {{ 100 / count($banners) }}%;">
                <a href="{{ $banner->link ?? '#' }}" target="_blank" class="block w-full h-full">
                    <img src="{{ asset('storage/' . $banner->image) }}" alt="{{ $banner->title }}" 
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    
                    <!-- Banner Overlay Content -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-4 sm:p-6">
                        <div class="text-white w-full">
                            <h3 class="text-lg sm:text-2xl font-bold mb-2">{{ $banner->title }}</h3>
                            @if($banner->description)
                            <p class="text-xs sm:text-sm opacity-90 mb-3 sm:mb-4">{{ $banner->description }}</p>
                            @endif
                            <button class="w-full sm:w-auto bg-white text-black px-4 sm:px-6 py-2 rounded-full font-medium hover:bg-gray-100 transition">
                                Explore Now
                            </button>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <!-- Navigation Arrows - Only show if multiple banners -->
        @if(count($banners) > 1)
        <button class="banner-prev left-2 sm:left-4 absolute top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 text-white p-2 sm:p-3 rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button class="banner-next right-2 sm:right-4 absolute top-1/2 -translate-y-1/2 bg-white/30 hover:bg-white/50 text-white p-2 sm:p-3 rounded-full backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Dots Indicator -->
        <div class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
            @foreach($banners as $index => $banner)
            <button class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full transition dot-indicator {{ $index === 0 ? 'bg-white/80' : 'bg-white/30' }}" data-index="{{ $index }}"></button>
            @endforeach
        </div>
        @endif
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

<div class="flex items-center justify-between bg-[#1e293b] px-6 py-4 rounded-xl shadow-md mb-6">
    <h2 class="text-lg font-medium text-white">Free Games - {{ $categories->sum(fn($cat) => $cat->games->count()) }}</h2>
    <span class="text-sm font-semibold text-gray-300">
        <a href="/free-games" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
            View All
        </a>
    </span>
</div>

<!-- Desktop Categories and Games Sections -->
<div class="desktop-view">
        @foreach($categories as $category)
        <div class="category-row">
            <div class="category-card" style="background-image: url('{{ asset('storage/' . $category->image) }}');">
                <div class="title">{{ $category->name }}</div>
                <div class="count">{{ $category->games->count() }} games</div>
                <a href="{{ route('category.view', ['id' => $category->id]) }}" class="explore-btn">Explore</a>
            </div>

            @foreach($category->games->take(8) as $game)
            <div class="game-thumb-wrapper" style="flex: 0 0 auto; width: 150px;">
                
                <div class="game-thumb relative group">
                    <img src="{{ asset($game->image) }}" alt="{{ $game->name }}" class="w-full h-auto">

                    <div style="position: absolute; top: 8px; left: 8px; z-index: 3;">
                        @auth
                            <span id="fav-{{ $game->id }}" onclick="toggleFavorite({{ $game->id }})" 
                                style="cursor: pointer; font-size: 20px; color: {{ auth()->user()->hasFavouriteGame($game->id) ? 'yellow' : 'white' }}">
                                {{ auth()->user()->hasFavouriteGame($game->id) ? '★' : '☆' }}
                            </span>
                        @endauth
                    </div>

                    <div class="game-overlay absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <div class="game-name text-white text-lg font-bold mb-2">{{ $game->name }}</div>

                        @auth
                        <a href="{{ route('games.open', ['id' => $game->id]) }}" class="play-button bg-yellow-500 text-black px-4 py-2 rounded inline-block">
                            Play
                        </a>
                        @else
                        <a href="/login" class="play-button bg-yellow-500 text-black px-4 py-2 rounded inline-block">
                            Login to play
                        </a>
                        @endauth
                    </div>
                </div>

                <!-- Always visible game name -->
                <div class="always-visible-name" style="text-align: center; font-size: 13px; font-weight: bold; color: white; margin-top: 5px;">
                    {{ $game->name }}
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    <!-- Mobile Categories and Games Sections -->
    <div class="mobile-view">
        @foreach($categories as $category)
        <div class="mobile-category">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; flex-grow: 1; overflow: hidden;">
                    <img src="{{ asset('storage/' . $category->image) }}" 
                        alt="{{ $category->name }}" 
                        style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: white; font-size: 14px;">
                        {{ $category->name }} ({{ $category->games->count() }})
                    </span>
                </div>
                <a href="{{ route('category.view', ['id' => $category->id]) }}" style="color: #4CAF50; font-weight: bold; text-decoration: none; margin-left: 10px; white-space: nowrap;">
                    <u>View All</u>
                </a>
            </div>
            <div class="mobile-games-row">
                @foreach($category->games->take(4) as $game)
                <div class="mobile-game-card relative">
                    <div class="mobile-game-image relative">
                        <img src="{{ asset($game->image) }}" alt="{{ $game->name }}">
                        
                        <!-- Favourite star for mobile -->
                        @auth
                        <span id="fav-{{ $game->id }}" 
                            onclick="toggleFavorite({{ $game->id }})" 
                            style="position: absolute; top: 8px; left: 8px; font-size: 20px; cursor: pointer; 
                                    color: {{ auth()->user()->hasFavouriteGame($game->id) ? 'yellow' : 'white' }}">
                            {{ auth()->user()->hasFavouriteGame($game->id) ? '★' : '☆' }}
                        </span>
                        @endauth

                        @auth
                        <a href="{{ route('games.open', ['id' => $game->id]) }}" class="mobile-play-btn">Play</a>
                        @else
                        <a href="/login" class="mobile-play-btn">Login to Play</a>
                        @endauth
                    </div>
                    <div class="mobile-game-name">{{ $game->name }}</div>
                </div>
                @endforeach
            </div>

        </div>
        @endforeach
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

<div class="flex items-center justify-between bg-[#1e293b] px-6 py-4 rounded-xl shadow-md mb-6">
    <h2 class="text-lg font-medium text-white">Casino Games - {{ $totalGames }}</h2>
    <span class="text-sm font-semibold text-gray-300">
        <a href="/casino" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
            View All
        </a>
    </span>
</div>

<div class="container mx-auto px-4 py-8 hidden sm:block">
    {{-- 2 categories per row on desktop, 1 on mobile --}}
    @foreach($groupedCategories->chunk(2) as $categoryPair)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">

            @foreach($categoryPair as $mainCategory => $data)
                @php
                    $aliases = $data['aliases'];
                    $categoryGamesAll = $data['games'];
                    $aliasString = implode(',', $aliases); // support multiple
                @endphp

                <div class="hidden sm:block">
                    {{-- Category header --}}
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            {{ $mainCategory }}
                            <span class="text-sm text-gray-400">
                                ({{ $categoryGamesAll->count() }})
                            </span>
                        </h2>

                        <a href="{{ url('/casino?category='.$aliasString) }}" 
                           class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                            View All
                        </a>
                    </div>

                    {{-- Show 8 games --}}
                    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                        @foreach($categoryGamesAll->take(8) as $game)
                            <div class="relative bg-gray-800 rounded-xl shadow hover:shadow-lg overflow-hidden group">

                                {{-- Game image --}}
                                <img src="{{ $game['img'] ?? 'https://via.placeholder.com/200x150' }}" 
                                     alt="{{ $game['name'] }}" 
                                     class="w-full h-40 object-cover">

                                {{-- Favourite & Bookmark --}}
                                @auth
                                    <button class="absolute top-2 left-2 text-xl fav-btn z-20" data-game="{{ $game['id'] }}">
                                        <i class="fas fa-star {{ isset($game['lastPlay']) && $game['lastPlay'] && $game['lastPlay']->is_favourite ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                                    </button>
                                    <button class="absolute top-2 right-2 text-xl bookmark-btn z-20" data-game="{{ $game['id'] }}">
                                        <i class="fas fa-bookmark {{ isset($game['lastPlay']) && $game['lastPlay'] && $game['lastPlay']->is_bookmark ? 'text-green-400' : 'text-gray-400' }}"></i>
                                    </button>
                                @endauth

                                {{-- Hover overlay --}}
                                <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-center items-center p-2">
                                    <h3 class="text-sm font-bold text-white text-center mb-2">{{ $game['name'] }}</h3>

                                    @auth
                                        <div class="flex gap-2">
                                            <a href="{{ route('casino.play', ['gameId' => $game['id'], 'name' => $game['name'], 'demo' => 0]) }}">
                                                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Play</button>
                                            </a>
                                            <a href="{{ route('casino.play', ['gameId' => $game['id'], 'name' => $game['name'], 'demo' => 1]) }}">
                                                <button type="button" class="bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded-md text-sm font-medium transition-colors">Demo</button>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('login') }}">
                                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Login to Play</button>
                                        </a>
                                    @endauth
                                </div>

                                {{-- Game footer --}}
                                <div class="p-3 text-center">
                                    <h3 class="text-sm font-semibold text-white truncate">{{ $game['name'] }}</h3>
                                    <h3 class="text-sm font-semibold text-yellow-400 truncate flex items-center justify-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"/>
                                        </svg>
                                        {{ str_replace(['_', '-'], ' ', ucwords($game['categories'])) }}
                                    </h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</div>

<!-- Yandex.RTB -->
<script>window.yaContextCb=window.yaContextCb||[]</script>
<script src="https://yandex.ru/ads/system/context.js" async></script>

<!-- Yandex.RTB R-A-17125537-1 -->
<div id="yandex_rtb_R-A-17125537-1"></div>
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-1",
        "renderTo": "yandex_rtb_R-A-17125537-1"
    })
})
</script>


<div class="container mx-auto px-4 py-8 block md:hidden">

    {{-- 2 categories per row --}}
    @foreach($groupedCategories->chunk(2) as $categoryPair)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">

            @foreach($categoryPair as $mainCategory => $data)
                @php
                    $aliases = $data['aliases'];
                    $categoryGamesAll = $data['games'];
                    $aliasString = implode(',', $aliases); // support multiple
                @endphp

                <div>
                    {{-- Category header --}}
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            {{ $mainCategory }}
                            <span class="text-sm text-gray-400">
                                ({{ $categoryGamesAll->count() }})
                            </span>
                        </h2>

                    <a href="{{ url('/casino?category='.$aliasString) }}" 
                       class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                        View All
                    </a>
                    </div>

                    {{-- Show 8 games --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        @foreach($categoryGamesAll->take(8) as $game)
                            <div class="relative bg-gray-800 rounded-xl shadow hover:shadow-lg overflow-hidden game-card">

                                {{-- Game image --}}
                                <img src="{{ $game['img'] ?? 'https://via.placeholder.com/200x150' }}" 
                                     alt="{{ $game['name'] }}" 
                                     class="w-full h-40 object-cover select-none">

                                {{-- Favourite & Bookmark (only for logged in) --}}
                                @auth
                                    <button 
                                        class="absolute top-2 left-2 text-xl fav-btn z-20"
                                        data-game="{{ $game['id'] }}">
                                        <i class="fas fa-star {{ isset($game['lastPlay']) && $game['lastPlay'] && $game['lastPlay']->is_favourite ? 'text-yellow-400' : 'text-gray-400' }}"></i>
                                    </button>
                                
                                    <button 
                                        class="absolute top-2 right-2 text-xl bookmark-btn z-20"
                                        data-game="{{ $game['id'] }}">
                                        <i class="fas fa-bookmark {{ isset($game['lastPlay']) && $game['lastPlay'] && $game['lastPlay']->is_bookmark ? 'text-green-400' : 'text-gray-400' }}"></i>
                                    </button>
                                @endauth

                                {{-- Tap overlay (hidden by default; JS toggles classes) --}}
                                <div class="absolute inset-0 z-10 bg-black/70 opacity-0 pointer-events-none transition-opacity duration-300 flex flex-col justify-center items-center p-2 game-overlay">
                                    <h3 class="text-sm font-bold text-white text-center mb-2">{{ $game['name'] }}</h3>

                                    @auth
                                        <div class="flex gap-2">
                                            {{-- Play button --}}
                                            <a href="{{ route('casino.play', ['gameId' => $game['id'],'name' => $game['name'],'demo' => 0]) }}" class="overlay-action">
                                                <button type="button"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                                    Play
                                                </button>
                                            </a>

                                            {{-- Demo button --}}
                                            <a href="{{ route('casino.play', ['gameId' => $game['id'],'name' => $game['name'],'demo' => 1]) }}" class="overlay-action">
                                                <button type="button"
                                                    class="bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                                    Demo
                                                </button>
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ route('login') }}" class="overlay-action">
                                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                                Login to Play
                                            </button>
                                        </a>
                                    @endauth
                                </div>

                                {{-- Game footer --}}
                                <div class="p-3 text-center">
                                    <h3 class="text-sm font-semibold text-white truncate">
                                        {{ $game['name'] }}
                                    </h3>

                                    <h3 class="text-sm font-semibold text-yellow-400 truncate flex items-center justify-center gap-1">
                                        {{-- Star icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" 
                                            class="h-4 w-4 text-yellow-400" 
                                            fill="currentColor" 
                                            viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.455a1 1 0 00-1.175 0l-3.38 2.455c-.784.57-1.838-.196-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"/>
                                        </svg>
                                        {{ str_replace(['_', '-'], ' ', ucwords($game['categories'])) }}
                                    </h3>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>


{{-- Mobile tap JS: robust, no width check, works with touch + click, closes on outside tap --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const gameCards = document.querySelectorAll('.game-card');

    function closeAll(except = null) {
        document.querySelectorAll('.game-overlay.opacity-100').forEach(overlay => {
            if (overlay !== except) {
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                overlay.classList.add('opacity-0', 'pointer-events-none');
            }
        });
    }

    gameCards.forEach(card => {
        const overlay = card.querySelector('.game-overlay');

        card.addEventListener('click', e => {
            // Don't toggle if clicking buttons/icons inside overlay
            if (e.target.closest('.overlay-action, .fav-btn, .bookmark-btn')) return;

            const isOpen = overlay.classList.contains('opacity-100');
            if (isOpen) {
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                overlay.classList.add('opacity-0', 'pointer-events-none');
            } else {
                closeAll(overlay);
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
            }
        });
    });

    // Close overlay when clicking outside any card
    document.addEventListener('click', e => {
        if (!e.target.closest('.game-card')) {
            closeAll();
        }
    });
});

</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".casino-card").forEach(card => {
        card.addEventListener("click", () => {
            const overlay = card.querySelector("div.absolute");
            if (overlay.classList.contains("opacity-0")) {
                overlay.classList.remove("opacity-0", "pointer-events-none");
                overlay.classList.add("opacity-100");
            } else {
                overlay.classList.add("opacity-0", "pointer-events-none");
                overlay.classList.remove("opacity-100");
            }
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    function postShowThenReload(url, data) {
        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            
            const container = document.createElement('div');
            container.className = `bg-${data.success ? 'green' : 'red'}-500 text-white px-4 py-3 rounded-lg shadow flex items-center cursor-pointer transition fixed top-4 right-4 z-50`;
            container.innerHTML = `<span>${data.message}</span>`;
            document.body.appendChild(container);

            setTimeout(() => container.remove(), 5000);
            container.addEventListener('click', () => container.remove());


            setTimeout(() => {
                window.location.reload();
            }, 500);
        })
        .catch(err => console.error("Error:", err));
    }

    // Favourite buttons
    document.querySelectorAll(".fav-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            postShowThenReload("{{ route('user.game.toggleFavourite') }}", { game_id: this.dataset.game });
        });
    });

    // Bookmark buttons
    document.querySelectorAll(".bookmark-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            postShowThenReload("{{ route('user.game.toggleBookmark') }}", { game_id: this.dataset.game });
        });
    });

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bannerSection = document.querySelector('.banner-section');
    const bannerContainer = bannerSection.querySelector('.banner-container');
    const banners = bannerContainer.querySelectorAll('.banner-slide');
    const totalBanners = banners.length;
    
    // Only initialize slider if there are multiple banners
    if (totalBanners > 1) {
        const dots = bannerSection.querySelectorAll('.dot-indicator');
        const prevBtn = bannerSection.querySelector('.banner-prev');
        const nextBtn = bannerSection.querySelector('.banner-next');
        let currentIndex = 0;
        const duration = 5000; // 5 seconds per banner
        let interval;
        
        // Set initial container width
        bannerContainer.style.width = `${totalBanners * 100}%`;
        
        function updateBanner() {
            // Calculate the translateX percentage
            const translateX = -(currentIndex * (100 / totalBanners));
            bannerContainer.style.transform = `translateX(${translateX}%)`;
            
            // Update active dot
            dots.forEach((dot, index) => {
                dot.classList.toggle('bg-white/80', index === currentIndex);
                dot.classList.toggle('bg-white/30', index !== currentIndex);
            });
        }
        
        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalBanners;
            updateBanner();
        }
        
        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalBanners) % totalBanners;
            updateBanner();
        }
        
        function startAutoScroll() {
            clearInterval(interval);
            interval = setInterval(nextSlide, duration);
        }
        
        // Initialize
        updateBanner();
        startAutoScroll();
        
        // Navigation controls
        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            nextSlide();
            startAutoScroll();
        });
        
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            prevSlide();
            startAutoScroll();
        });
        
        // Dot navigation
        dots.forEach((dot) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                currentIndex = parseInt(dot.getAttribute('data-index'));
                updateBanner();
                startAutoScroll();
            });
        });
        
        // Pause on hover
        bannerSection.addEventListener('mouseenter', () => {
            clearInterval(interval);
        });
        
        bannerSection.addEventListener('mouseleave', () => {
            startAutoScroll();
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            bannerContainer.style.transition = 'none';
            updateBanner();
            setTimeout(() => {
                bannerContainer.style.transition = 'transform 0.5s ease-in-out';
            }, 10);
        });
    } else {
        // For single banner, ensure full width
        bannerContainer.style.width = '100%';
        bannerContainer.style.transform = 'translateX(0)';
    }
});
</script>

<script>
    // Show/hide desktop/mobile views based on screen size
    function updateView() {
        if (window.innerWidth < 768) {
            document.querySelector('.desktop-view').style.display = 'none';
            document.querySelector('.mobile-view').style.display = 'block';
        } else {
            document.querySelector('.desktop-view').style.display = 'block';
            document.querySelector('.mobile-view').style.display = 'none';
        }
    }

    // Initial check
    updateView();

    // Update on resize
    window.addEventListener('resize', updateView);

</script>

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