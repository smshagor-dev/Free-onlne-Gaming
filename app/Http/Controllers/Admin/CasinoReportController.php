<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CasinoReportController extends Controller
{
    /**
     * Admin-only, read-only reporting over the EXISTING casino session and
     * transaction tables. No provider callback or wager settlement is added.
     *
     * Sessions = recorded casino launches; unique users = observed players.
     * Gross bet/win totals require an approved, provider-identified ledger
     * entry with an exact bet/win action and matching session owner.
     * Unverified/legacy transactions must not be reported as confirmed bets.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:80'],
            'user_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $userId = $filters['user_id'] ?? null;

        $sessionSummary = DB::table('casino_game_sessions')
            ->select('game_name')
            ->selectRaw('COUNT(*) AS launches, COUNT(DISTINCT user_id) AS players')
            ->groupBy('game_name');

        $playerSessions = DB::table('casino_game_sessions')
            ->select('user_id', 'game_name')
            ->selectRaw('COUNT(*) AS launches')
            ->groupBy('user_id', 'game_name');

        // Build monetary reports independently from sessions to avoid counting
        // one session multiple times when it has many ledger transactions.
        $auditedLedger = DB::table('transactions as t')
            ->join('casino_game_sessions as s', 's.id', '=', 't.session_id')
            ->whereColumn('t.user_id', 's.user_id')
            ->where('t.status', 'approved')
            ->whereNotNull('t.provider_name')
            ->whereNotNull('t.provider_transaction_id')
            ->whereIn('t.provider_action', ['bet', 'win']);

        $gameMoney = (clone $auditedLedger)
            ->select('s.game_name')
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'bet' THEN t.amount ELSE 0 END) AS total_bet")
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'win' THEN t.amount ELSE 0 END) AS total_win")
            ->selectRaw('COUNT(*) AS audited_events')
            ->groupBy('s.game_name');

        $playerMoney = (clone $auditedLedger)
            ->select('s.game_name', 's.user_id')
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'bet' THEN t.amount ELSE 0 END) AS total_bet")
            ->selectRaw("SUM(CASE WHEN t.provider_action = 'win' THEN t.amount ELSE 0 END) AS total_win")
            ->selectRaw('COUNT(*) AS audited_events')
            ->groupBy('s.game_name', 's.user_id');

        $games = DB::query()->fromSub($sessionSummary, 'activity')
            ->leftJoinSub($gameMoney, 'money', fn ($join) => $join->on('activity.game_name', '=', 'money.game_name'))
            ->select(
                'activity.game_name', 'activity.launches', 'activity.players',
                'money.total_bet', 'money.total_win', 'money.audited_events'
            )
            ->when($search !== '', fn ($query) => $query->where('activity.game_name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderByDesc('activity.launches')->orderBy('activity.game_name')
            ->paginate(20, ['*'], 'game_page')->withQueryString();

        $players = DB::query()->fromSub($playerSessions, 'activity')
            ->leftJoin('users as u', 'u.id', '=', 'activity.user_id')
            ->leftJoinSub($playerMoney, 'money', function ($join) {
                $join->on('activity.game_name', '=', 'money.game_name')
                    ->on('activity.user_id', '=', 'money.user_id');
            })
            ->select(
                'activity.user_id', 'activity.game_name', 'activity.launches',
                'u.name', 'u.email', 'money.total_bet', 'money.total_win', 'money.audited_events'
            )
            ->when($search !== '', fn ($query) => $query->where('activity.game_name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->when($userId !== null, fn ($query) => $query->where('activity.user_id', '=', $userId))
            ->orderByDesc('activity.launches')->orderBy('activity.user_id')
            ->paginate(20, ['*'], 'user_page')->withQueryString();

        // Games stored in the separate local Game model are not linked to
        // provider session IDs. Existing play_time counts route opens, not
        // verified completed plays; game_opens is one row per member/game.
        $localGames = Game::query()->with('category:id,name')
            ->withCount('gameOpens')
            ->where(function ($query) {
                $terms = ['casino', 'slot', 'roulette', 'poker', 'blackjack', 'baccarat'];
                foreach ($terms as $term) {
                    $query->orWhere('name', 'like', '%'.$term.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$term.'%'));
                }
            })
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.addcslashes($search, '%_\\').'%'))
            ->orderByDesc('play_time')->orderBy('name')
            ->paginate(10, ['*'], 'local_page')->withQueryString();

        $totals = [
            'sessions' => DB::table('casino_game_sessions')->count(),
            'games' => DB::table('casino_game_sessions')->distinct()->count('game_name'),
            'players' => DB::table('casino_game_sessions')->distinct()->count('user_id'),
        ];

        $moneyTotals = DB::query()->fromSub($gameMoney, 'money')
            ->selectRaw('SUM(money.total_bet) AS total_bet, SUM(money.total_win) AS total_win, SUM(money.audited_events) AS audited_events')
            ->first();

        $totals['audited_events'] = (int) ($moneyTotals->audited_events ?? 0);
        $totals['total_bet'] = $moneyTotals->total_bet ?? null;
        $totals['total_win'] = $moneyTotals->total_win ?? null;

        return view('admin.casino.index', compact('games', 'players', 'localGames', 'totals', 'search', 'userId'));
    }
}
