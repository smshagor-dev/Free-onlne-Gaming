<?php

namespace App\Http\Controllers;

use App\Data\GameData;
use App\Services\CatalogPersonalizationService;
use App\Services\GameCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CatalogLibraryController extends Controller
{
    public function __construct(private readonly GameCatalogService $catalog, private readonly CatalogPersonalizationService $personalization) {}

    public function index(Request $request): View
    {
        return view('catalog.library', [
            'saved' => $this->personalization->saved($request->user(), 100),
            'recent' => $this->personalization->recent($request->user(), 50),
            'pageTitle' => 'My Games | '.config('app.name'),
            'metaDescription' => 'Your saved catalog games and recently viewed titles.',
        ]);
    }

    public function store(Request $request, string $provider, string $id): RedirectResponse
    {
        $provider = Str::lower($provider);
        abort_unless(in_array($provider, ['rawg', 'freetogame', 'gamerpower', 'cheapshark'], true), 404);
        $game = $this->catalog->details($provider, $id);
        abort_unless($game instanceof GameData, 404);
        $this->personalization->save($request->user(), $game);
        return back()->with('success', 'Game saved to your watchlist.');
    }

    public function destroy(Request $request, string $provider, string $id): RedirectResponse
    {
        $provider = Str::lower($provider);
        abort_unless(in_array($provider, ['rawg', 'freetogame', 'gamerpower', 'cheapshark'], true), 404);
        $this->personalization->remove($request->user(), $provider, $id);
        return back()->with('success', 'Game removed from your watchlist.');
    }

    public function clearRecent(Request $request): RedirectResponse
    {
        $this->personalization->clearRecent($request->user());
        return back()->with('success', 'Recently viewed history cleared.');
    }
}
