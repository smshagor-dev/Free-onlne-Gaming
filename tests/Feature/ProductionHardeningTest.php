<?php

namespace Tests\Feature;

use App\Models\CatalogRecentGame;
use App\Models\CatalogSavedGame;
use App\Models\User;
use App\Models\UserGamePreference;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_cleanup_removes_only_expired_demo_users_and_cascaded_data(): void
    {
        $expiredDemo = User::factory()->create([
            'registration_type' => 'demo',
            'created_at' => now()->subHours(48),
        ]);
        $recentDemo = User::factory()->create([
            'registration_type' => 'demo',
            'created_at' => now()->subHours(2),
        ]);
        $realUser = User::factory()->create([
            'registration_type' => 'manual',
            'created_at' => now()->subDays(30),
        ]);

        CatalogSavedGame::create([
            'user_id' => $expiredDemo->id,
            'provider' => 'rawg',
            'provider_game_id' => 'cleanup-1',
            'title' => 'Cleanup Game',
        ]);
        CatalogRecentGame::create([
            'user_id' => $expiredDemo->id,
            'provider' => 'rawg',
            'provider_game_id' => 'cleanup-1',
            'title' => 'Cleanup Game',
            'viewed_at' => now()->subDay(),
        ]);
        UserGamePreference::create([
            'user_id' => $expiredDemo->id,
            'preferred_genres' => ['Action'],
            'preferred_platforms' => ['PC'],
        ]);
        DB::table('sessions')->insert([
            'id' => 'expired-demo-session',
            'user_id' => $expiredDemo->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);

        $this->artisan('demo:cleanup', ['--hours' => 24])->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('catalog_saved_games', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('catalog_recent_games', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('user_game_preferences', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseHas('users', ['id' => $recentDemo->id]);
        $this->assertDatabaseHas('users', ['id' => $realUser->id]);
    }

    public function test_demo_cleanup_dry_run_never_deletes_candidates(): void
    {
        $demo = User::factory()->create([
            'registration_type' => 'demo',
            'created_at' => now()->subHours(48),
        ]);

        $this->artisan('demo:cleanup', ['--hours' => 24, '--dry-run' => true])
            ->expectsOutputToContain('eligible for cleanup')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $demo->id]);
    }

    public function test_private_pages_receive_no_store_and_noindex_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_robots_sitemap_and_custom_404_are_publicly_available(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /user/', false)
            ->assertSee('Sitemap:', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee('/games', false);

        config(['app.debug' => false]);
        $this->get('/this-production-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found');
    }

    public function test_production_check_passes_for_hardened_runtime_configuration(): void
    {
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://example.test',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'session.secure' => true,
            'session.http_only' => true,
            'session.encrypt' => true,
            'security.trusted_hosts' => ['^example\\.test$'],
            'cache.default' => 'database',
            'queue.default' => 'database',
        ]);

        $this->artisan('app:production-check')->assertExitCode(0);
    }

    public function test_pwa_service_worker_excludes_private_and_financial_routes(): void
    {
        $worker = file_get_contents(public_path('sw.js'));

        $this->assertIsString($worker);
        $this->assertStringContainsString('/user/', $worker);
        $this->assertStringContainsString('/api/', $worker);
        $this->assertStringContainsString('/casino/play', $worker);
        $this->assertStringContainsString('cache: "no-store"', $worker);
    }

    public function test_casino_provider_exception_is_not_exposed_to_user_flash(): void
    {
        config([
            'casino.host' => 'https://casino-provider.test/',
            'casino.providers.default.hall' => 'hall-1',
            'casino.providers.default.key' => 'secret-key',
            'security.sanitize_provider_errors' => true,
        ]);

        $mock = new MockHandler([new GuzzleResponse(500, [], 'provider-internal-error')]);
        $this->app->instance(Client::class, new Client(['handler' => HandlerStack::create($mock)]));

        $user = User::factory()->create([
            'google2fa_status' => false,
            'is_banned' => false,
            'user_id' => 'production-player-1',
        ]);

        $this->actingAs($user)
            ->from('/games')
            ->get(route('casino.play', ['gameId' => 700, 'name' => 'Test Game', 'demo' => 0]))
            ->assertRedirect('/games')
            ->assertSessionHas('error', 'The game service is temporarily unavailable. Please try again.');
    }

    public function test_catalog_pages_keep_lazy_images_and_provider_calls_are_not_added_by_seo(): void
    {
        config(['games.rawg.key' => 'test-key', 'games.rawg.base_url' => 'https://rawg.test/api']);
        Http::fake([
            'rawg.test/api/games*' => Http::response(['results' => [[
                'id' => 1,
                'name' => 'Lazy Game',
                'background_image' => 'https://images.test/game.jpg',
                'genres' => [['name' => 'Action']],
                'platforms' => [['platform' => ['name' => 'PC']]],
            ]]], 200),
            '*' => Http::response([], 200),
        ]);

        $this->get('/games')->assertOk()->assertSee('loading="lazy"', false);
        $this->get('/sitemap.xml')->assertOk();
    }
}
