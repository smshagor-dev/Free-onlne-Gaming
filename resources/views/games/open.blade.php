@extends('layouts.app')

@section('content')


<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4 text-left flex items-center gap-2">
        <img src="{{ $game->image }}" alt="{{ $game->name }}" class="w-10 h-10 rounded-md object-cover">
        {{ $game->name }}
        

        @php
            use App\Models\Favorite;
            $isFav = Favorite::where('user_id', auth()->id())
                        ->where('game_id', $game->id)
                        ->exists();
        @endphp

        <span
            id="fav-{{ $game->id }}"
            onclick="toggleFavorite({{ $game->id }})"
            class="cursor-pointer text-2xl"
            style="color: {{ $isFav ? 'yellow' : 'white' }}"
            title="{{ $isFav ? 'Unfavorite' : 'Add to Favorite' }}"
        >
            {{ $isFav ? '★' : '☆' }}
        </span>
    </h1>
    <div class="flex gap-4 mt-2 text-gray-300">
        <span>👍 Likes: {{ $totalLikes }}</span>
        <span>👎 Dislikes: {{ $totalDislikes }}</span>
    </div>
</br>
    <div id="game-container" class="relative bg-black w-full h-96 sm:h-[80vh] mx-auto rounded-lg shadow-lg overflow-hidden">
        {{-- Exit button --}}
        <button id="exitGame" 
            class="absolute top-2 right-2 bg-red-600 text-white p-2 rounded-full z-50 hover:bg-red-700 transition">
            <!-- X icon -->
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                stroke-width="2">
                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        {{-- Fullscreen button (Left Side) --}}
        <button id="fullscreenGame" 
            class="absolute top-2 left-2 bg-blue-600 text-white p-2 rounded-full z-50 hover:bg-blue-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor" 
                stroke-width="2">
                <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4" />
            </svg>
        </button>

        {{-- Game Iframe --}}

        <iframe 
            id="gameIframe"
            src="{{ $game->games_url }}" 
            scrolling="no"
            class="w-full h-full"
            allowfullscreen
            allow="autoplay; fullscreen">
        </iframe>
    </div>
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

<div class="w-full min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 p-6">
    <div class="max-w-full mx-auto bg-gray-800 bg-opacity-70 backdrop-blur-lg rounded-xl shadow-2xl overflow-hidden border border-gray-700">
        <!-- Header -->
        <div class="flex items-center gap-4 p-6 border-b border-gray-700">
            <img src="{{ $game->image }}" alt="{{ $game->name }}" class="w-16 h-16 rounded-lg object-cover border-2 border-gray-600">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $game->name }}</h1>
                <p class="text-gray-300 text-sm">{{ $game->play_time }} play times</p>
                <p class="text-yellow-400 text-sm">
                    ⭐ {{ $avgRating }} / 10 
                    <span class="text-gray-400">({{ $totalReviews }} reviews)</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-4 p-6 border-b border-gray-700">
            <p>{{ preg_replace('/[^\x20-\x7E]/', '??', $game->details) }}</p>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Review -->
            <div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Rating</label>
                    <div class="flex items-center gap-1" id="star-rating">
                        <!-- Stars will be inserted here -->
                    </div>
                    <div class="text-gray-400 text-sm mt-1">Click to rate (0-10 stars)</div>
                    <input type="hidden" id="review" name="review" value="0">
                </div>

            <!-- Comment -->
            <div>
                <label for="comment" class="block text-sm font-medium text-gray-300 mb-1">Comments</label>
                <textarea id="comment" rows="4"
                    class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-500"
                    placeholder="Share your experience with this game..."></textarea>
            </div>

            <!-- Quick Actions -->
            <div>
                <p class="text-sm font-medium text-gray-300 mb-2">Quick Actions</p>
                <div class="flex flex-wrap gap-3">
                    <button onclick="submitAction('like')" class="flex items-center gap-2 px-5 py-2.5 bg-green-600 bg-opacity-70 hover:bg-opacity-100 text-white rounded-lg transition-all hover:scale-105 shadow-lg">
                        <span class="text-lg">👍</span> Like
                    </button>
                    <button onclick="submitAction('dislike')" class="flex items-center gap-2 px-5 py-2.5 bg-red-600 bg-opacity-70 hover:bg-opacity-100 text-white rounded-lg transition-all hover:scale-105 shadow-lg">
                        <span class="text-lg">👎</span> Dislike
                    </button>
                    <button onclick="submitAction('bookmark')" class="flex items-center gap-2 px-5 py-2.5 bg-yellow-600 bg-opacity-70 hover:bg-opacity-100 text-white rounded-lg transition-all hover:scale-105 shadow-lg">
                        <span class="text-lg">🔖</span> Bookmark
                    </button>
                </div>
            </div>

            <!-- Report -->
            <div>
                <label for="report" class="block text-sm font-medium text-gray-300 mb-1">Report Issue</label>
                <select id="report" class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="" class="bg-gray-800">-- Select an issue --</option>
                    <option value="Bug" class="bg-gray-800">🐞 Bug or Technical Issue</option>
                    <option value="Legal" class="bg-gray-800">⚖️ Legal Concern</option>
                    <option value="Harmful" class="bg-gray-800">⚠️ Harmful Content</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex justify-end pt-2">
                <button onclick="submitGameData()" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all hover:scale-105 shadow-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Submit Review
                </button>
            </div>

            <!-- Response -->
            <div id="response" class="mt-4 text-sm text-gray-300 p-3 rounded bg-gray-700 bg-opacity-50 hidden"></div>
        </div>
    </div>
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

