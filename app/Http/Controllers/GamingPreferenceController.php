<?php

namespace App\Http\Controllers;

use App\Services\CatalogPersonalizationService;
use App\Services\GameCatalogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GamingPreferenceController extends Controller
{
    public function __construct(private readonly CatalogPersonalizationService $personalization, private readonly GameCatalogService $catalog) {}

    public function edit(Request $request): View
    {
        return view('catalog.settings', [
            'preferences' => $this->personalization->preferences($request->user()),
            'genres' => array_slice($this->catalog->genres(), 0, 60),
            'platforms' => array_slice($this->catalog->platforms(), 0, 60),
            'pageTitle' => 'Gaming Preferences | '.config('app.name'),
            'metaDescription' => 'Manage your gaming preferences and catalog history.',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_genres' => ['nullable', 'array', 'max:20'],
            'preferred_genres.*' => ['string', 'max:80'],
            'preferred_platforms' => ['nullable', 'array', 'max:20'],
            'preferred_platforms.*' => ['string', 'max:80'],
        ]);
        $this->personalization->updatePreferences($request->user(), $validated['preferred_genres'] ?? [], $validated['preferred_platforms'] ?? []);
        return back()->with('success', 'Gaming preferences updated.');
    }
}
