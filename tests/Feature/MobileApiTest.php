<?php

namespace Tests\Feature;

use App\Models\MobileApiToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'cache.limiter' => 'array']);
        \Illuminate\Support\Facades\RateLimiter::clearResolvedInstance(\Illuminate\Cache\RateLimiter::class);
        $this->app->forgetInstance(\Illuminate\Cache\RateLimiter::class);
        $this->app->getProvider(\App\Providers\AppServiceProvider::class)->boot();
        Cache::flush();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.random_int(1, 254)]);
        Http::preventStrayRequests();
    }

    public function test_playable_filter_runs_before_pagination(): void
    {
        $this->mock(\App\Services\MobileGameCatalog::class, function ($mock) {
            $mock->shouldReceive('list')->with('local')->andReturn([
                ['id' => '1', 'title' => 'Download only', 'playable' => false],
                ['id' => '2', 'title' => 'Browser puzzle', 'playable' => true],
            ]);
        });
        $this->getJson('/api/v1/games?provider=local&playable_only=1&per_page=1')
            ->assertOk()->assertJsonPath('data.0.id', '2')
            ->assertJsonPath('meta.total', 1)->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/games?provider=local&per_page=1')
            ->assertOk()->assertJsonPath('meta.total', 2);
    }

    private function player(array $extra = []): User
    {
        return User::factory()->create([...['is_verified' => true, 'password' => Hash::make('correct-password')], ...$extra]);
    }

    private function login(User $user, array $extra = []): string
    {
        return $this->postJson('/api/v1/auth/login', [...['email' => $user->email, 'password' => 'correct-password', 'device_name' => 'Test phone'], ...$extra])->assertOk()->json('data.access_token');
    }

    public function test_tokens_are_hashed_rotated_revoked_and_json_is_forced(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        $this->assertDatabaseHas('mobile_api_tokens', ['token_hash' => hash('sha256', $token)]);
        $this->assertDatabaseMissing('mobile_api_tokens', ['token_hash' => $token]);
        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $u->id)->assertJsonMissingPath('data.password')->assertJsonMissingPath('data.google2fa_secret');
        $new = $this->withToken($token)->postJson('/api/v1/auth/refresh')->assertOk()->json('data.access_token');
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->withToken($new)->postJson('/api/v1/auth/logout')->assertOk();
        $this->withToken($new)->get('/api/v1/me')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
    }

    public function test_registration_and_expiring_email_verification(): void
    {
        Mail::fake();
        $this->postJson('/api/v1/auth/register', ['name' => 'New player', 'username' => 'newplayer', 'email' => 'New@Example.com', 'password' => 'correct-password', 'password_confirmation' => 'correct-password'])->assertCreated();
        $u = User::where('email', 'new@example.com')->firstOrFail();
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'correct-password', 'device_name' => 'Phone'])->assertForbidden();
        Cache::put('mobile.verify.'.hash('sha256', $u->email), hash('sha256', '123456'), now()->addMinutes(10));
        $this->postJson('/api/v1/auth/verify-email', ['email' => $u->email, 'code' => '000000'])->assertUnprocessable();
        $this->postJson('/api/v1/auth/verify-email', ['email' => $u->email, 'code' => '123456'])->assertOk();
        $this->postJson('/api/v1/auth/verify-email', ['email' => $u->email, 'code' => '123456'])->assertUnprocessable();
        $this->assertTrue($u->refresh()->is_verified);
    }

    public function test_banned_expired_and_security_changed_tokens_fail(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        $u->update(['is_banned' => true]);
        $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
        $u->update(['is_banned' => false, 'password' => Hash::make('changed-password')]);
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $u->update(['password' => Hash::make('correct-password')]);
        $token = $this->login($u);
        MobileApiToken::where('user_id', $u->id)->update(['expires_at' => now()->subMinute()]);
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_two_factor_login_and_replay_protection(): void
    {
        $g = new Google2FA;
        $secret = $g->generateSecretKey();
        $u = $this->player(['google2fa_status' => true, 'google2fa_secret' => $secret]);
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'correct-password', 'device_name' => 'Phone'])->assertUnprocessable()->assertJsonValidationErrors('otp');
        $otp = $g->getCurrentOtp($secret);
        $this->login($u, ['otp' => $otp]);
        $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'correct-password', 'device_name' => 'Phone', 'otp' => $otp])->assertUnprocessable();
    }

    public function test_games_pagination_filters_and_browser_history(): void
    {
        $games = [];
        for ($i = 1; $i <= 65; $i++) {
            $games[] = ['id' => 'g'.$i, 'name' => 'Puzzle '.$i, 'category' => 'Puzzle', 'image' => null, 'url' => 'https://html5.gamemonetize.co/g'.$i.'/'];
        }
        $games[] = ['id' => 'casino', 'name' => 'Casino poker', 'category' => 'Casino', 'image' => null, 'url' => 'https://html5.gamemonetize.co/casino/'];
        Cache::put('gamemonetize.embeddable.all', $games);
        $this->getJson('/api/v1/games')->assertOk()->assertJsonCount(30, 'data')->assertJsonPath('meta.total', 65)->assertJsonPath('meta.next_page', 2);
        $this->getJson('/api/v1/games?page=3')->assertJsonCount(5, 'data')->assertJsonPath('meta.has_more', false);
        $this->getJson('/api/v1/games?q=Puzzle%2065')->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/games?per_page=31')->assertUnprocessable();
        $this->getJson('/api/v1/games/gamemonetize/casino')->assertNotFound();
        $u = $this->player();
        $token = $this->login($u);
        $this->withToken($token)->putJson('/api/v1/me/games/gamemonetize/g1/favorite')->assertOk();
        $this->withToken($token)->putJson('/api/v1/me/games/gamemonetize/g1/favorite')->assertOk();
        $this->withToken($token)->getJson('/api/v1/me/favorites')->assertJsonCount(1, 'data');
        $this->withToken($token)->postJson('/api/v1/me/games/gamemonetize/g1/played')->assertOk();
        $this->withToken($token)->getJson('/api/v1/me/recent')->assertJsonCount(1, 'data');
        $this->withToken($token)->getJson('/api/v1/me/dashboard')->assertJsonPath('data.games_played', 1);
        $this->withToken($token)->deleteJson('/api/v1/me/recent')->assertOk();
        $this->withToken($token)->getJson('/api/v1/me/recent')->assertJsonCount(0, 'data');
    }

    public function test_owner_scoping_and_profile_mass_assignment(): void
    {
        $a = $this->player();
        $b = $this->player();
        $token = $this->login($a);
        $notification = Notification::create(['user_id' => $b->id, 'title' => 'Private', 'message' => 'Secret', 'is_read' => false]);
        $this->withToken($token)->patchJson('/api/v1/me/notifications/'.$notification->id.'/read')->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/me/notifications')->assertJsonCount(0, 'data');
        Notification::create(['user_id' => $a->id, 'title' => 'Unread', 'message' => 'Own message', 'is_read' => false]);
        Notification::create(['user_id' => $a->id, 'title' => 'Read', 'message' => 'Own message', 'is_read' => true]);
        $this->withToken($token)->getJson('/api/v1/me/dashboard')->assertJsonPath('data.unread_notifications', 1);
        $this->withToken($token)->patchJson('/api/v1/me/notifications/read-all')->assertOk();
        $this->withToken($token)->getJson('/api/v1/me/dashboard')->assertJsonPath('data.unread_notifications', 0);
        $other = $this->login($b);
        $id = MobileApiToken::where('user_id', $b->id)->value('id');
        $this->withToken($token)->deleteJson('/api/v1/me/devices/'.$id)->assertNotFound();
        $this->withToken($token)->patchJson('/api/v1/me', ['name' => 'Updated', 'mobile_number' => '+880123456789', 'balance' => 99999, 'is_verified' => false, 'google2fa_status' => true])->assertOk()->assertJsonPath('data.name', 'Updated')->assertJsonPath('data.mobile_number', '+880123456789');
        $this->withToken($token)->getJson('/api/v1/me')->assertJsonPath('data.mobile_number', '+880123456789');
        $this->withToken($token)->getJson('/api/v1/me/devices')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.current', true);
        $this->assertTrue($a->refresh()->is_verified);
        $this->assertFalse((bool) $a->google2fa_status);
        $this->withToken($other)->getJson('/api/v1/me/favorites')->assertJsonCount(0, 'data');
    }

    public function test_password_reset_revokes_tokens_and_preferences_work(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        $this->withToken($token)->putJson('/api/v1/me/preferences', ['preferred_genres' => ['Puzzle'], 'preferred_platforms' => ['Browser']])->assertOk()->assertJsonPath('data.preferred_genres.0', 'Puzzle');
        $reset = Password::broker()->createToken($u);
        $this->postJson('/api/v1/auth/reset-password', ['email' => $u->email, 'token' => $reset, 'password' => 'replacement-password', 'password_confirmation' => 'replacement-password'])->assertOk();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/reset-password', ['email' => $u->email, 'token' => $reset, 'password' => 'replacement-password', 'password_confirmation' => 'replacement-password'])->assertUnprocessable();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'missing@example.com', 'password' => 'bad', 'device_name' => 'Phone'])->assertUnprocessable();
        }
        $this->postJson('/api/v1/auth/login', ['email' => 'missing@example.com', 'password' => 'bad', 'device_name' => 'Phone'])->assertStatus(429);
    }

    public function test_two_factor_setup_and_account_closure(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        $secret = $this->withToken($token)->postJson('/api/v1/me/2fa/setup', ['current_password' => 'correct-password'])->assertOk()->json('data.secret');
        $otp = (new Google2FA)->getCurrentOtp($secret);
        $this->withToken($token)->postJson('/api/v1/me/2fa/enable', ['otp' => $otp])->assertOk();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $new = $this->login($u->refresh(), ['otp' => $otp]);
        $this->withToken($new)->deleteJson('/api/v1/me', ['current_password' => 'wrong', 'otp' => $otp])->assertUnprocessable();
        $this->withToken($new)->deleteJson('/api/v1/me', ['current_password' => 'correct-password', 'otp' => $otp])->assertOk();
        $this->assertSoftDeleted($u);
        $this->withToken($new)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_catalog_favorites_are_shared_with_website_library_and_use_native_ids(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        config(['games.freetogame.base_url' => 'https://freetogame.test/api']);
        $game = ['id' => 123, 'title' => 'Free Adventure', 'genre' => 'Adventure', 'platform' => 'PC'];
        Http::fake(['freetogame.test/api/games*' => Http::response([$game], 200), 'freetogame.test/api/game*' => Http::response($game, 200)]);
        $this->getJson('/api/v1/games?provider=freetogame')->assertJsonPath('data.0.id', '123')->assertJsonPath('data.0.playable', false);
        $this->withToken($token)->putJson('/api/v1/me/games/freetogame/123/favorite')->assertOk();
        $this->assertDatabaseHas('catalog_saved_games', ['user_id' => $u->id, 'provider_game_id' => '123']);
        $this->withToken($token)->getJson('/api/v1/me/favorites')->assertJsonCount(1, 'data')->assertJsonPath('data.0.snapshot.title', 'Free Adventure');
        app(\App\Services\CatalogPersonalizationService::class)->remove($u, 'freetogame', '123');
        $this->withToken($token)->getJson('/api/v1/me/favorites')->assertJsonCount(0, 'data');
    }

    public function test_lottery_ticket_ownership_and_read_only_capability(): void
    {
        $a = $this->player();
        $b = $this->player();
        $token = $this->login($a);
        $lottery = \App\Models\Lottary::create(['title' => 'Draw', 'price' => 1, 'prize_number' => 1, 'draw_date' => now()->addDay()]);
        $ticket = \App\Models\LotteryTransaction::create(['user_id' => $b->id, 'lottary_id' => $lottery->id, 'ticket_number' => 'PRIVATE-TICKET', 'transaction_number' => 'ABC123', 'amount' => 1]);
        $this->withToken($token)->getJson('/api/v1/me/lottery/tickets')->assertJsonCount(0, 'data');
        $this->withToken($token)->getJson('/api/v1/me/lottery/tickets/'.$ticket->id)->assertNotFound();
        $this->withToken($token)->getJson('/api/v1/lotteries')->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/config')->assertJsonPath('data.features.lottery_purchase', false)->assertJsonPath('data.features.casino', false);
        $this->withToken($token)->postJson('/api/v1/lotteries/'.$lottery->id.'/buy')->assertNotFound();
    }

    public function test_verification_expires_and_password_change_revokes_every_mobile_device(): void
    {
        $u = $this->player();
        $token = $this->login($u);
        $second = $this->login($u);
        $this->withToken($token)->putJson('/api/v1/me/password', ['current_password' => 'correct-password', 'password' => 'changed-secret', 'password_confirmation' => 'changed-secret'])->assertOk();
        $this->withToken($second)->getJson('/api/v1/me')->assertUnauthorized();
        $unverified = $this->player(['is_verified' => false]);
        Cache::put('mobile.verify.'.hash('sha256', $unverified->email), hash('sha256', '123456'), now()->addMinutes(10));
        $this->travel(11)->minutes();
        $this->postJson('/api/v1/auth/verify-email', ['email' => $unverified->email, 'code' => '123456'])->assertUnprocessable();
        $this->assertFalse($unverified->refresh()->is_verified);
    }
}
