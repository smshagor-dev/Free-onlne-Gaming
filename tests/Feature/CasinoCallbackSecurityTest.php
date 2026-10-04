<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasinoCallbackSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'casino.currency' => 'BDT',
            'casino.providers.default.hall' => 'hall-1',
            'casino.providers.default.key' => 'secret-1',
            'casino.providers.default.balance_fields' => ['balance', 'available_balance'],
            'casino.providers.default.win_field' => 'available_balance',
            'casino.providers.default.refund_field' => 'balance',
        ]);
    }

    public function test_callback_rejects_invalid_provider_credentials(): void
    {
        $user = User::factory()->create([
            'user_id' => 'player-1',
            'balance' => 100,
            'available_balance' => 50,
        ]);

        $response = $this->postJson('/api/casino/player', $this->payload(['key' => 'wrong']));

        $response->assertStatus(403)->assertJson([
            'status' => 'fail',
            'error' => 'invalid_credentials',
        ]);

        $user->refresh();
        $this->assertEquals(100.0, (float) $user->balance);
        $this->assertEquals(50.0, (float) $user->available_balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_duplicate_callback_is_idempotent(): void
    {
        $user = User::factory()->create([
            'user_id' => 'player-1',
            'balance' => 100,
            'available_balance' => 50,
        ]);

        $this->postJson('/api/casino/player', $this->payload())->assertOk()->assertJson([
            'status' => 'success',
            'balance' => '135.00',
        ]);

        $this->postJson('/api/casino/player', $this->payload())->assertOk()->assertJson([
            'status' => 'success',
            'balance' => '135.00',
        ]);

        $user->refresh();
        $this->assertEquals(75.0, (float) $user->balance);
        $this->assertEquals(60.0, (float) $user->available_balance);
        $this->assertSame(1, Transaction::where('provider_transaction_id', 'trade-1')->count());
    }

    public function test_callback_does_not_overdraw_balance(): void
    {
        $user = User::factory()->create([
            'user_id' => 'player-1',
            'balance' => 10,
            'available_balance' => 0,
        ]);

        $this->postJson('/api/casino/player', $this->payload(['bet' => 25, 'win' => 0]))
            ->assertOk()
            ->assertJson([
                'status' => 'fail',
                'error' => 'fail_balance',
            ]);

        $user->refresh();
        $this->assertEquals(10.0, (float) $user->balance);
        $this->assertEquals(0.0, (float) $user->available_balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_callback_reports_balance_and_processes_refunds_once(): void
    {
        $user = User::factory()->create([
            'user_id' => 'player-1',
            'balance' => 100,
            'available_balance' => 50,
        ]);

        $this->postJson('/api/casino/player', [
            'cmd' => 'getBalance',
            'hall' => 'hall-1',
            'key' => 'secret-1',
            'login' => 'player-1',
        ])->assertOk()->assertJson([
            'status' => 'success',
            'balance' => '150.00',
        ]);

        $payload = $this->payload([
            'bet' => 20,
            'win' => 0,
            'betInfo' => 'refund',
            'tradeId' => 'refund-1',
        ]);

        $this->postJson('/api/casino/player', $payload)->assertOk()->assertJson([
            'status' => 'success',
            'balance' => '170.00',
        ]);
        $this->postJson('/api/casino/player', $payload)->assertOk()->assertJson([
            'status' => 'success',
            'balance' => '170.00',
        ]);

        $this->assertEquals(120.0, (float) $user->fresh()->balance);
        $this->assertEquals(50.0, (float) $user->fresh()->available_balance);
        $this->assertDatabaseHas('transactions', [
            'provider_transaction_id' => 'refund-1',
            'provider_action' => 'refund',
            'status' => 'approved',
        ]);
        $this->assertSame(1, Transaction::where('provider_transaction_id', 'refund-1')->count());
    }

    public function test_invalid_callback_payloads_and_methods_make_no_financial_changes(): void
    {
        $user = User::factory()->create([
            'user_id' => 'player-1',
            'balance' => 100,
            'available_balance' => 50,
        ]);

        $this->getJson('/api/casino/player')->assertStatus(405)->assertJson([
            'status' => 'fail',
            'error' => 'method_not_allowed',
        ]);

        $payload = $this->payload();
        unset($payload['gameId']);
        $this->postJson('/api/casino/player', $payload)->assertStatus(400)->assertJson([
            'status' => 'fail',
            'error' => 'invalid_payload',
        ]);

        $user->refresh();
        $this->assertEquals(100.0, (float) $user->balance);
        $this->assertEquals(50.0, (float) $user->available_balance);
        $this->assertDatabaseCount('transactions', 0);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'cmd' => 'writeBet',
            'hall' => 'hall-1',
            'key' => 'secret-1',
            'login' => 'player-1',
            'bet' => 25,
            'win' => 10,
            'tradeId' => 'trade-1',
            'gameId' => 100,
            'sessionId' => 200,
        ], $overrides);
    }
}
