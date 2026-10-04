<?php

namespace App\Http\Controllers;

use App\Data\GameData;
use App\Services\CatalogPersonalizationService;
use App\Services\GameCatalogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class GameBrowseController extends Controller
{
    public function __construct(private readonly GameCatalogService $catalog, private readonly CatalogPersonalizationService $personalization) {}

    public function index(Request $request): View
    {
        $filters = ['page' => 1, 'page_size' => 12, 'ordering' => '-rating'];
        if ($request->filled('genre')) $filters['genres'] = Str::limit((string) $request->query('genre'), 80, '');
        if ($request->filled('platform')) $filters['platforms'] = Str::limit((string) $request->query('platform'), 80, '');

        $discover = $this->take($this->catalog->discover($filters), 12);
        $free = $this->take($this->catalog->freeToPlay(), 8);
        $giveaways = $this->take($this->catalog->giveaways(['sort_by' => 'value']), 6);
        $deals = $this->take($this->catalog->deals(['page_size' => 12, 'sort_by' => 'Savings', 'desc' => true]), 8);
        $genres = $this->take($this->catalog->genres(), 14);
        $platforms = $this->take($this->catalog->platforms(), 14);

        $personalized = $recent = $saved = [];
        $preferences = null;
        if ($request->user()) {
            $preferences = $this->personalization->preferences($request->user());
            $personalized = $this->personalization->personalize([...$discover, ...$free, ...$giveaways, ...$deals], $preferences, 12);
            $recent = $this->personalization->recent($request->user(), 8);
            $saved = $this->personalization->saved($request->user(), 8);
        }

        return view('catalog.index', compact('discover', 'free', 'giveaways', 'deals', 'genres', 'platforms', 'personalized', 'recent', 'saved', 'preferences') + [
            'activeGenre' => $request->query('genre'), 'activePlatform' => $request->query('platform'),
            'pageTitle' => 'Discover Games | '.config('app.name'),
            'metaDescription' => 'Discover popular games, free-to-play titles, giveaways and current game deals from multiple trusted sources.',
        ]);
    }

    public function search(Request $request): View
    {
        $query = trim(Str::limit((string) $request->query('q', ''), 120, ''));
        $results = $query === '' ? [] : $this->catalog->search($query, ['page_size' => 40]);
        return view('catalog.search', ['query' => $query, 'games' => $this->paginate($results, $request, 24), 'pageTitle' => $query === '' ? 'Search Games | '.config('app.name') : 'Search: '.$query.' | '.config('app.name'), 'metaDescription' => $query === '' ? 'Search games across multiple game providers.' : 'Search results for '.$query.' across multiple game providers.']);
    }

    public function free(Request $request): View
    {
        $games = $this->catalog->freeToPlay(['platform' => $request->query('platform'), 'category' => $request->query('category'), 'sort_by' => $request->query('sort')]);
        return view('catalog.free', ['games' => $this->paginate($games, $request, 24), 'pageTitle' => 'Free-to-Play Games | '.config('app.name'), 'metaDescription' => 'Browse free-to-play games with platform, genre and release information.']);
    }

    public function giveaways(Request $request): View
    {
        $games = $this->catalog->giveaways(['platform' => $request->query('platform'), 'type' => $request->query('type'), 'sort_by' => $request->query('sort')]);
        return view('catalog.giveaways', ['games' => $this->paginate($games, $request, 18), 'pageTitle' => 'Game Giveaways | '.config('app.name'), 'metaDescription' => 'Find active game giveaways and limited-time free offers.']);
    }

    public function deals(Request $request): View
    {
        $games = $this->catalog->deals(['page_size' => 60, 'sort_by' => 'Savings', 'desc' => true, 'lower_price' => $request->query('min'), 'upper_price' => $request->query('max')]);
        return view('catalog.deals', ['games' => $this->paginate($games, $request, 24), 'pageTitle' => 'Game Deals | '.config('app.name'), 'metaDescription' => 'Browse current game discounts and price deals.']);
    }

    public function show(Request $request, string $provider, string $id): View
    {
        $provider = Str::lower($provider);
        $game = $this->catalog->details($provider, $id);
        if (! $game instanceof GameData) throw new NotFoundHttpException('Game not found.');
        if ($request->user()) $this->personalization->recordRecent($request->user(), $game);
        $plainDescription = trim(preg_replace('/\s+/', ' ', strip_tags($game->description ?? '')) ?? '');
        $metaDescription = $plainDescription !== '' ? Str::limit($plainDescription, 160) : 'View details for '.$game->title.', including platforms, release information and available offers.';
        return view('catalog.show', ['game' => $game, 'pageTitle' => $game->title.' | '.config('app.name'), 'metaDescription' => $metaDescription, 'ogImage' => $game->backgroundImage ?? $game->image]);
    }

    private function paginate(array $items, Request $request, int $perPage): LengthAwarePaginator
    {
        $page = max(1, $request->integer('page', 1)); $items = array_values($items); $slice = array_slice($items, ($page - 1) * $perPage, $perPage);
        return new Paginator($slice, count($items), $perPage, $page, ['path' => $request->url(), 'query' => $request->query()]);
    }

    private function take(array $items, int $limit): array { return array_slice(array_values($items), 0, $limit); }
}
