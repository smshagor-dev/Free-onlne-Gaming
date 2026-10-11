@php
    $isArrayGame = is_array($game);
    $title = $isArrayGame ? ($game['name'] ?? 'Untitled Game') : ($game->name ?? 'Untitled Game');
    $image = $isArrayGame
        ? ($game['image'] ?? asset('default.png'))
        : (($game->image ?? null) ? asset($game->image) : asset('default.png'));
    $categoryLabel = $isArrayGame
        ? ($game['category'] ?? ($fallbackTag ?? 'Games'))
        : ($game->category->name ?? ($fallbackTag ?? 'Games'));
    $secondaryLabel = $isArrayGame ? 'HTML5' : ($fallbackTag ?? 'Free');
    $playUrl = $isArrayGame
        ? route('playable.play', $game['id'])
        : (auth()->check() ? route('games.open', ['id' => $game->id]) : route('login.form'));
    $canFavorite = !$isArrayGame && auth()->check() && method_exists(auth()->user(), 'hasFavouriteGame');
    $isFavorite = $canFavorite ? auth()->user()->hasFavouriteGame($game->id) : false;
@endphp

<article class="fg-game-card">
    <a class="fg-game-media" href="{{ $playUrl }}" aria-label="Play {{ $title }}">
        <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
    </a>

    @if($canFavorite)
        <button class="fg-favorite" type="button" data-game-id="{{ $game->id }}" aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}">
            <i class="{{ $isFavorite ? 'fa-solid' : 'fa-regular' }} fa-star" aria-hidden="true"></i>
        </button>
    @endif

    <div class="fg-game-body">
        <h3><a href="{{ $playUrl }}">{{ $title }}</a></h3>
        <div class="fg-card-row">
            <div class="fg-tags">
                <span>{{ $categoryLabel }}</span>
                <span>{{ $secondaryLabel }}</span>
            </div>
            <a class="fg-card-play" href="{{ $playUrl }}" aria-label="Play {{ $title }}"><i class="fa-solid fa-play" aria-hidden="true"></i></a>
        </div>
    </div>
</article>

@once
    <style>
        .fg-game-card {
            position: relative;
            min-width: 0;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid var(--fg-border);
            background: linear-gradient(180deg, var(--fg-panel-2), var(--fg-panel));
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.18);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .fg-game-card:hover {
            transform: translateY(-4px);
            border-color: rgba(16, 168, 255, 0.38);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.28);
        }

        .fg-game-media {
            display: block;
            aspect-ratio: 16 / 9;
            overflow: hidden;
            background: #0a1723;
        }

        .fg-game-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
        }

        .fg-game-card:hover .fg-game-media img {
            transform: scale(1.06);
        }

        .fg-favorite {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            color: #ffce43;
            background: rgba(4, 13, 22, 0.7);
            cursor: pointer;
            backdrop-filter: blur(10px);
        }

        .fg-game-body {
            position: relative;
            padding: 9px 11px 10px;
        }

        .fg-game-body h3 {
            margin: 0 0 7px;
            color: #fff;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fg-card-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .fg-tags {
            min-width: 0;
            display: flex;
            gap: 5px;
            overflow: hidden;
        }

        .fg-tags span {
            max-width: 86px;
            padding: 4px 9px;
            border-radius: 999px;
            color: #bfd1e4;
            background: rgba(255, 255, 255, 0.08);
            font-size: 12px;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fg-card-play {
            flex: 0 0 auto;
            width: 33px;
            height: 33px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: #fff;
            background: linear-gradient(135deg, #5d97d4, #2567a6);
            box-shadow: 0 8px 18px rgba(16, 108, 196, 0.35);
            font-size: 12px;
        }

        .fg-game-card:focus-within,
        .fg-favorite:focus-visible,
        .fg-card-play:focus-visible,
        .fg-game-body a:focus-visible,
        .fg-game-media:focus-visible {
            outline: 3px solid rgba(16, 168, 255, 0.58);
            outline-offset: 2px;
        }
    </style>

    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.fg-favorite');
            if (!button) return;

            event.preventDefault();
            event.stopPropagation();

            const gameId = button.dataset.gameId;
            const icon = button.querySelector('i');
            const isFavorite = icon.classList.contains('fa-solid');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/user/games/${gameId}/${isFavorite ? 'unfavorite' : 'favorite'}`, {
                method: isFavorite ? 'DELETE' : 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(response => response.json()).then(data => {
                if (!data.success) return;
                icon.classList.toggle('fa-solid', !isFavorite);
                icon.classList.toggle('fa-regular', isFavorite);
                button.setAttribute('aria-label', isFavorite ? 'Add to favorites' : 'Remove from favorites');
            }).catch(() => {});
        });
    </script>
@endonce
