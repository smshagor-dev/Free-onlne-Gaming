<?php

namespace App\Http\Controllers;

use App\Services\EmbeddableGameCatalog;
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
        $games = new LengthAwarePaginator(
            $filtered->forPage($page, 48)->values(),
            $filtered->count(),
            48,
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
