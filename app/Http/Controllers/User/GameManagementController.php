<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameOpen;
use App\Models\Favorite;
use App\Models\Game;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\LastPlay;
use Illuminate\Support\Facades\Cache;

class GameManagementController extends Controller
{
    public function lastplay()
    {
        $gameOpens = GameOpen::with('game')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(30);

        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400);
        }

        $casinoGames = [];
        if ($casinoData) {
            $casinoArray = json_decode($casinoData, true);
            $casinoGames = collect($casinoArray['content']['gameList'] ?? []);
            $casinoGames = $casinoGames->map(fn($item) => (object) $item);
        }

        $lastPlays = LastPlay::where('user_id', Auth::id())
            ->where('last_play', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('game_id');

        $games = $lastPlays->flatMap(function ($plays, $gameId) use ($casinoGames) {
            // $plays contains all LastPlay records for this game
            return $plays->map(function ($play) use ($casinoGames) {
                $casinoGame = $casinoGames->firstWhere('id', $play->game_id);

                if (!$casinoGame) {
                    return null; // skip if not in cache
                }

                return (object)[
                    'id' => $play->game_id,
                    'name' => $casinoGame->name ?? 'Unknown',
                    'img' => $casinoGame->img ?? '',
                    'last_played_at' => $play->created_at,
                    'is_favourite' => $play->is_favourite ?? false,
                ];
            });
        })->filter();


        return view('user.game_management.lastplay', [
            'games' => $games,
            'gameOpens' => $gameOpens
        ]);
    }

    public function favorite($id)
    {
        Favorite::firstOrCreate([
            'user_id' => Auth::id(),
            'game_id' => $id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Game added to favorites.'
        ]);
    }

    public function unfavorite($id)
    {
        Favorite::where('user_id', Auth::id())
            ->where('game_id', $id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Game removed from favorites.'
        ]);
    }

    public function viewFavorites()
    {
        $favorites = Favorite::with('game')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(30);

        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400);
        }

        $casinoGames = [];
        if ($casinoData) {
            $casinoArray = json_decode($casinoData, true);
            $casinoGames = collect($casinoArray['content']['gameList'] ?? []);
            $casinoGames = $casinoGames->map(fn($item) => (object) $item);
        }

        $lastPlays = LastPlay::where('user_id', Auth::id())
            ->where('is_favourite', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('game_id');

        $games = $lastPlays->flatMap(function ($plays, $gameId) use ($casinoGames) {
            // $plays contains all LastPlay records for this game
            return $plays->map(function ($play) use ($casinoGames) {
                $casinoGame = $casinoGames->firstWhere('id', $play->game_id);

                if (!$casinoGame) {
                    return null; // skip if not in cache
                }

                return (object)[
                    'id' => $play->game_id,
                    'name' => $casinoGame->name ?? 'Unknown',
                    'img' => $casinoGame->img ?? '',
                    'last_played_at' => $play->created_at,
                    'is_favourite' => $play->is_favourite ?? false,
                ];
            });
        })->filter();

        if (auth::check()) {
            $userId = auth()->id();
            $gameIds = $games->pluck('id')->toArray();;

            $lastPlays = \App\Models\LastPlay::where('user_id', $userId)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');

            foreach ($games as &$game) {
                $game->lastPlay = $lastPlays[$game->id] ?? null;
            }
            unset($game);
        }

        return view('user.game_management.favorites', [
            'games' => $games,
            'favorites' => $favorites
        ]);
    }


    public function viewNewGames()
    {
        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400); // 24h
        }

        $casinoGames = collect();

        if ($casinoData) {
            // Shuffle & cache for 30 minutes
            $casinoGames = Cache::remember('casino_shuffled_games', 30 * 60, function () use ($casinoData) {
                $casinoArray = json_decode($casinoData, true);
                $games = collect($casinoArray['content']['gameList'] ?? [])
                    ->map(fn($item) => (object) $item)
                    ->shuffle();

                $count = $games->count();
                if ($count > 1) {
                    $middleIndex = (int) ($count / 2);
                    $firstPart = $games->slice(0, $middleIndex);
                    $secondPart = $games->slice($middleIndex);

                    $games = $secondPart->concat($firstPart);
                }

                return $games;
            });
        }

        if (Auth::check()) {
            $userId = Auth::id();
            foreach ($casinoGames as $game) {
                $lastPlay = \App\Models\LastPlay::where('user_id', $userId)
                    ->where('game_id', $game->id)
                    ->first();

                $game->lastPlay = $lastPlay;
            }
        }

        $casinoGames = $casinoGames->take(35);

        return view('user.game_management.new_games', [
            'games' => $casinoGames,
        ]);
    }

    public function popularGames()
    {
        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400); 
        }

        $casinoGames = collect();

        if ($casinoData) {
            $favouriteGameIds = LastPlay::where('is_favourite', 1)
                ->pluck('game_id')
                ->unique()
                ->map(fn($id) => (string) $id);

            if ($favouriteGameIds->isNotEmpty()) {
                $casinoArray = json_decode($casinoData, true);
                $allGames = collect($casinoArray['content']['gameList'] ?? [])
                    ->map(fn($item) => (object) $item);

                $casinoGames = $allGames->filter(
                    fn($game) => $favouriteGameIds->contains((string) $game->id)
                );

                foreach ($casinoGames as $game) {
                    $lastPlay = LastPlay::where('game_id', $game->id)
                        ->where('is_favourite', 1)
                        ->latest()
                        ->first();
                    $game->lastPlay = $lastPlay;
                }

                $casinoGames = $casinoGames->unique('id');
            }
        }

        $perPage = 35;
        $currentPage = request()->get('page', 1);
        $casinoGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $casinoGames->forPage($currentPage, $perPage),
            $casinoGames->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $gameIds = Favorite::select('game_id')
            ->distinct()     
            ->pluck('game_id');
            
        $dbTrendingGames = Game::whereIn('id', $gameIds)
            ->orderByRaw("FIELD(id, " . $gameIds->implode(',') . ")")
            ->paginate(12);

        return view('user.game_management.popular', [
            'games' => $casinoGames,
            'dbTrendingGames'  => $dbTrendingGames,
        ]);
    }


    public function trendingGames()
    {
        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400); // 24h
        }

        $casinoGames = collect();

        if ($casinoData) {
            $trendingGameIds = LastPlay::where('last_play', 1)
                ->pluck('game_id')
                ->unique()
                ->map(fn($id) => (string) $id); 

            if ($trendingGameIds->isNotEmpty()) {
                $casinoArray = json_decode($casinoData, true);
                $allGames = collect($casinoArray['content']['gameList'] ?? [])
                    ->map(fn($item) => (object) $item);

                $casinoGames = $allGames->filter(
                    fn($game) => $trendingGameIds->contains((string) $game->id)
                );

                foreach ($casinoGames as $game) {
                    $lastPlay = LastPlay::where('game_id', $game->id)
                        ->where('last_play', 1)
                        ->latest()
                        ->first();
                    $game->lastPlay = $lastPlay;
                }

                $casinoGames = $casinoGames->unique('id');
            }
        }

        $perPage = 35;
        $currentPage = request()->get('page', 1);
        $casinoGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $casinoGames->forPage($currentPage, $perPage),
            $casinoGames->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $dbTrendingGames = Game::select('id', 'name', 'image', 'games_url', 'play_time')
            ->where('play_time', '>', 0)
            ->orderByDesc('play_time')
            ->paginate(35);

        return view('user.game_management.trending', [
            'games' => $casinoGames,
            'dbTrendingGames'  => $dbTrendingGames,
        ]);
    }


    public function recommendedGames()
    {
        // Get cached casino data
        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400); // 24h
        }

        $casinoGames = collect();

        if ($casinoData) {
            // Shuffle & cache for 30 minutes
            $casinoGames = Cache::remember('casino_shuffled_games', 30 * 60, function () use ($casinoData) {
                $casinoArray = json_decode($casinoData, true);
                return collect($casinoArray['content']['gameList'] ?? [])
                    ->map(fn($item) => (object) $item)
                    ->shuffle();
            });
        }

        // Attach last play data for logged-in user
        if (Auth::check()) {
            $userId = Auth::id();
            foreach ($casinoGames as $game) {
                $lastPlay = \App\Models\LastPlay::where('user_id', $userId)
                    ->where('game_id', $game->id)
                    ->first();

                $game->lastPlay = $lastPlay;
            }
        }

        // Take only the first 30 games
        $casinoGames = $casinoGames->take(35);

        return view('user.game_management.recommended', [
            'games' => $casinoGames,
        ]);
    }


    public function bookmarks()
    {
        $user = Auth::user();

        $bookmarkedGames = GameOpen::with('game')
            ->where('user_id', $user->id)
            ->where('bookmark', 1)
            ->orderByDesc('created_at')
            ->paginate(30);

        $casinoData = Cache::get('casino_raw_response');

        if (!$casinoData) {
            $casinoData = $this->getCasinoData();
            Cache::put('casino_raw_response', $casinoData, 86400);
        }

        $casinoGames = [];
        if ($casinoData) {
            $casinoArray = json_decode($casinoData, true);
            $casinoGames = collect($casinoArray['content']['gameList'] ?? []);
            $casinoGames = $casinoGames->map(fn($item) => (object) $item);
        }

        $lastPlays = LastPlay::where('user_id', Auth::id())
            ->where('is_bookmark', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('game_id');

        $games = $lastPlays->flatMap(function ($plays, $gameId) use ($casinoGames) {
            // $plays contains all LastPlay records for this game
            return $plays->map(function ($play) use ($casinoGames) {
                $casinoGame = $casinoGames->firstWhere('id', $play->game_id);

                if (!$casinoGame) {
                    return null; // skip if not in cache
                }

                return (object)[
                    'id' => $play->game_id,
                    'name' => $casinoGame->name ?? 'Unknown',
                    'img' => $casinoGame->img ?? '',
                    'last_played_at' => $play->created_at,
                    'is_favourite' => $play->is_favourite ?? false,
                ];
            });
        })->filter();

        if (auth::check()) {
            $userId = auth()->id();
            $gameIds = $games->pluck('id')->toArray();;

            $lastPlays = \App\Models\LastPlay::where('user_id', $userId)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');

            foreach ($games as &$game) {
                $game->lastPlay = $lastPlays[$game->id] ?? null;
            }
            unset($game);
        }

        return view('user.game_management.bookmarks', [
            'games' => $games,
            'bookmarkedGames' => $bookmarkedGames
        ]);
    }

    public function removeBookmark($bookmarkId)
    {
        $user = Auth::user();

        $bookmark = GameOpen::where('id', $bookmarkId)
            ->where('user_id', $user->id)
            ->first();

        if (!$bookmark) {
            return response()->json(['success' => false, 'message' => 'Bookmark not found'], 404);
        }

        $bookmark->bookmark = 0;
        $bookmark->save();

        return response()->json(['success' => true]);
    }

    private function getCasinoData(): ?string
    {
        return app(\App\Http\Controllers\CasinoController::class)->getCasinoData();
    }
}
