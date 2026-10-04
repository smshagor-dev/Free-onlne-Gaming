<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Game;
use App\Models\GameOpen;
use App\Models\GamePoint;
use App\Models\Level;
use App\Models\User;
use App\Models\GamesCategory;
use App\Models\Tag;
use App\Models\Banner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\DepositSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;


class GameController extends Controller
{
    public function index()
    {
        $games = Game::with(['category', 'tag'])->latest()->paginate(10);
        return view('games.index', compact('games'));
    }

    public function create()
    {
        $categories = GamesCategory::all();
        $tags = Tag::all();
        return view('games.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $slug = Str::slug($request->name);
        $count = Game::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'category_id' => 'required|exists:games_categories,id',
            'tag_id'      => 'required|exists:tags,id',
            'name'        => 'required|string|max:255',
            'games_url'   => 'required|url',
            'image'       => 'nullable|url',
            'details'     => 'nullable|string',
        ]);

        Game::create([
            'category_id' => $request->category_id,
            'tag_id'      => $request->tag_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'games_url'   => $request->games_url,
            'image'       => $request->image,
            'details'     => $request->details,
        ]);

        return redirect()->route('admin.games.index')->with('success', 'Game created successfully.');
    }

    public function edit($id)
    {
        $game = Game::findOrFail($id);
        $categories = GamesCategory::all();
        $tags = Tag::all();
        return view('games.edit', compact('game', 'categories', 'tags'));
    }

    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $slug = Str::slug($request->name);
        $count = Game::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'category_id' => 'required|exists:games_categories,id',
            'tag_id'      => 'required|exists:tags,id',
            'name'        => 'required|string|max:255',
            'games_url'   => 'required|url',
            'image'       => 'nullable|url',
            'details'     => 'nullable|string',
        ]);

        $game->update([
            'category_id' => $request->category_id,
            'tag_id'      => $request->tag_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'games_url'   => $request->games_url,
            'image'       => $request->image,
            'details'     => $request->details,
        ]);

        return redirect()->route('admin.games.index')->with('success', 'Game updated successfully.');
    }


    public function destroy($id)
    {
        $game = Game::findOrFail($id);

        if ($game->image && Storage::disk('public')->exists($game->image)) {
            Storage::disk('public')->delete($game->image);
        }

        $game->delete();

        return redirect()->route('admin.games.index')->with('success', 'Game deleted successfully.');
    }


    public function viewIndex(Request $request)
    {
        $casinoData = Cache::get('casino_raw_response');
        
        if (!$casinoData) {
            app()->call('App\Http\Controllers\CasinoController@cacheCasinoData');
            $casinoData = Cache::get('casino_raw_response');
        }


        if (!$casinoData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No cached casino data found. Please refresh first.'
            ], 404);
        }

        $casinoDataArray = json_decode($casinoData, true);

        $games = $casinoDataArray['content']['gameList'] ?? [];

        if (auth::check()) {
            $userId = auth()->id();
            $gameIds = array_column($games, 'id');

            $lastPlays = \App\Models\LastPlay::where('user_id', $userId)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');

            foreach ($games as &$game) {
                $game['lastPlay'] = $lastPlays[$game['id']] ?? null;
            }
            unset($game);
        }

        $category = $request->get('category');
        $search   = $request->get('search');
        $sortBy   = $request->get('sort_by', 'name');
        $order    = $request->get('order', 'asc');
        $perPage  = (int) $request->get('limit', 60);

        $games = array_filter($games, function ($game) use ($category, $search) {
            $matchCategory = true;
            $matchSearch   = true;

            if ($category) {
                $categories = explode(',', $category); // multiple support
                $matchCategory = isset($game['categories']) && in_array($game['categories'], $categories);
            }

            if ($search) {
                $matchSearch = isset($game['name']) && stripos($game['name'], $search) !== false;
            }

            return $matchCategory && $matchSearch;
        });

        $games = collect($games);

        $games = $games->sortBy(function ($game) use ($sortBy) {
            return $game[$sortBy] ?? null;
        }, SORT_REGULAR, strtolower($order) === 'desc')->values();

        $perPage     = $perPage > 0 ? $perPage : 60;
        $currentPage = $request->get('page', 1);
        $pagedData   = $games->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $games->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = $games->pluck('categories')->filter()->unique()->values();

        $categoryMap = [
            'Slots' => ['slots'],
            'Live Casino' => ['live_dealers', 'roulette'],
            'Crash Games' => ['crash_games'],
            'Arcade' => ['arcade'],
            'Card' => ['card', 'video_poker', 'table_games'],
            'Fast Games' => ['fast_games', 'lottery'],
        ];

        $groupedCategories = collect($categoryMap)->map(function ($aliases, $mainCategory) use ($games) {
            return [
                'aliases' => $aliases,
                'games'   => $games->filter(function ($game) use ($aliases) {
                    return in_array($game['categories'], $aliases);
                })
            ];
        });


        $categories = GamesCategory::select('id', 'name', 'image', 'slug')->get();

        $categories->map(function ($category) {
            $category->games = Game::select('id', 'name', 'games_url', 'image', 'category_id', 'slug')
                ->where('category_id', $category->id)
                ->inRandomOrder()
                ->get();

            return $category;
        });

        $totalGames = $games->count();

        $banners = Banner::latest()->get();

        return view('index', [
            'games'            => $paginatedGames,
            'allGames'         => $games,
            'totalGames'       => $totalGames,
            'categories'       => $categories,
            'groupedCategories' => $groupedCategories,
            'selectedCategory' => $category,
            'search'           => $search,
            'sortBy'           => $sortBy,
            'banners'            => $banners,
        ]);
    }

    public function viewFreeGames(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $perPage = 50;

        $gamesQuery = Game::with('category')
            ->select('id', 'name', 'games_url', 'image', 'category_id');

        if (!empty($search)) {
            $gamesQuery->where('name', 'like', "%{$search}%");
        }

        // For AJAX requests (infinite scroll)
        if ($request->ajax()) {
            $games = $gamesQuery->paginate($perPage, ['*'], 'page', $page);

            $html = '';
            foreach ($games as $game) {
                $isFavorite = auth()->check() && method_exists(auth()->user(), 'hasFavouriteGame')
                    ? auth()->user()->hasFavouriteGame($game->id)
                    : false;

                $html .= '
            <div class="game-card" id="game-card-' . $game->id . '">
                <div class="game-image">
                    <img src="' . asset($game->image) . '" alt="' . $game->name . '" loading="lazy">';

                if (auth()->check()) {
                    $html .= '<div class="favorite-btn" id="fav-' . $game->id . '" onclick="toggleFavorite(' . $game->id . ')">
                            ' . ($isFavorite ? '★' : '☆') . '
                         </div>';
                }

                $html .= '<div class="game-overlay">
                        <div class="game-name">' . $game->name . '</div>';

                if (auth::check()) {
                    $html .= '<a href="' . route('games.open', ['id' => $game->id]) . '" class="play-btn">Play</a>';
                } else {
                    $html .= '<a href="/login" class="play-btn">Login to Play</a>';
                }

                $html .= '</div>
                </div>
                <div class="game-title">' . $game->name . '</div>
            </div>';
            }

            return response()->json([
                'html' => $html,
                'next_page' => $games->hasMorePages() ? $games->currentPage() + 1 : null,
                'total' => $games->total(),
                'has_more' => $games->hasMorePages()
            ]);
        }

        // Initial page load
        $games = $gamesQuery->paginate($perPage);
        $totalGames = $games->total();
        $userData = collect([
            'points'   => $user?->points ?? 0,
            'level_id' => $user?->level_id ?? null,
        ]);


        return view('freegames', compact('games', 'search', 'totalGames', 'userData'));
    }


    public function viewCategory($category_id, Request $request)
    {
        $search = $request->input('search');

        $category = GamesCategory::select('id', 'name', 'image', 'title', 'subtitle')
            ->findOrFail($category_id);

        $gamesQuery = Game::select('id', 'name', 'games_url', 'image', 'category_id')
            ->where('category_id', $category->id);

        if (!empty($search)) {
            $gamesQuery->where('name', 'like', "%{$search}%");
        }

        // Use standard pagination
        $games = $gamesQuery->paginate(504)->withQueryString();

        return view('games_categories.category', compact('category', 'games', 'search'));
    }

    public function viewTag($category_id, $tag_id)
    {
        $category = GamesCategory::findOrFail($category_id);
        $tag = Tag::where('category_id', $category->id)->findOrFail($tag_id);
        $games = Game::where('category_id', $category->id)
            ->where('tag_id', $tag->id)
            ->paginate(50);
        $tags = collect([$tag]);

        return view('tags.tag', compact('category', 'tag', 'tags', 'tag_id', 'games'));
    }

    public function open($id)
    {
        $game = Game::findOrFail($id);
        $user = auth::user();

        $pointsToAdd = GamePoint::value('points') ?? 0;

        $gameOpen = GameOpen::firstOrCreate(
            ['user_id' => $user->id, 'game_id' => $game->id],
            ['points' => 0]
        );

        $gameOpen->increment('points', $pointsToAdd);

        $user->points += $pointsToAdd;
        $user->available_points += $pointsToAdd;
        $user->save();

        $level = Level::where('points', '<=', $user->points)
            ->orderBy('points', 'desc')
            ->first();

        if ($level) {
            if ($user->level_id !== $level->id) {
                $user->level_id = $level->id;
            }
        } else {
            $user->level_id = null;
        }

        // Save user points and level
        $user->save();

        $game->increment('play_time');

        $ratingData = GameOpen::where('game_id', $game->id)
            ->selectRaw('AVG(review) as avg_rating, COUNT(review) as total_reviews, SUM(`like`) as total_likes, SUM(`dislike`) as total_dislikes')
            ->first();


        $avgRating = round($ratingData->avg_rating ?? 0, 1);
        $totalReviews = $ratingData->total_reviews ?? 0;
        $totalLikes = $ratingData->total_likes ?? 0;
        $totalDislikes = $ratingData->total_dislikes ?? 0;

        $comments = GameOpen::with('user')
            ->where('game_id', $game->id)
            ->whereNotNull('comments')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('games.open', compact('game', 'avgRating', 'totalReviews', 'totalLikes', 'totalDislikes', 'comments'));
    }

    // public function open($slug)
    // {
    //     $game = Game::where('slug', $slug)->firstOrFail();
    //     $user = auth::user();

    //     $pointsToAdd = GamePoint::value('points') ?? 0;

    //     $gameOpen = GameOpen::firstOrCreate(
    //         ['user_id' => $user->id, 'game_id' => $game->id],
    //         ['points' => 0]
    //     );

    //     $gameOpen->increment('points', $pointsToAdd);

    //     $user->points += $pointsToAdd;
    //     $user->available_points += $pointsToAdd;
    //     $user->save();

    //     $level = Level::where('points', '<=', $user->points)
    //         ->orderBy('points', 'desc')
    //         ->first();

    //     $user->level_id = $level ? $level->id : null;
    //     $user->save();

    //     $game->increment('play_time');

    //     $ratingData = GameOpen::where('game_id', $game->id)
    //         ->selectRaw('AVG(review) as avg_rating, COUNT(review) as total_reviews, SUM(`like`) as total_likes, SUM(`dislike`) as total_dislikes')
    //         ->first();

    //     $avgRating = round($ratingData->avg_rating ?? 0, 1);
    //     $totalReviews = $ratingData->total_reviews ?? 0;
    //     $totalLikes = $ratingData->total_likes ?? 0;
    //     $totalDislikes = $ratingData->total_dislikes ?? 0;

    //     $comments = GameOpen::with('user')
    //         ->where('game_id', $game->id)
    //         ->whereNotNull('comments')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('games.open', compact(
    //         'game',
    //         'avgRating',
    //         'totalReviews',
    //         'totalLikes',
    //         'totalDislikes',
    //         'comments'
    //     ));
    // }




    public function postGameOpen(Request $request, $gameId)
    {
        $user = auth::user();

        // Validate incoming data
        $validated = $request->validate([
            'review'   => 'nullable|integer|min:0',
            'comments' => 'nullable|string|max:1000',
            'like'     => 'nullable|boolean',
            'dislike'  => 'nullable|boolean',
            'bookmark' => 'nullable|boolean',
            'report'   => 'nullable|in:Bug,Legal,Harmful',
        ]);

        // Find or create record for this user + game
        $gameOpen = GameOpen::firstOrCreate(
            ['user_id' => $user->id, 'game_id' => $gameId],
            []
        );

        // Update with new data
        $gameOpen->update([
            'review'   => $validated['review']   ?? $gameOpen->review,
            'comments' => $validated['comments'] ?? $gameOpen->comments,
            'like'     => $validated['like']     ?? $gameOpen->like,
            'dislike'  => $validated['dislike']  ?? $gameOpen->dislike,
            'bookmark' => $validated['bookmark'] ?? $gameOpen->bookmark,
            'report'   => $validated['report']   ?? $gameOpen->report,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Game data updated successfully',
            'data'    => $gameOpen,
        ]);
    }

    public function dashboard()
    {
        $user = auth::user();

        // Only for first login show popup
        $showPopup = $user->last_login_at === null;

        // Get all deposit settings for Welcome & First Deposit Bonus
        $bonusSettings = DepositSetting::whereIn('bonus_type', ['Welcome Bonus', 'First Deposit Bonus'])->get();

        return view('index', compact('bonusSettings', 'showPopup'));
    }
}
