<?php

namespace App\Http\Controllers;

use App\Services\EmbeddableGameCatalog;
use App\Services\SiteCms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class EmbeddableGameController extends Controller
{
    public function homeBatch(Request $request, EmbeddableGameCatalog $catalog)
    {
        $all = $catalog->all();
        $page = max(1, $request->integer('page', 1));
        $offset = ($page - 1) * 30;
        $games = array_slice($all, $offset, 30);

        return response()->json([
            'html' => view($request->query('layout') === 'home' ? 'playable.home-cards' : 'playable.cards', compact('games'))->render(),
            'has_more' => $offset + count($games) < count($all),
        ]);
    }

    /**
     * Lightweight live suggestions for the shared navigation search.
     * Uses the same filtered embeddable catalog as the full /play-games page.
     */
    public function suggestions(Request $request, EmbeddableGameCatalog $catalog): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ]);
        $term = trim($validated['q']);

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $results = collect($catalog->all())
            ->filter(static fn (array $game): bool => mb_stripos((string) ($game['name'] ?? ''), $term) !== false)
            ->take(SiteCms::section('catalog')['suggestion_limit'])
            ->map(static fn (array $game): array => [
                'name' => (string) $game['name'],
                'category' => (string) ($game['category'] ?? 'Games'),
                'image' => $game['image'] ?? null,
                'url' => route('playable.play', ['id' => $game['id']]),
            ])
            ->values()
            ->all();

        return response()->json(['data' => $results]);
    }

    public function index(Request $request, EmbeddableGameCatalog $catalog)
    {
        $all = collect($catalog->all());
        $category = $request->query('category');
        $categories = $all->pluck('category')->unique()->sort()->values();
        $filtered = $category ? $all->where('category', $category)->values() : $all;
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $filtered = $filtered->filter(fn ($game) => mb_stripos($game['name'], $search) !== false)->values();
        }
        $page = max(1, $request->integer('page', 1));
        $perPage = SiteCms::section('catalog')['page_size'];
        $games = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('playable.index', compact('games', 'categories', 'category', 'search'));
    }

    public function play(string $id, EmbeddableGameCatalog $catalog)
    {
        $game = $catalog->find($id);
        abort_unless($game, 404);

        return view('playable.play', compact('game'));
    }
}
