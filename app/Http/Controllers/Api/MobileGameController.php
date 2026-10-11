<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\MobileGameState;
use App\Services\CatalogPersonalizationService;
use App\Services\GameCatalogService;
use App\Services\MobileActivityService;
use App\Services\MobileGameCatalog;
use Illuminate\Http\Request;

class MobileGameController extends Controller
{
    public function __construct(private MobileGameCatalog $catalog) {}

    public function index(Request $r)
    {
        $d = $r->validate(['provider' => 'sometimes|in:gamemonetize,local,freetogame,gamerpower', 'q' => 'sometimes|string|max:120', 'category' => 'sometimes|string|max:80', 'page' => 'sometimes|integer|min:1|max:100000', 'per_page' => 'sometimes|integer|min:1|max:30']);
        $games = collect($this->catalog->list($d['provider'] ?? 'gamemonetize'))->filter(function ($g) use ($d) {
            return (! isset($d['q']) || str_contains(mb_strtolower($g['title']), mb_strtolower($d['q']))) && (! isset($d['category']) || strcasecmp($g['category'] ?? implode(',', $g['genres'] ?? []), $d['category']) === 0);
        })->values();
        $page = $d['page'] ?? 1;
        $size = $d['per_page'] ?? 30;

        return response()->json(['data' => $games->slice(($page - 1) * $size, $size)->values(), 'meta' => ['page' => $page, 'per_page' => $size, 'total' => $games->count(), 'has_more' => $page * $size < $games->count(), 'next_page' => $page * $size < $games->count() ? $page + 1 : null]]);
    }

    public function categories()
    {
        return response()->json(['data' => collect($this->catalog->list('gamemonetize'))->pluck('category')->unique()->sort()->values()]);
    }

    public function show(string $provider, string $id)
    {
        $game = $this->catalog->find($provider, $id);
        abort_unless($game, 404, 'Game unavailable.');

        return response()->json(['data' => $game]);
    }

    public function favorite(Request $r, string $provider, string $id)
    {
        $game = $this->catalog->find($provider, $id);
        abort_unless($game, 404, 'Game unavailable.');
        if ($provider === 'gamemonetize') {
            MobileGameState::updateOrCreate(['user_id' => $r->user()->id, 'provider' => $provider, 'game_id' => $id], ['favorite' => true, 'snapshot' => $game]);
        }

        if ($provider === 'local') {
            Favorite::firstOrCreate(['user_id' => $r->user()->id, 'game_id' => $id]);
        }
        if (in_array($provider, ['freetogame', 'gamerpower'], true)) {
            $catalogGame = app(GameCatalogService::class)->details($provider, $id);
            if ($catalogGame) {
                app(CatalogPersonalizationService::class)->save($r->user(), $catalogGame);
            }
        }

        return response()->json(['message' => 'Game saved.']);
    }

    public function unfavorite(Request $r, string $provider, string $id)
    {
        MobileGameState::where('user_id', $r->user()->id)->where('provider', $provider)->where('game_id', $id)->update(['favorite' => false]);

        if ($provider === 'local') {
            Favorite::where('user_id', $r->user()->id)->where('game_id', $id)->delete();
        }
        if (in_array($provider, ['freetogame', 'gamerpower'], true)) {
            app(CatalogPersonalizationService::class)->remove($r->user(), $provider, $id);
        }

        return response()->json(['message' => 'Game removed.']);
    }

    public function played(Request $r, string $provider, string $id)
    {
        abort_unless($provider === 'gamemonetize', 422, 'Only browser games support mobile play history.');
        $game = $this->catalog->find($provider, $id);
        abort_unless($game, 404, 'Game unavailable.');
        MobileGameState::updateOrCreate(['user_id' => $r->user()->id, 'provider' => $provider, 'game_id' => $id], ['played_at' => now(), 'snapshot' => $game]);

        return response()->json(['message' => 'Play recorded.']);
    }

    public function favorites(Request $r)
    {
        return $this->states($r, 'favorite');
    }

    public function recent(Request $r)
    {
        return $this->states($r, 'recent');
    }

    private function states(Request $r, string $kind)
    {
        $r->validate(['page' => 'sometimes|integer|min:1']);
        $service = app(MobileActivityService::class);
        $items = $kind === 'favorite' ? $service->favorites($r->user()) : $service->recent($r->user());
        $page = (int) $r->input('page', 1);

        return response()->json(['data' => $items->slice(($page - 1) * 30, 30)->values(), 'meta' => ['page' => $page, 'per_page' => 30, 'total' => $items->count(), 'has_more' => $page * 30 < $items->count(), 'next_page' => $page * 30 < $items->count() ? $page + 1 : null]]);
    }

    public function clearRecent(Request $r)
    {
        MobileGameState::where('user_id', $r->user()->id)->update(['played_at' => null]);

        return response()->json(['message' => 'Mobile browser history cleared.']);
    }
}
