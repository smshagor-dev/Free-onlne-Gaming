@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4 md:p-6">
    <!-- Header + Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 md:mb-8">
        <!-- Title with Count -->
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-4 md:mb-0">
            Total Games ({{ $games->total() }})
        </h1>

        <div class="flex flex-col md:flex-row items-start md:items-center gap-4 w-full md:w-auto">

            <!-- Search Input -->
            <input type="search" placeholder="Search game name..."
                x-data x-ref="searchInput"
                x-on:input.debounce.500ms="
                    window.location.href = '{{ route('casino.bonus.index') }}?search=' + $refs.searchInput.value
                    + '{{ $selectedCategory ? '&category=' . $selectedCategory : '' }}'
                    + '{{ $sortBy ? '&sort_by=' . $sortBy : '' }}'
                    + '{{ $order ? '&order=' . $order : '' }}'
                "
                class="w-full md:w-64 px-4 py-2 rounded-lg border border-gray-600 bg-[#2d3748] text-white placeholder-gray-400 focus:outline-none">

            <!-- Category Filter Dropdown -->
            <div class="relative w-full md:w-auto" x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full md:w-auto bg-[#2d3748] text-white px-4 py-2 rounded-lg flex items-center justify-between">
                    <span>{{ $selectedCategory ? ucwords(str_replace('_',' ',$selectedCategory)) : 'Filter by Category' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-full md:w-58 bg-[#1a2938] border border-gray-700 rounded-lg shadow-lg z-10 py-1 max-h-[500px] overflow-y-auto">
                    <a href="{{ route('casino.bonus.index', array_merge(request()->except('category'))) }}"
                        class="block px-4 py-2 {{ !$selectedCategory ? 'bg-blue-600 text-white' : 'text-gray-300' }} hover:bg-blue-500">
                        All Games
                    </a>
                    @foreach($categories as $cat)
                    @php $displayName = ucwords(str_replace('_', ' ', $cat)); @endphp
                    <a href="{{ route('casino.bonus.index', array_merge(request()->query(), ['category' => $cat])) }}"
                        class="block px-4 py-2 {{ $selectedCategory == $cat ? 'bg-blue-600 text-white' : 'text-gray-300' }} hover:bg-blue-500">
                        {{ $displayName }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Sorting Dropdown -->
            <!-- <div>
                <form method="GET" class="flex space-x-2">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="category" value="{{ $selectedCategory }}">

                    <select name="sort_by" class="border px-3 py-2 rounded bg-[#2d3748] text-white">
                        <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="title" {{ $sortBy == 'title' ? 'selected' : '' }}>Provider</option>
                        <option value="categories" {{ $sortBy == 'categories' ? 'selected' : '' }}>Category</option>
                        <option value="device" {{ $sortBy == 'device' ? 'selected' : '' }}>Device</option>
                        <option value="demo" {{ $sortBy == 'demo' ? 'selected' : '' }}>Demo</option>
                    </select>

                    <select name="order" class="border px-3 py-2 rounded bg-[#2d3748] text-white">
                        <option value="asc" {{ $order == 'asc' ? 'selected' : '' }}>Asc</option>
                        <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Desc</option>
                    </select>

                    <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded">Sort</button>
                </form>
            </div> -->
        </div>
    </div>

    <!-- Games Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6" id="gamesGrid">
        @forelse($games as $game)
        <div class="game-card bg-[#1a2938] rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:scale-105 relative group">
            <div class="relative overflow-hidden">
                <img src="{{ $game['img'] ?? '' }}" alt="{{ $game['name'] }}" class="w-full h-40 object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-70 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-center items-center p-2">
                    <h3 class="text-sm font-bold text-white text-center mb-2">{{ $game['name'] }}</h3>
                    @if(auth()->check())
                    <a href="{{ route('casino.bonus.play', [
                                'gameId' => $game['id'],
                                'name'   => $game['name'],
                                'demo'   => 0
                            ]) }}">
                        <button type="button"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Play Now
                        </button>
                    </a>
                    @else
                    <a href="{{ route('login') }}">
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Login to Play
                        </button>
                    </a>
                    @endif
                </div>
            </div>
            <div class="p-3 text-center">
                <span class="text-xs font-semibold text-yellow-400 block mb-1">
                    {{ ucwords(str_replace('_', ' ', $game['categories'] ?? '')) }}
                </span>
                <h3 class="text-sm font-bold text-white truncate">{{ \Illuminate\Support\Str::limit($game['name'], 18) }}</h3>
            </div>
        </div>
        @empty
        <p class="text-gray-400">No games found.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $games->appends(request()->query())->links() }}
    </div>
</div>

<style>
    .pagination {
        display: flex;
        justify-content: center;
        list-style: none;
        padding: 0;
    }

    .pagination li {
        margin: 0 4px;
    }

    .pagination li a,
    .pagination li span {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 4px;
        background-color: #1a2938;
        color: white;
        text-decoration: none;
    }

    .pagination li.active span {
        background-color: #3b82f6;
    }

    .pagination li a:hover {
        background-color: #2d3748;
    }

    .game-card {
        transition: all 0.3s ease;
    }

    .game-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    /* Custom scrollbar for category dropdown */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #2d3748;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: #4a5568;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background: #718096;
    }
</style>
@endsection