<div class="w-full min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 p-6">
    <div class="max-w-full mx-auto bg-gray-800 bg-opacity-70 backdrop-blur-lg rounded-xl shadow-2xl overflow-hidden border border-gray-700">

        <!-- Comments Section -->
        <div class="p-6">
            <h2 class="text-xl font-semibold text-white mb-6">Player Reviews</h2>
            
            @if($comments->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p>No reviews yet. Be the first to share your thoughts!</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($comments as $comment)
                    <div class="bg-gray-700 bg-opacity-50 rounded-lg p-4 border border-gray-600">
                        <div class="flex items-center gap-3 mb-3">
                            @if($comment->user->photo)
                            <img src="{{ $comment->user->photo ? asset('storage/' . $comment->user->photo) : 'https://via.placeholder.com/50' }}" 
                            alt="{{ $comment->user->name }}" 
                            class="w-10 h-10 rounded-full border border-gray-500 object-cover">

                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-medium">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="font-medium text-white">{{ $comment->user->name }}</h3>
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 10; $i++)
                                        @if($i <= floor($comment->review / 1))
                                            <span class="text-yellow-400">★</span>
                                        @elseif($i == ceil($comment->review / 1) && $comment->review % 1 != 0)
                                            <span class="text-yellow-400">½</span>
                                        @else
                                            <span class="text-gray-500">★</span>
                                        @endif
                                    @endfor
                                    <span class="text-gray-400 text-sm ml-1">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-300">{{ $comment->comments }}</p>
                        
                        @if($comment->likes > 0 || $comment->dislikes > 0)
                        <div class="flex gap-4 mt-3 text-sm text-gray-400">
                            @if($comment->likes > 0)
                                <span>👍 {{ $comment->likes }}</span>
                            @endif
                            @if($comment->dislikes > 0)
                                <span>👎 {{ $comment->dislikes }}</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('star-rating');
    const hiddenInput = document.getElementById('review');
    let currentRating = 0;
    let hoverRating = 0;

    // Create 10 stars
    for (let i = 1; i <= 10; i++) {
        const star = document.createElement('button');
        star.type = 'button';
        star.className = 'text-2xl focus:outline-none transition-transform';
        star.innerHTML = '★';
        star.dataset.value = i;
        
        star.addEventListener('click', function() {
            currentRating = i;
            hiddenInput.value = currentRating;
            updateStars();
        });
        
        star.addEventListener('mouseover', function() {
            hoverRating = i;
            updateStars();
        });
        
        star.addEventListener('mouseleave', function() {
            hoverRating = 0;
            updateStars();
        });
        
        container.appendChild(star);
    }

    function updateStars() {
        const stars = container.querySelectorAll('button');
        stars.forEach(function(star) {
            const value = parseInt(star.dataset.value);
            if ((hoverRating && value <= hoverRating) || (!hoverRating && value <= currentRating)) {
                star.className = 'text-2xl focus:outline-none transition-transform text-yellow-400 hover:scale-125';
            } else {
                star.className = 'text-2xl focus:outline-none transition-transform text-gray-500 hover:text-gray-300';
            }
        });
    }

    // Initialize
    updateStars();
});
</script>

<script>
    function submitAction(type) {
        if (type === 'like') {
            document.getElementById('review').value = "5";
            sendGameData({ like: true });
        } else if (type === 'dislike') {
            document.getElementById('review').value = "1";
            sendGameData({ dislike: true });
        } else if (type === 'bookmark') {
            sendGameData({ bookmark: true });
        }
    }

    function submitGameData() {
        const review = document.getElementById('review').value;
        const comment = document.getElementById('comment').value;
        const report = document.getElementById('report').value;

        let data = {};
        if (review) data.review = review;
        if (comment) data.comments = comment;
        if (report) data.report = report;

        sendGameData(data);
    }

    function sendGameData(data) {
        const responseEl = document.getElementById("response");
        responseEl.classList.remove('hidden');
        responseEl.textContent = "Submitting your feedback...";
        
        fetch("{{ route('user.games.open.post', $game->id) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            responseEl.textContent = res.message;
            responseEl.classList.add('text-green-400');
            setTimeout(() => {
                responseEl.classList.add('hidden');
                responseEl.classList.remove('text-green-400');
            }, 3000);
        })
        .catch(err => {
            responseEl.textContent = "Error saving your data. Please try again.";
            responseEl.classList.add('text-red-400');
        });
    }
</script>
           

<script>
    document.getElementById('exitGame').addEventListener('click', () => {
        window.history.back();
    });

    const container = document.getElementById('game-container');

    document.getElementById('fullscreenGame').addEventListener('click', () => {
        if (!document.fullscreenElement) {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        }
    });
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
        star.textContent = isFav ? '☆' : '★';
        star.style.color = isFav ? 'white' : 'yellow';
        star.title = isFav ? 'Add to Favorite' : 'Unfavorite';
    });
}
</script>


@endsection
