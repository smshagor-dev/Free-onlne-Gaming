<?php

namespace App\Http\Controllers;

use App\Services\CasinoGameCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CasinoGameController extends Controller
{
    /**
     * Public listing of local CMS games, not the excluded provider feed.
     * No game stakes, settlements or real-money features are introduced.
     */
    public function index(Request $request)
    {
        $validated = $request->validate(['search' => ['sometimes', 'nullable', 'string', 'max:80']]);
        $search = trim((string) ($validated['search'] ?? ''));

        $games = CasinoGameCatalog::query()
            ->select(['id', 'name', 'image', 'games_url', 'category_id', 'play_time'])
            ->with('category:id,name')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderBy('name')
            ->paginate(18)->withQueryString();

        return view('casino.games', compact('games', 'search'));
    }

    /**
     * Strictly user-scoped recorded session history. The existing game_name
     * field is not an authenticated foreign key to local games.
     */
    public function activity(Request $request)
    {
        $userId = (int) $request->user()->id;
        $sessions = DB::table('casino_game_sessions')
            ->where('user_id', $userId)
            ->select('game_name')
            ->selectRaw('COUNT(*) AS launches')
            ->groupBy('game_name');

        $ledger = DB::table('transactions as t')
            ->join('casino_game_sessions as s', 's.id', '=', 't.session_id')
            ->where('s.user_id', $userId)
            ->where('t.user_id', $userId)
            ->where('t.status', 'approved')
            ->whereNotNull('t.provider_name')
            ->whereNotNull('t.provider_transaction_id')
            ->whereIn('t.provider_action', ['bet', 'win'])
            ->select('s.game_name')
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'bet' THEN 1 ELSE 0 END) AS bet_count")
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'win' THEN 1 ELSE 0 END) AS win_count")
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'bet' THEN t.amount ELSE 0 END) AS total_bet")
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'win' THEN t.amount ELSE 0 END) AS total_win")
            ->selectRaw('COUNT(*) AS audited_events')
            ->groupBy('s.game_name');

        $games = DB::query()->fromSub($sessions, 'activity')
            ->leftJoinSub($ledger, 'money', fn ($join) => $join->on('activity.game_name', '=', 'money.game_name'))
            ->select('activity.*', 'money.bet_count', 'money.win_count', 'money.total_bet', 'money.total_win', 'money.audited_events')
            ->orderByDesc('launches')->orderBy('game_name')
            ->paginate(20);

        return view('casino.activity', compact('games'));
    }
}
