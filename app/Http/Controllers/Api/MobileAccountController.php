<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lottary;
use App\Models\LottaryWinner;
use App\Models\LotteryTransaction;
use App\Services\CatalogPersonalizationService;
use App\Services\MobileActivityService;
use Illuminate\Http\Request;

class MobileAccountController extends Controller
{
    public function dashboard(Request $r)
    {
        $id = $r->user()->id;

        return response()->json(['data' => ['name' => $r->user()->name, 'favorites' => app(MobileActivityService::class)->favorites($r->user())->count(), 'games_played' => app(MobileActivityService::class)->recent($r->user())->count(), 'lottery_tickets' => LotteryTransaction::where('user_id', $id)->count(), 'lottery_wins' => LottaryWinner::where('user_id', $id)->count(), 'two_factor_enabled' => (bool) $r->user()->google2fa_status]]);
    }

    public function preferences(Request $r, CatalogPersonalizationService $s)
    {
        $p = $s->preferences($r->user());

        return response()->json(['data' => ['preferred_genres' => $p->preferred_genres ?? [], 'preferred_platforms' => $p->preferred_platforms ?? []]]);
    }

    public function savePreferences(Request $r, CatalogPersonalizationService $s)
    {
        $d = $r->validate(['preferred_genres' => 'required|array|max:20', 'preferred_genres.*' => 'string|max:80', 'preferred_platforms' => 'required|array|max:20', 'preferred_platforms.*' => 'string|max:80']);
        $s->updatePreferences($r->user(), $d['preferred_genres'], $d['preferred_platforms']);

        return $this->preferences($r, $s);
    }

    public function notifications(Request $r)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);

        return response()->json($r->user()->notifications()->latest()->paginate(30, ['id', 'title', 'message', 'is_read', 'created_at'])->toArray());
    }

    public function read(Request $r, string $id)
    {
        $r->user()->notifications()->findOrFail($id)->update(['is_read' => true]);

        return response()->json(['message' => 'Notification read.']);
    }

    public function readAll(Request $r)
    {
        $r->user()->notifications()->update(['is_read' => true]);

        return response()->json(['message' => 'Notifications read.']);
    }

    public function lotteries(Request $r)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);

        return response()->json(Lottary::orderByDesc('id')->paginate(30, ['id', 'title', 'description', 'photo', 'price', 'draw_date', 'is_draw'])->toArray());
    }

    public function tickets(Request $r)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);

        return response()->json(LotteryTransaction::where('user_id', $r->user()->id)->latest()->paginate(30, ['id', 'lottary_id', 'ticket_number', 'amount', 'created_at'])->toArray());
    }

    public function ticket(Request $r, string $id)
    {
        return response()->json(['data' => LotteryTransaction::where('user_id', $r->user()->id)->findOrFail($id)->only(['id', 'lottary_id', 'ticket_number', 'amount', 'created_at'])]);
    }

    public function wins(Request $r)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);

        return response()->json(LottaryWinner::where('user_id', $r->user()->id)->latest()->paginate(30, ['id', 'lottary_id', 'ticket_number', 'position', 'price', 'created_at'])->toArray());
    }
}
