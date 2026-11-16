<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameOpen;
use App\Models\User;
use App\Models\UserLogin;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserActivityController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $from = CarbonImmutable::parse($filters['from'] ?? now()->subDays(13)->toDateString())->startOfDay();
        $to = CarbonImmutable::parse($filters['to'] ?? now()->toDateString())->endOfDay();
        if ($from->greaterThan($to) || $from->diffInDays($to->startOfDay()) > 89 || $to->greaterThan(now()->endOfDay())) {
            throw ValidationException::withMessages(['from' => 'Choose a valid reporting range of 90 days or less, ending no later than today.']);
        }

        $logins = UserLogin::query()
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->whereBetween('created_at', [$from, $to]);
        $plays = GameOpen::query()
            ->when($filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->whereBetween('created_at', [$from, $to]);

        $counts = [
            'logins' => (clone $logins)->count(),
            'plays' => (clone $plays)->count(),
            'members' => (clone $logins)->distinct()->count('user_id'),
        ];

        $loginDays = (clone $logins)->selectRaw('DATE(created_at) AS day, COUNT(*) AS events')
            ->groupBy('day')->pluck('events', 'day')->all();
        $playDays = (clone $plays)->selectRaw('DATE(created_at) AS day, COUNT(*) AS events')
            ->groupBy('day')->pluck('events', 'day')->all();
        $days = [];
        for ($day = $from; $day->lessThanOrEqualTo($to); $day = $day->addDay()) {
            $date = $day->toDateString();
            $days[] = [
                'label' => $day->format('M j'),
                'date' => $date,
                'logins' => (int) ($loginDays[$date] ?? 0),
                'plays' => (int) ($playDays[$date] ?? 0),
            ];
        }

        return view('admin.user-activity.index', [
            'filters' => $filters,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'counts' => $counts,
            'days' => $days,
            'logins' => (clone $logins)->with('user:id,name,email')
                ->latest()->paginate(15, ['*'], 'login_page')->withQueryString(),
            'plays' => (clone $plays)->with(['user:id,name,email', 'game:id,name'])
                ->latest()->paginate(15, ['*'], 'play_page')->withQueryString(),
        ]);
    }
}
