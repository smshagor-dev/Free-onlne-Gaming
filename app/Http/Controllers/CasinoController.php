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
use App\Models\LastPlay;
use App\Models\Transaction;


class CasinoController extends Controller
{
    protected $activeTemplate;

    protected string $providerName = 'default';
    public $host;
    public $hall;
    public $key;
    public $cdnUrl;
    public $domain;
    protected \GuzzleHttp\Client $httpClient;

    public function __construct(?\GuzzleHttp\Client $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new \GuzzleHttp\Client();
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
        $casinoData = Cache::get('casino_raw_response');
    
        if (!$casinoData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No cached casino data found. Please refresh first.'
            ], 404);
        }
    
        $casinoDataArray = json_decode($casinoData, true);
    
        // Get all games
        $games = $casinoDataArray['content']['gameList'] ?? [];
        
        if (auth::check()) {
            $userId = auth::id();
            $gameIds = array_column($games, 'id');
        
            $lastPlays = \App\Models\LastPlay::where('user_id', $userId)
                ->whereIn('game_id', $gameIds)
                ->get()
                ->keyBy('game_id');
        
            foreach ($games as &$game) {
                $game['lastPlay'] = $lastPlays[$game['id']] ?? null;
            }
            unset($game);
        }
            
        // Get filters and sort parameters
        $category = $request->get('category');
        $search   = $request->get('search');
        $sortBy   = $request->get('sort_by', 'name');
        $order    = $request->get('order', 'asc');
        $perPage  = (int) $request->get('limit', 60);
    
        // Filter games
        $games = array_filter($games, function ($game) use ($category, $search) {
            $matchCategory = true;
            $matchSearch   = true;
    
            if ($category) {
                $categories = explode(',', $category); // multiple support
                $matchCategory = isset($game['categories']) && in_array($game['categories'], $categories);
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
        $perPage     = $perPage > 0 ? $perPage : 60;
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
    
        return view('casino.index', [
            'games'            => $paginatedGames,
            'categories'       => $categories,
            'selectedCategory' => $category,
            'search'           => $search,
            'sortBy'           => $sortBy,
            'order'            => $order,
        ]);
    }

    public function indexhome()
    {
        return redirect()->route('casino.index');
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

        $response = $this->httpClient->post($url, [
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
        Cache::put('casino_raw_response', $responseData, 86400);

        return redirect()->back()->with('success', 'Successfully stored data');
    } 
    
    public function adminCasino(Request $request)
    {
        // Load cached casino data
        $casinoData = Cache::get('casino_raw_response');
    
        if (!$casinoData) {
            return redirect()->back()->with('error', 'No cached casino data found. Please refresh first.');
        }
    
        $casinoDataArray = json_decode($casinoData, true);
    
        // Get all games
        $games = $casinoDataArray['content']['gameList'] ?? [];
    
        // Convert to collection
        $games = collect($games);
    
        // Sorting (optional, default by name ascending)
        $sortBy = $request->get('sort_by', 'name');
        $order  = $request->get('order', 'asc');
    
        $games = $games->sortBy(function ($game) use ($sortBy) {
            return $game[$sortBy] ?? null;
        }, SORT_REGULAR, strtolower($order) === 'desc')->values();
    
        // Pagination 20 per page
        $perPage     = 20;
        $currentPage = $request->get('page', 1);
        $pagedData   = $games->slice(($currentPage - 1) * $perPage, $perPage)->values();
    
        $paginatedGames = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            $games->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        return view('admin.casino_view', [
            'games'  => $paginatedGames,
            'sortBy' => $sortBy,
            'order'  => $order,
        ]);
    }

    public function closeCasinoGame()
    {
        return redirect()->route('casino.index');
    }



    public function casinoPlayer(Request $request)
    {
        return app(\App\Services\CasinoCallbackService::class)->handle($request, $this->providerName);
    }

    private function fetchBalanceFromDatabase($login)
    {
        return User::select('id', 'user_id', 'balance', 'available_balance')
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
        $demo = $request->input('demo', '0');

        if (empty($gameId)) {
            return back()->with('error', 'Game ID is required.');
        }
        
        if ($demo == '0') {
            $exists = LastPlay::where('user_id', auth::id())
                ->where('game_id', $gameId)
                ->exists();
        
            if (!$exists) {
                LastPlay::create([
                    'user_id' => auth::id(),
                    'game_id' => $gameId,
                    'last_play' => true,
                ]);
            }
        }

        $payload = [
            "cmd" => "openGame",
            "hall" => $this->hall,
            "domain" => config('app.url'),
            "exitUrl" => route('casino.close'),
            "language" => 'en',
            "key" => $this->key,
            "login" => $userLogin,
            "gameId" => $gameId,
            "cdnUrl" => "https://static.cdns-stat.com/resources",
            "demo" => $request->input('demo', '0'), 
            "continent" => "eur" 
        ];

        try {
            $response = $this->httpClient->post($this->host . 'openGame/', [
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
                return view('casino.play', [
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
    
    // Mark game as favourite
    public function toggleFavourite(Request $request)
    {
        $userId = auth::id();
        $gameId = $request->input('game_id');

        if (!$gameId) {
            return back()->with('error', 'Game ID is required');
        }

        $lastPlay = LastPlay::firstOrNew([
            'user_id' => $userId,
            'game_id' => $gameId,
        ]);

        $lastPlay->is_favourite = $lastPlay->exists ? !$lastPlay->is_favourite : 1;

        if (!$lastPlay->exists) {
            $lastPlay->is_bookmark = 0;
        }

        $lastPlay->save();

        $message = $lastPlay->is_favourite ? 'Game marked as favourite' : 'Game removed from favourite';
        
        return response()->json([
            'success' => true, // or false
            'message' => $message,
            'is_active' => (bool) $lastPlay->is_favourite
        ]);

    }

    public function toggleBookmark(Request $request)
    {
        $userId = auth::id();
        $gameId = $request->input('game_id');

        if (!$gameId) {
            return back()->with('error', 'Game ID is required');
        }

        $lastPlay = LastPlay::firstOrNew([
            'user_id' => $userId,
            'game_id' => $gameId,
        ]);

        $lastPlay->is_bookmark = $lastPlay->exists ? !$lastPlay->is_bookmark : 1;

        if (!$lastPlay->exists) {
            $lastPlay->is_favourite = 0;
        }

        $lastPlay->save();

        $message = $lastPlay->is_bookmark ? 'Game bookmarked' : 'Game removed from bookmark';
        
        return response()->json([
            'success' => true, // or false
            'message' => $message,
            'is_active' => (bool) $lastPlay->is_bookmark
        ]);

    }
}
