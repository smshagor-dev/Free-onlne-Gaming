<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RuntimeSmokeTest extends TestCase
{
    use RefreshDatabase;

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
            route('casino.index'),
            route('casino.bonus.index'),
            route('casino.cashback.index'),
            route('casino.vip.bonus.index'),
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

        $this->get(route('games.viewIndex'))
            ->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'No cached casino data found. Please refresh first.',
            ]);
    }

    public function test_user_routes_require_authentication_and_notifications_are_owner_scoped(): void
    {
        foreach ([
            route('user.dashboard'),
            route('user.profile'),
            route('user.games.viewFavorites'),
            route('user.deposit.index'),
            route('user.withdrew.index'),
            route('user.kyc.index'),
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
            route('user.deposit.index'),
            route('user.withdrew.index'),
            route('user.kyc.create'),
            route('bonus.page'),
            route('my.bonus'),
            route('user.lottaries.view'),
        ] as $uri) {
            $this->actingAs($user)->get($uri)->assertOk();
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
            route('admin.kyc.index'),
            route('admin.bonuses.index'),
            route('admin.lottaries.index'),
            route('admin.casino.view'),
            route('admin.casino.bonuscache.view'),
            route('admin.casino.cashbackcache.view'),
            route('admin.casino.vipcache.view'),
        ] as $uri) {
            $this->actingAs($admin, 'admin')->get($uri)->assertOk();
        }
    }
}
