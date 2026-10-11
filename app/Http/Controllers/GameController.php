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
        $categories = GamesCategory::select('id', 'name', 'image', 'slug')
            ->with(['games' => fn ($query) => $query->select('id', 'name', 'games_url', 'image', 'category_id', 'slug')->latest()->limit(8)])
            ->withCount('games')
            ->get();
        $allPlayableGames = app(\App\Services\EmbeddableGameCatalog::class)->all();

        return view('index', [
            'categories' => $categories,
            'playableGames' => array_slice($allPlayableGames, 0, 30),
            'playableCategories' => collect($allPlayableGames)->pluck('category')->unique()->values(),
            'playableTotal' => count($allPlayableGames),
            'search' => '',
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


}
