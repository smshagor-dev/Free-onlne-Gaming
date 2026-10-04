<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogWebTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'games.rawg.key' => 'test-rawg-key',
            'games.rawg.base_url' => 'https://rawg.test/api',
            'games.freetogame.base_url' => 'https://freetogame.test/api',
            'games.gamerpower.base_url' => 'https://gamerpower.test/api',
            'games.cheapshark.base_url' => 'https://cheapshark.test/api/1.0',
            'games.cheapshark.web_url' => 'https://cheapshark.test',
            'games.rawg.timeout' => 1,
            'games.freetogame.timeout' => 1,
            'games.gamerpower.timeout' => 1,
            'games.cheapshark.timeout' => 1,
            'games.rawg.connect_timeout' => 1,
            'games.freetogame.connect_timeout' => 1,
            'games.gamerpower.connect_timeout' => 1,
            'games.cheapshark.connect_timeout' => 1,
        ]);

        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_public_game_home_renders_all_catalog_sections(): void
    {
        $this->fakeCatalog();

        $this->get('/games')
            ->assertOk()
            ->assertSee('Trending &amp; discover', false)
            ->assertSee('Free-to-play')
            ->assertSee('Active giveaways')
            ->assertSee('Game deals')
            ->assertSee('RAWG Pick')
            ->assertSee('Free Pick')
            ->assertSee('Giveaway Pick')
            ->assertSee('Deal Pick');
    }

    public function test_search_preserves_query_and_renders_aggregated_results(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 101,
                'name' => 'Alpha RAWG',
                'released' => '2026-01-01',
                'platforms' => [['platform' => ['name' => 'PC']]],
            ]]], 200),
            'freetogame.test/api/games*' => Http::response([[
                'id' => 102,
                'title' => 'Alpha Free',
                'platform' => 'PC',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([], 200),
            'cheapshark.test/api/1.0/games*' => Http::response([], 200),
        ]);

        $this->get('/games/search?q=Alpha')
            ->assertOk()
            ->assertSee('value="Alpha"', false)
            ->assertSee('Alpha RAWG')
            ->assertSee('Alpha Free');
    }

    public function test_details_page_renders_normalized_game_information_and_safe_external_link(): void
    {
        Http::fake([
            'rawg.test/api/games/77*' => Http::response([
                'id' => 77,
                'slug' => 'detail-game',
                'name' => 'Detail Game',
                'description_raw' => '<b>Safe description</b>',
                'background_image' => 'https://img.test/detail.jpg',
                'released' => '2026-06-07',
                'rating' => 4.7,
                'genres' => [['name' => 'Action']],
                'platforms' => [['platform' => ['name' => 'PC']]],
                'developers' => [['name' => 'Studio']],
                'publishers' => [['name' => 'Publisher']],
                'stores' => [['store' => ['name' => 'Steam']]],
                'website' => 'https://game.test',
            ], 200),
        ]);

        $this->get('/games/rawg/77')
            ->assertOk()
            ->assertSee('Detail Game')
            ->assertSee('Safe description')
            ->assertDontSee('<b>Safe description</b>', false)
            ->assertSee('Studio')
            ->assertSee('Publisher')
            ->assertSee('rel="noopener noreferrer nofollow"', false);
    }

    public function test_free_giveaway_and_deal_pages_render(): void
    {
        Http::fake([
            'freetogame.test/api/games*' => Http::response([[
                'id' => 201,
                'title' => 'Free Page Game',
                'platform' => 'PC',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([[
                'id' => 202,
                'title' => 'Giveaway Page Game',
                'platforms' => 'PC',
                'status' => 'Active',
                'open_giveaway_url' => 'https://giveaway.test/open',
            ]], 200),
            'cheapshark.test/api/1.0/deals*' => Http::response([[
                'dealID' => 'd203',
                'gameID' => '203',
                'title' => 'Deal Page Game',
                'salePrice' => '3.99',
                'normalPrice' => '19.99',
                'savings' => '80',
            ]], 200),
        ]);

        $this->get('/games/free')->assertOk()->assertSee('Free Page Game');
        Cache::flush();
        $this->get('/games/giveaways')->assertOk()->assertSee('Giveaway Page Game');
        Cache::flush();
        $this->get('/games/deals')->assertOk()->assertSee('Deal Page Game');
    }

    public function test_invalid_provider_and_missing_game_return_not_found(): void
    {
        Http::fake([
            'rawg.test/api/games/404*' => Http::response(['detail' => 'Not found'], 404),
        ]);

        $this->get('/games/not-a-provider/1')->assertNotFound();
        $this->get('/games/rawg/404')->assertNotFound();
    }

    public function test_empty_and_provider_failure_states_render_without_crashing(): void
    {
        Http::fake([
            'rawg.test/*' => Http::response(['error' => 'down'], 503),
            'freetogame.test/*' => Http::response([], 200),
            'gamerpower.test/*' => Http::response([], 200),
            'cheapshark.test/*' => Http::response([], 200),
        ]);

        $this->get('/games')
            ->assertOk()
            ->assertSee('Discover games are temporarily unavailable')
            ->assertSee('No free-to-play games available right now');

        Cache::flush();

        $this->get('/games/search?q=missing')
            ->assertOk()
            ->assertSee('No matching games found');
    }

    public function test_legacy_game_and_casino_routes_are_preserved(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());

        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'free-games' && $route->getName() === 'free.games.index'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'casino' && $route->getName() === 'casino.index'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'games/open/{id}' && $route->getName() === 'games.open'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'games/{provider}/{id}' && $route->getName() === 'catalog.show'));
    }

    private function fakeCatalog(): void
    {
        Http::fake([
            'rawg.test/api/genres*' => Http::response(['results' => [[
                'id' => 1,
                'slug' => 'action',
                'name' => 'Action',
            ]]], 200),
            'rawg.test/api/platforms*' => Http::response(['results' => [[
                'id' => 4,
                'slug' => 'pc',
                'name' => 'PC',
            ]]], 200),
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 10,
                'name' => 'RAWG Pick',
                'released' => '2026-01-01',
                'rating' => 4.4,
                'genres' => [['name' => 'Action']],
                'platforms' => [['platform' => ['name' => 'PC']]],
            ]]], 200),
            'freetogame.test/api/games*' => Http::response([[
                'id' => 20,
                'title' => 'Free Pick',
                'platform' => 'PC',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([[
                'id' => 30,
                'title' => 'Giveaway Pick',
                'platforms' => 'PC',
                'status' => 'Active',
            ]], 200),
            'cheapshark.test/api/1.0/deals*' => Http::response([[
                'dealID' => 'd40',
                'gameID' => '40',
                'title' => 'Deal Pick',
                'salePrice' => '4.99',
                'normalPrice' => '19.99',
                'savings' => '75',
            ]], 200),
        ]);
    }
}
