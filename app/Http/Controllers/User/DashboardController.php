<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CatalogSavedGame;
use App\Models\LotteryTransaction;
use App\Models\LottaryWinner;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->whereHas('game');
        $recent = $user->game_opens()->whereHas('game');

        return view('user.dashboard', [
            'userInfo' => $user,
            'favoriteCount' => (clone $favorites)->count(),
            'libraryCount' => CatalogSavedGame::where('user_id', $user->id)->whereIn('provider', ['freetogame', 'gamerpower'])->count(),
            'playedCount' => (clone $recent)->distinct()->count('game_id'),
            'ticketCount' => LotteryTransaction::where('user_id', $user->id)->count(),
            'winCount' => LottaryWinner::where('user_id', $user->id)->count(),
            'favoriteGames' => $favorites->with('game')->latest()->limit(4)->get(),
            'recentGames' => $recent->with('game')->orderByDesc('updated_at')->limit(20)->get()->unique('game_id')->take(4),
        ]);
    }
}
