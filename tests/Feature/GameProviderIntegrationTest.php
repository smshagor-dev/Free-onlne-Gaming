<?php

namespace Tests\Feature;

use App\GameProviders\CheapSharkProvider;
use App\GameProviders\FreeToGameProvider;
use App\GameProviders\GamerPowerProvider;
use App\GameProviders\RawgProvider;
use App\Services\GameCatalogService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameProviderIntegrationTest extends TestCase
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
    }

    public function test_rawg_success_is_normalized_and_nullable_fields_are_safe(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 10,
                'slug' => 'alpha-game',
                'name' => 'Alpha Game',
                'background_image' => null,
                'released' => '2026-01-02',
                'rating' => 4.25,
                'genres' => [['name' => 'Action']],
                'platforms' => [['platform' => ['name' => 'PC']]],
                'stores' => null,
                'short_screenshots' => null,
            ]]], 200),
        ]);

        $game = app(RawgProvider::class)->discover()[0];

        $this->assertSame('rawg:10', $game->id);
        $this->assertSame('10', $game->providerId);
        $this->assertSame('Alpha Game', $game->title);
        $this->assertSame(['Action'], $game->genres);
        $this->assertSame(['PC'], $game->platforms);
        $this->assertNull($game->image);
        $this->assertSame([], $game->screenshots);
        $this->assertSame([], $game->stores);
    }

    public function test_freetogame_success_is_normalized(): void
    {
        Http::fake([
            'freetogame.test/api/games*' => Http::response([[
                'id' => 20,
                'title' => 'Free Alpha',
                'thumbnail' => 'https://img.test/free.jpg',
                'short_description' => 'Free game',
                'game_url' => 'https://game.test',
                'genre' => 'Shooter',
                'platform' => 'PC (Windows)',
                'publisher' => 'Pub',
                'developer' => 'Dev',
                'release_date' => '2026-02-03',
            ]], 200),
        ]);

        $game = app(FreeToGameProvider::class)->freeToPlay()[0];

        $this->assertSame('freetogame', $game->provider);
        $this->assertTrue($game->isFree);
        $this->assertSame(0.0, $game->price);
        $this->assertSame(['Shooter'], $game->genres);
        $this->assertSame(['Dev'], $game->developers);
    }

    public function test_gamerpower_success_is_normalized(): void
    {
        Http::fake([
            'gamerpower.test/api/giveaways*' => Http::response([[
                'id' => 30,
                'title' => 'Giveaway Alpha',
                'worth' => '$29.99',
                'thumbnail' => 'https://img.test/giveaway.jpg',
                'description' => 'Claim it',
                'open_giveaway_url' => 'https://giveaway.test/open',
                'gamerpower_url' => 'https://gamerpower.test/item',
                'type' => 'Game',
                'platforms' => 'PC, Steam',
                'status' => 'Active',
                'published_date' => '2026-01-01',
                'end_date' => '2026-01-10',
            ]], 200),
        ]);

        $game = app(GamerPowerProvider::class)->giveaways()[0];

        $this->assertSame('gamerpower', $game->provider);
        $this->assertTrue($game->isFree);
        $this->assertSame(29.99, $game->normalPrice);
        $this->assertSame(100.0, $game->discount);
        $this->assertSame('Active', $game->giveaway['status']);
    }

    public function test_cheapshark_success_is_normalized(): void
    {
        Http::fake([
            'cheapshark.test/api/1.0/deals*' => Http::response([[
                'dealID' => 'deal-40',
                'gameID' => '40',
                'title' => 'Deal Alpha',
                'salePrice' => '9.99',
                'normalPrice' => '39.99',
                'savings' => '75.018754',
                'storeID' => '1',
                'metacriticScore' => '88',
                'releaseDate' => 1767225600,
                'thumb' => 'https://img.test/deal.jpg',
            ]], 200),
        ]);

        $game = app(CheapSharkProvider::class)->deals()[0];

        $this->assertSame('cheapshark:deal:deal-40', $game->id);
        $this->assertSame('40', $game->providerId);
        $this->assertSame(9.99, $game->price);
        $this->assertSame(39.99, $game->normalPrice);
        $this->assertSame(['1'], $game->stores);
        $this->assertStringContainsString('dealID=deal-40', $game->dealUrl);
    }

    public function test_malformed_provider_payload_returns_safe_empty_result(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => 'broken'], 200),
        ]);

        $this->assertSame([], app(RawgProvider::class)->discover());
    }

    public function test_connection_failure_returns_safe_empty_result(): void
    {
        Http::fake([
            'freetogame.test/*' => Http::failedConnection(),
        ]);

        $this->assertSame([], app(FreeToGameProvider::class)->freeToPlay());
        Http::assertSentCount(2);
    }

    public function test_rate_limit_is_not_retried_or_exposed(): void
    {
        Http::fake([
            'gamerpower.test/*' => Http::response(['error' => 'rate limited'], 429),
        ]);

        $this->assertSame([], app(GamerPowerProvider::class)->giveaways());
        Http::assertSentCount(1);
    }

    public function test_server_error_is_retried_once_then_returns_safe_empty_result(): void
    {
        Http::fake([
            'cheapshark.test/*' => Http::response(['error' => 'down'], 503),
        ]);

        $this->assertSame([], app(CheapSharkProvider::class)->deals());
        Http::assertSentCount(2);
    }

    public function test_cache_hit_avoids_repeated_provider_call(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 50,
                'name' => 'Cached Game',
            ]]], 200),
        ]);

        $provider = app(RawgProvider::class);
        $this->assertCount(1, $provider->discover());
        $this->assertCount(1, $provider->discover());
        Http::assertSentCount(1);
    }

    public function test_expired_fresh_cache_can_fall_back_to_stale_data(): void
    {
        Http::fakeSequence()
            ->push(['results' => [['id' => 51, 'name' => 'Stale Game']]], 200)
            ->push(['error' => 'down'], 503)
            ->push(['error' => 'down'], 503);

        $provider = app(RawgProvider::class);
        $first = $provider->discover();
        $this->travel(7)->hours();
        $second = $provider->discover();

        $this->assertSame('Stale Game', $first[0]->title);
        $this->assertSame('Stale Game', $second[0]->title);
        Http::assertSentCount(3);
    }

    public function test_aggregated_search_combines_results_and_survives_one_provider_failure(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['error' => 'down'], 503),
            'freetogame.test/api/games*' => Http::response([[
                'id' => 61,
                'title' => 'Alpha Free',
                'platform' => 'PC',
                'release_date' => '2026-01-01',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([[
                'id' => 62,
                'title' => 'Alpha Giveaway',
                'platforms' => 'PC',
            ]], 200),
            'cheapshark.test/api/1.0/games*' => Http::response([[
                'gameID' => '63',
                'external' => 'Alpha Deal',
                'cheapest' => '4.99',
                'cheapestDealID' => 'd63',
            ]], 200),
        ]);

        $results = app(GameCatalogService::class)->search('Alpha');

        $this->assertCount(3, $results);
        $this->assertSame(['freetogame', 'gamerpower', 'cheapshark'], array_map(fn ($game) => $game->provider, $results));
    }

    public function test_aggregated_search_deduplicates_only_confident_cross_provider_match(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 70,
                'name' => 'Same Game',
                'released' => '2026-04-04',
                'platforms' => [['platform' => ['name' => 'PC']]],
            ]]], 200),
            'freetogame.test/api/games*' => Http::response([[
                'id' => 71,
                'title' => 'Same Game',
                'release_date' => '2026-04-04',
                'platform' => 'PC',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([], 200),
            'cheapshark.test/api/1.0/games*' => Http::response([], 200),
        ]);

        $results = app(GameCatalogService::class)->search('Same Game');

        $this->assertCount(1, $results);
        $this->assertSame('rawg', $results[0]->provider);
    }

    public function test_same_title_without_release_platform_context_is_not_merged(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 80,
                'name' => 'Shared Name',
            ]]], 200),
            'freetogame.test/api/games*' => Http::response([[
                'id' => 81,
                'title' => 'Shared Name',
            ]], 200),
            'gamerpower.test/api/giveaways*' => Http::response([], 200),
            'cheapshark.test/api/1.0/games*' => Http::response([], 200),
        ]);

        $results = app(GameCatalogService::class)->search('Shared Name');

        $this->assertCount(2, $results);
    }

    public function test_aggregate_search_itself_is_cached(): void
    {
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => []], 200),
            'freetogame.test/api/games*' => Http::response([], 200),
            'gamerpower.test/api/giveaways*' => Http::response([], 200),
            'cheapshark.test/api/1.0/games*' => Http::response([], 200),
        ]);

        $catalog = app(GameCatalogService::class);
        $catalog->search('cached query');
        $catalog->search('cached query');

        Http::assertSentCount(4);
    }

    public function test_game_catalog_endpoints_return_normalized_payload_and_existing_casino_routes_remain_available(): void
    {
        Http::fake([
            'cheapshark.test/api/1.0/deals*' => Http::response([[
                'dealID' => 'route-deal',
                'gameID' => 'route-game',
                'title' => 'Route Deal',
                'salePrice' => '1.99',
                'normalPrice' => '9.99',
            ]], 200),
        ]);

        $this->getJson('/api/games/deals')
            ->assertOk()
            ->assertJsonPath('data.0.provider', 'cheapshark')
            ->assertJsonPath('data.0.title', 'Route Deal');

        $routes = collect(app('router')->getRoutes()->getRoutes());
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'api/casino/player'));
        $this->assertTrue($routes->contains(fn ($route) => $route->uri() === 'api/games/search'));
    }
}
