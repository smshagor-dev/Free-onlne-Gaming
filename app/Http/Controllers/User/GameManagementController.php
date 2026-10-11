<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GameOpen;
use App\Models\Favorite;
use App\Models\Game;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GameManagementController extends Controller
{
    public function lastplay()
    {
        $gameOpens = GameOpen::with('game')->whereHas('game')->where('user_id', Auth::id())->latest()->paginate(30);
        return view('user.game_management.lastplay', compact('gameOpens'));
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
        $favorites = Favorite::with('game')->whereHas('game')->where('user_id', Auth::id())->latest()->paginate(30);
        return view('user.game_management.favorites', compact('favorites'));
    }


    public function viewNewGames()
    {
        return redirect()->route('playable.index');
    }

    public function popularGames()
    {
        return redirect()->route('playable.index');
    }


    public function trendingGames()
    {
        return redirect()->route('playable.index');
    }


    public function recommendedGames()
    {
        return redirect()->route('playable.index');
    }


    public function bookmarks()
    {
        $bookmarkedGames = GameOpen::with('game')->whereHas('game')->where('user_id', Auth::id())->where('bookmark', 1)->latest()->paginate(30);
        return view('user.game_management.bookmarks', compact('bookmarkedGames'));
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


}
