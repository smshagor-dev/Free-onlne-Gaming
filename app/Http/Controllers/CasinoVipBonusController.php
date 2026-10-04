<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasinoGameSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Transaction;

class CasinoVipBonusController extends Controller
{
    protected $activeTemplate;

    protected string $providerName = 'vip_bonus';
    public $host;
    public $hall;
    public $key;
    public $cdnUrl;
    public $domain;

    public function __construct()
    {
        $provider = config("casino.providers.{$this->providerName}", []);
        $this->host = config('casino.host');
        $this->hall = (string) ($provider['hall'] ?? '');
        $this->key = (string) ($provider['key'] ?? '');
        $this->cdnUrl = config('casino.cdn_url');
        $this->domain = config('casino.domain');
    }

    public function index(Request $request)
    {
        // Get data from cache (24 hours cache)
        $casinoData = Cache::get('casino_vip_response');
        
        if (!$casinoData) {
            app()->call('App\Http\Controllers\CasinoVipBonusController@cacheCasinoData');
            $casinoData = Cache::get('casino_vip_response');
        }

        if (!$casinoData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No cached casino data found. Please refresh first.'
            ], 404);
        }

        $casinoDataArray = json_decode($casinoData, true);

        // Get all games
        $games = $casinoDataArray['content']['gameList'] ?? [];

        // Get filters and sort parameters
        $category = $request->get('category');
        $search   = $request->get('search');
        $sortBy   = $request->get('sort_by', 'name');
        $order    = $request->get('order', 'asc');
        $perPage  = (int) $request->get('limit', 30);

        // Filter games
        $games = array_filter($games, function ($game) use ($category, $search) {
            $matchCategory = true;
            $matchSearch   = true;

            if ($category) {
                $matchCategory = isset($game['categories']) && $game['categories'] === $category;
            }

            if ($search) {
                $matchSearch = isset($game['name']) && stripos($game['name'], $search) !== false;
            }

            return $matchCategory && $matchSearch;
        });

        // Convert to collection
        $games = collect($games);

        // Sorting logic
        $games = $games->sortBy(function ($game) use ($sortBy) {
            return $game[$sortBy] ?? null;
        }, SORT_REGULAR, strtolower($order) === 'desc')->values();

        // Paginate manually (default 30 per page)
        $perPage     = $perPage > 0 ? $perPage : 30;
        $currentPage = $request->get('page', 1);
        $pagedData   = $games->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $games->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Categories available after filtering
        $categories = $games->pluck('categories')->filter()->unique()->values();

        return view('vip_bonus.index', [
            'games'            => $paginatedGames,
            'categories'       => $categories,
            'selectedCategory' => $category,
            'search'           => $search,
            'sortBy'           => $sortBy,
            'order'            => $order,
        ]);
    }
    /**
     * Fetch casino data from external API
     */
    public function getCasinoData()
    {
        $url = $this->host;

        $raw = [
            "cmd" => "gamesList",
            "hall" => $this->hall,
            "key" => $this->key,
            "cdnUrl" => $this->cdnUrl,
        ];

        $client = new \GuzzleHttp\Client();

        $response = $client->post($url, [
            'json' => $raw,
            'headers' => [
                'Accept' => 'application/json',
            ]
        ]);

        $responseData = $response->getBody()->getContents();

        // Debug
        // \Log::info("Casino API Response: " . $responseData);

        return $responseData;
    }

    public function cacheCasinoData()
    {
        $responseData = $this->getCasinoData();

        // Store in cache for 24 hours (86400 seconds)
        Cache::put('casino_vip_response', $responseData, 86400);

        return redirect()->back()->with('success', 'Successfully stored data');
    }

    public function adminCasino(Request $request)
    {
        return $this->adminCasinoGames($request, 'casino_vip_response');
    }

    public function closeCasinoGame()
    {
        return redirect()->route('casino.vip.bonus.index');
    }

    private function adminCasinoGames(Request $request, string $cacheKey)
    {
        $casinoData = Cache::get($cacheKey);

        if (!$casinoData) {
            return redirect()->back()->with('error', 'No cached casino data found. Please refresh first.');
        }

        $games = collect(json_decode($casinoData, true)['content']['gameList'] ?? []);
        $sortBy = $request->get('sort_by', 'name');
        $order = $request->get('order', 'asc');
        $games = $games->sortBy(fn ($game) => $game[$sortBy] ?? null, SORT_REGULAR, strtolower($order) === 'desc')->values();
        $currentPage = $request->get('page', 1);

        return view('admin.casino_view', [
            'games' => new \Illuminate\Pagination\LengthAwarePaginator(
                $games->slice(($currentPage - 1) * 20, 20)->values(),
                $games->count(),
                20,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            ),
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }



    public function casinoPlayer(Request $request)
    {
        return app(\App\Services\CasinoCallbackService::class)->handle($request, $this->providerName);
    }

    private function fetchBalanceFromDatabase($login)
    {
        return User::select('id', 'user_id', 'balance', 'available_balance', 'vip_bonus')
            ->where('user_id', $login)
            ->first();
    }


    // public function openCasinoGame(Request $request)
    // {
    //     try {
    //         $url = $this->host . 'openGame/';
    //         $raw = [
    //             "cmd" => "openGame",
    //             "hall" => $this->hall,
    //             "key" => $this->key,
    //             "cdnUrl" => $this->cdnUrl,
    //             "domain" => "http://127.0.0.1:8000",
    //             "exitUrl" => "http://127.0.0.1:8000/casino/close",
    //             "language" => "en",
    //             "login" => auth::user()->user_id,
    //             "gameId" => $request->id,
    //             "cdnUrl" => "https://static.cdns-stat.com/resources",
    //             "demo" => $request->demo,
    //             "continent" => "eur"
    //         ];
    //         // dd($raw);
    //         $client = new \GuzzleHttp\Client();
    //         $response = $client->request('POST', $url, [
    //             'body' => json_encode($raw),
    //             'headers' => [
    //                 'Content-Type' => 'application/json',
    //             ]
    //         ]);

    //         $responseData = $response->getBody()->getContents();

    //         $data = json_decode($responseData, true);
    //         // dd($data);

    //         if ($data['content']['game']['sessionId']) {
    //             $sessionData = new CasinoGameSession;
    //             $sessionData->user_id = auth::user()->user_id;
    //             $sessionData->session_id = $data['content']['game']['sessionId'];
    //             $sessionData->game_name = $request->name;
    //             $sessionData->save();
    //         }

    //         return $responseData;
    //     } catch (\Exception $e) {
    //         return $e->getMessage();
    //     }
    // }


    // Iframe open game
    public function casinoGameOpen(Request $request)
    {
        $pageTitle = 'Casino Open';

        $userLogin = auth::user()->user_id;
        $gameId = $request->input('gameId');

        if (empty($gameId)) {
            return back()->with('error', 'Game ID is required.');
        }

        $payload = [
            "cmd" => "openGame",
            "hall" => $this->hall,
            "domain" => config('app.url'),
            "exitUrl" => route('casino.vip.bonus.close'),
            "language" => 'en',
            "key" => $this->key,
            "login" => $userLogin,
            "gameId" => $gameId,
            "cdnUrl" => "https://static.cdns-stat.com/resources",
            "demo" => $request->input('demo', '0'),
            "continent" => "eur"
        ];

        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post($this->host . 'openGame/', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload)
            ]);

            $res = json_decode($response->getBody()->getContents(), true);

            if (($res['status'] ?? '') !== 'success') {
                return back()->with('error', $res['error'] ?? 'Failed to open game.');
            }

            $game = $res['content']['game'] ?? [];
            $sessionId = $res['content']['gameRes']['sessionId'] ?? null;
            $gameUrl = $game['url'] ?? null;

            if ($sessionId) {
                $sessionData = new CasinoGameSession();
                $sessionData->user_id = auth::id();
                $sessionData->session_id = $sessionId;
                $sessionData->game_name = $request->input('name', $game['name'] ?? 'Unknown');
                $sessionData->save();
            }

            if (!$gameUrl) {
                return back()->with('error', 'Game URL not returned from API.');
            }

            if (($game['iframe'] ?? "0") == "1" && ($game['withoutFrame'] ?? "0") == "0") {
                return view('vip_bonus.play', [
                    'pageTitle' => $pageTitle,
                    'src' => $gameUrl,
                    'exitButton' => $game['exitButton'] ?? 1,
                    'sessionId' => $sessionId
                ]);
            }

            // Otherwise redirect
            return redirect()->to($gameUrl);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }




    //  casino history
    public function casinoHistory()
    {
        $pageTitle = 'Casino Bet History';

        $user = Auth::id();

        $currentDate = Carbon::now();
        $nineDaysAgo = $currentDate->copy()->subDays(9);

        $data = CasinoGameSession::where('user_id', $user)
            ->whereBetween('created_at', [$nineDaysAgo->startOfDay(), $currentDate->endOfDay()])
            ->latest()
            ->limit(100)
            ->paginate(10);

        return view('casino.history', compact('pageTitle', 'data'));
    }

    // casino session
    public function casinoSession($session)
    {
        try {
            $pageTitle = "Casino $session Hisotry";
            $url = $this->host;
            $raw = [
                "cmd" => "gameSessionsLog",
                "hall" => $this->hall,
                "key" => $this->key,
                "sessionsId" => $session,
                "count" => 40,
                "page" => 1
            ];
            // dd($raw);
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'body' => json_encode($raw),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = $response->getBody()->getContents();

            $data = json_decode($responseData, true);
            return view('casino.session', compact('pageTitle', 'data', 'session'));
        } catch (\Exception $e) {
            $notify = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function showAllGames(Request $request)
    {
        $casinoData = $this->getCasinoData();
        $casinoDataArray = json_decode($casinoData, true);

        // Get all games
        $games = $casinoDataArray['content']['gameList'] ?? [];

        // Convert to collection
        $games = collect($games);

        // Paginate manually (30 per page)
        $perPage     = 30;
        $currentPage = $request->get('page', 1);
        $pagedData   = $games->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $games->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Categories available
        $categories = $games->pluck('categories')->filter()->unique()->values();

        return view('index', [
            'games'      => $paginatedGames,
            'categories' => $categories,
        ]);
    }
}
