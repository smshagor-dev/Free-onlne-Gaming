<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RuntimeSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_home_and_player_use_embeddable_games_when_local_games_are_empty(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        $games = [];
        for ($number = 1; $number <= 65; $number++) {
            $games[] = [
                'title' => 'Browser Game '.$number,
                'category' => 'Puzzle',
                'url' => 'https://html5.gamemonetize.co/game'.$number.'/',
                'thumb' => 'https://images.test/game'.$number.'.jpg',
            ];
        }
        $games[] = ['title' => 'Unsafe Game', 'url' => 'https://example.com/redirect'];
        Http::fake(['gamemonetize.com/rssfeed.php*' => Http::response($games, 200)]);

        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('Browser Game 1')
            ->assertSee(route('playable.play', 'game1'), false)
            ->assertSee('Browser Game 30')->assertSee('fg-scroll-sentinel')->assertDontSee('Load More Games')->assertDontSee('Browser Game 31')
            ->assertDontSee('Unsafe Game');

        $this->get(route('playable.load', ['page' => 2]))
            ->assertOk()->assertJsonPath('has_more', true)
            ->assertSee('Browser Game 31')->assertDontSee('Browser Game 61');
        $this->get(route('playable.load', ['page' => 2, 'layout' => 'home']))
            ->assertOk()->assertJsonPath('has_more', true)->assertSee('fg-game-card')->assertSee('Browser Game 31');
        $this->get(route('playable.load', ['page' => 3]))
            ->assertOk()->assertJsonPath('has_more', false)
            ->assertSee('Browser Game 65');

        $this->get(route('playable.index'))->assertOk()->assertSee('Browser Game 1');
        $this->get(route('playable.play', 'game1'))
            ->assertOk()
            ->assertSee('src="https://html5.gamemonetize.co/game1/"', false);
        $this->get(route('playable.play', 'MissingGame'))->assertNotFound();
    }

    public function test_home_search_and_categories_link_to_the_playable_catalog(): void
    {
        config(['cache.default' => 'array']);
        Cache::put('gamemonetize.embeddable.all', [
            ['id' => 'alpha', 'name' => 'Alpha Racer', 'category' => 'Racing', 'image' => null, 'url' => 'https://html5.gamemonetize.co/alpha/'],
            ['id' => 'beta', 'name' => 'Beta Puzzle', 'category' => 'Puzzle', 'image' => null, 'url' => 'https://html5.gamemonetize.co/beta/'],
        ], 60);

        $this->get('/')->assertOk()
            ->assertSee(route('playable.index', ['category' => 'Racing']), false)
            ->assertDontSee('aria-label="Hero slides"', false);
        $this->get('/play-games?search=racer&category=Racing')
            ->assertOk()->assertSee('Alpha Racer')->assertDontSee('Beta Puzzle');
        $this->get('/play-games?search=missing')->assertOk()->assertSee('No games are available');
        Http::assertNothingSent();
    }

    public function test_unavailable_game_feed_is_cached_briefly(): void
    {
        config(['cache.default' => 'array']);
        Cache::flush();
        Http::fake(['gamemonetize.com/rssfeed.php*' => Http::response('Unavailable', 503)]);
        $catalog = app(\App\Services\EmbeddableGameCatalog::class);
        $this->assertSame([], $catalog->all());
        $this->assertSame([], $catalog->all());
        Http::assertSentCount(1);
    }

    public function test_embeddable_catalog_excludes_casino_games_even_from_old_cache(): void
    {
        config(['cache.default' => 'array']);
        Cache::put('gamemonetize.embeddable.all', [
            ['id' => 'puzzle', 'name' => 'Puzzle Adventure', 'category' => 'Puzzle'],
            ['id' => 'roulette', 'name' => 'Lucky Roulette', 'category' => 'Casino'],
            ['id' => 'slots', 'name' => 'Vegas Slots', 'category' => 'Arcade'],
        ], 60);
        $games = app(\App\Services\EmbeddableGameCatalog::class)->all();
        $this->assertSame(['puzzle'], array_column($games, 'id'));
        $this->assertNull(app(\App\Services\EmbeddableGameCatalog::class)->find('roulette'));
        Http::assertNothingSent();
    }

    public function test_every_controller_route_has_a_resolvable_target(): void
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $action = $route->getActionName();

            if ($action === 'Closure' || !str_contains($action, '@')) {
                continue;
            }

            [$controller, $method] = explode('@', $action, 2);

            $this->assertTrue(class_exists($controller), "Missing controller for [{$route->uri()}]: {$controller}");
            $this->assertTrue(method_exists($controller, $method), "Missing action for [{$route->uri()}]: {$action}");
        }
    }

    public function test_public_catalog_and_auth_pages_render_with_cached_casino_data(): void
    {
        $payload = json_encode(['content' => ['gameList' => []]]);

        foreach ([
            'casino_raw_response',
            'casino_bonus_response',
            'casino_cashback_response',
            'casino_vip_response',
        ] as $cacheKey) {
            Cache::put($cacheKey, $payload, now()->addHour());
        }

        foreach ([
            route('games.viewIndex'),
            route('free.games.index'),
            route('login.form'),
            route('register'),
            route('password.request'),
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_games_page_does_not_crash_when_casino_api_host_is_missing(): void
    {
        config(['casino.host' => null]);
        Cache::forget('casino_raw_response');

        $this->get(route('games.viewIndex'))->assertOk()->assertSee('Play');
    }

    public function test_user_routes_require_authentication_and_notifications_are_owner_scoped(): void
    {
        foreach ([
            route('user.dashboard'),
            route('user.profile'),
            route('user.games.viewFavorites'),
        ] as $uri) {
            $this->get($uri)->assertRedirect(route('login.form'));
        }

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownNotification = Notification::create([
            'user_id' => $user->id,
            'title' => 'Own notification',
            'message' => 'Message',
        ]);
        $otherNotification = Notification::create([
            'user_id' => $otherUser->id,
            'title' => 'Other notification',
            'message' => 'Message',
        ]);

        $this->actingAs($user)
            ->from(route('user.notifications.index'))
            ->post(route('user.notifications.read', $ownNotification))
            ->assertRedirect(route('user.notifications.index'));

        $this->assertDatabaseHas('notifications', ['id' => $ownNotification->id, 'is_read' => 1]);

        $this->actingAs($user)
            ->post(route('user.notifications.read', $otherNotification))
            ->assertNotFound();
    }

    public function test_oauth_provider_configuration_keys_are_present(): void
    {
        $this->assertArrayHasKey('client_id', config('services.google'));
        $this->assertArrayHasKey('client_secret', config('services.google'));
        $this->assertArrayHasKey('redirect', config('services.google'));
        $this->assertArrayHasKey('client_id', config('services.facebook'));
        $this->assertArrayHasKey('client_secret', config('services.facebook'));
        $this->assertArrayHasKey('redirect', config('services.facebook'));
    }

    public function test_authenticated_user_core_pages_do_not_raise_runtime_errors(): void
    {
        $user = User::factory()->create([
            'google2fa_status' => false,
            'is_banned' => false,
        ]);

        foreach ([
            route('user.dashboard'),
            route('user.profile'),
            route('user.games.viewFavorites'),
            route('user.lottaries.view'),
        ] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk()->assertSee('fg-topbar')->assertSee('site-footer');
        }
    }

    public function test_admin_login_and_core_read_pages_are_wired_to_the_admin_guard(): void
    {
        $admin = Admin::create([
            'name' => 'Smoke Admin',
            'email' => 'smoke-admin@example.test',
            'password' => Hash::make('password'),
        ]);

        $payload = json_encode(['content' => ['gameList' => []]]);
        foreach ([
            'casino_raw_response',
            'casino_bonus_response',
            'casino_cashback_response',
            'casino_vip_response',
        ] as $cacheKey) {
            Cache::put($cacheKey, $payload, now()->addHour());
        }

        $this->get(route('admin.login'))->assertOk();

        foreach ([
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.games.index'),
            route('admin.games_categories.index'),
            route('admin.lottaries.index'),
        ] as $uri) {
            $this->actingAs($admin, 'admin')->get($uri)->assertOk()->assertSee('fg-topbar')->assertSee('site-footer');
        }
    }
}
