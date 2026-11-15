<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Game;
use App\Models\GameOpen;
use App\Models\Lottary;
use App\Models\LotteryTransaction;
use App\Models\Page;
use App\Models\User;
use App\Models\UserDeposit;
use App\Models\UserWithdrew;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class AdminCmsAnalytics
{
    /**
     * Aggregates actual database records, never inferred analytics or fabricated numbers.
     * Amounts are recorded totals (not recognized revenue).
     *
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $today = CarbonImmutable::today();
        $from = $today->subDays(13);

        $series = [
            'registrations' => $this->dailyCounts(User::query(), $from),
            'plays' => $this->dailyCounts(GameOpen::query(), $from),
            'deposits' => $this->dailyAmounts(UserDeposit::query(), $from),
            'withdrawals' => $this->dailyAmounts(UserWithdrew::query(), $from),
        ];

        $days = [];
        for ($day = $from; $day->lessThanOrEqualTo($today); $day = $day->addDay()) {
            $date = $day->toDateString();
            $days[] = [
                'date' => $date,
                'label' => $day->format('M j'),
                'registrations' => (int) ($series['registrations'][$date] ?? 0),
                'plays' => (int) ($series['plays'][$date] ?? 0),
                'deposits' => round((float) ($series['deposits'][$date] ?? 0), 2),
                'withdrawals' => round((float) ($series['withdrawals'][$date] ?? 0), 2),
            ];
        }

        return [
            'stats' => [
                'users' => User::count(),
                'new_users' => User::where('created_at', '>=', $from->startOfDay())->count(),
                'verified_users' => User::where('is_verified', true)->count(),
                'pending_users' => User::where('is_verified', false)->count(),
                'banned_users' => User::where('is_banned', true)->count(),
                'games' => Game::count(),
                'game_plays' => GameOpen::count(),
                'lotteries' => Lottary::count(),
                'lottery_tickets' => LotteryTransaction::count(),
                'pages' => Page::count(),
                'messages' => Contact::count(),
                'unread_messages' => Contact::where('read', false)->count(),
                'deposits_pending' => UserDeposit::where('status', 'pending')->count(),
                'withdrawals_pending' => UserWithdrew::where('status', 'pending')->count(),
                'deposits_total' => round((float) UserDeposit::sum('amount'), 2),
                'withdrawals_total' => round((float) UserWithdrew::sum('amount'), 2),
            ],
            'daily' => $days,
            'recentUsers' => User::latest('created_at')->limit(7)->get([
                'id', 'name', 'username', 'email', 'is_verified', 'is_banned', 'created_at',
            ]),
            'topGames' => GameOpen::query()
                ->select('game_id')
                ->selectRaw('COUNT(*) AS total_plays')
                ->with('game:id,name')
                ->groupBy('game_id')
                ->orderByDesc('total_plays')
                ->limit(6)
                ->get(),
        ];
    }

    private function dailyCounts(Builder $query, CarbonImmutable $from): array
    {
        return $query->where('created_at', '>=', $from->startOfDay())
            ->selectRaw('DATE(created_at) as report_date, COUNT(*) as total')
            ->groupBy('report_date')
            ->pluck('total', 'report_date')
            ->all();
    }

    private function dailyAmounts(Builder $query, CarbonImmutable $from): array
    {
        return $query->where('created_at', '>=', $from->startOfDay())
            ->selectRaw('DATE(created_at) as report_date, SUM(amount) as total')
            ->groupBy('report_date')
            ->pluck('total', 'report_date')
            ->all();
    }
}
