<?php

namespace Tests\Feature;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasinoLaunchTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_launch_uses_provider_response_and_records_play_session(): void
    {
        config([
            'casino.host' => 'https://casino-provider.test/',
            'casino.providers.default.hall' => 'hall-1',
            'casino.providers.default.key' => 'key-1',
        ]);

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'status' => 'success',
                'content' => [
                    'game' => [
                        'name' => 'Mocked Slots',
                        'url' => 'https://casino-provider.test/game/session',
                        'iframe' => '1',
                        'withoutFrame' => '0',
                        'exitButton' => 1,
                    ],
                    'gameRes' => ['sessionId' => 'provider-session-1'],
                ],
            ])),
        ]);
        $this->app->instance(Client::class, new Client(['handler' => HandlerStack::create($mock)]));

        $user = User::factory()->create([
            'google2fa_status' => false,
            'is_banned' => false,
            'user_id' => 'casino-player-1',
        ]);

        $this->actingAs($user)
            ->get(route('casino.play', ['gameId' => 700, 'name' => 'Mocked Slots', 'demo' => 0]))
            ->assertOk()
            ->assertSee('casino-provider.test/game/session');

        $this->assertDatabaseHas('last_play', [
            'user_id' => $user->id,
            'game_id' => 700,
            'last_play' => 1,
        ]);
        $this->assertDatabaseHas('casino_game_sessions', [
            'user_id' => $user->id,
            'session_id' => 'provider-session-1',
            'game_name' => 'Mocked Slots',
        ]);
    }
}
