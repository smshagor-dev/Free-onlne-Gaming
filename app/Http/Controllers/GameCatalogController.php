<?php

namespace App\Http\Controllers;

use App\Data\GameData;
use App\Services\GameCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GameCatalogController extends Controller
{
    public function __construct(private readonly GameCatalogService $catalog)
    {
    }

    public function discover(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'page_size' => ['sometimes', 'integer', 'min:1', 'max:40'],
            'ordering' => ['sometimes', 'string', 'max:80'],
            'genres' => ['sometimes', 'string', 'max:255'],
            'platforms' => ['sometimes', 'string', 'max:255'],
            'dates' => ['sometimes', 'string', 'max:80'],
        ]);

        return $this->gamesResponse($this->catalog->discover($filters));
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:120'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'page_size' => ['sometimes', 'integer', 'min:1', 'max:40'],
            'ordering' => ['sometimes', 'string', 'max:80'],
            'genres' => ['sometimes', 'string', 'max:255'],
            'platforms' => ['sometimes', 'string', 'max:255'],
            'dates' => ['sometimes', 'string', 'max:80'],
        ]);

        $term = $validated['q'];
        unset($validated['q']);

        return $this->gamesResponse($this->catalog->search($term, $validated));
    }

    public function details(string $provider, string $id): JsonResponse
    {
        $game = $this->catalog->details($provider, $id);

        if ($game === null) {
            return response()->json(['message' => 'Game not found or provider is unavailable.'], 404);
        }

        return response()->json(['data' => $game->toArray()]);
    }

    public function freeToPlay(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'platform' => ['sometimes', 'string', 'max:80'],
            'category' => ['sometimes', 'string', 'max:80'],
            'sort_by' => ['sometimes', 'string', 'max:80'],
        ]);

        return $this->gamesResponse($this->catalog->freeToPlay($filters));
    }

    public function giveaways(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'platform' => ['sometimes', 'string', 'max:80'],
            'type' => ['sometimes', 'string', 'max:80'],
            'sort_by' => ['sometimes', 'string', 'max:80'],
        ]);

        return $this->gamesResponse($this->catalog->giveaways($filters));
    }

    public function deals(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'store_id' => ['sometimes', 'string', 'max:40'],
            'page' => ['sometimes', 'integer', 'min:0'],
            'page_size' => ['sometimes', 'integer', 'min:1', 'max:60'],
            'sort_by' => ['sometimes', 'string', 'max:80'],
            'desc' => ['sometimes', 'boolean'],
            'lower_price' => ['sometimes', 'numeric', 'min:0'],
            'upper_price' => ['sometimes', 'numeric', 'min:0'],
            'title' => ['sometimes', 'string', 'max:120'],
            'exact' => ['sometimes', 'boolean'],
        ]);

        return $this->gamesResponse($this->catalog->deals($filters));
    }

    public function genres(): JsonResponse
    {
        return response()->json(['data' => $this->catalog->genres()]);
    }

    public function platforms(): JsonResponse
    {
        return response()->json(['data' => $this->catalog->platforms()]);
    }

    private function gamesResponse(array $games): JsonResponse
    {
        return response()->json([
            'data' => array_values(array_map(
                static fn (GameData $game): array => $game->toArray(),
                $games
            )),
        ]);
    }
}
