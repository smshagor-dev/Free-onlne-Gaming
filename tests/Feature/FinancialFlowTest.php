<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Gateway;
use App\Models\Lottary;
use App\Models\LotteryTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDeposit;
use App\Models\UserWithdrew;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FinancialFlowTest extends TestCase
{
    use RefreshDatabase;







    public function test_lottery_purchase_requires_balance_and_ticket_access_is_owner_scoped(): void
    {
        Mail::fake();
        $lottery = Lottary::create([
            'title' => 'Smoke Lottery',
            'price' => 20,
            'prize_number' => 1,
            'draw_date' => now()->addDay()->toDateString(),
        ]);
        $poorUser = $this->financialUser(['balance' => 10]);

        $this->actingAs($poorUser)
            ->from(route('user.lottaries.show', $lottery))
            ->post(route('user.lottaries.buy', $lottery))
            ->assertRedirect(route('user.lottaries.show', $lottery));

        $this->assertDatabaseCount('lottery_transactions', 0);
        $buyer = $this->financialUser(['balance' => 50]);

        $this->actingAs($buyer)
            ->post(route('user.lottaries.buy', $lottery))
            ->assertRedirect(route('user.lottaries.my'));

        $ticket = LotteryTransaction::firstOrFail();
        $this->assertEquals(30.0, (float) $buyer->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $buyer->id,
            'transaction_number' => $ticket->transaction_number,
            'transaction_type' => 'lottary Buy',
        ]);

        $this->actingAs($poorUser)
            ->get(route('user.lottaries.view_ticket', $ticket->ticket_number))
            ->assertNotFound();

        $this->actingAs($buyer)
            ->get(route('user.lottaries.view_ticket', $ticket->ticket_number))
            ->assertOk();
    }

    private function financialUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'google2fa_status' => false,
            'is_banned' => false,
            'kyc_verified' => true,
            'balance' => 0,
            'available_balance' => 0,
        ], $overrides));
    }

    private function gateway(string $name): Gateway
    {
        return Gateway::create([
            'name' => $name,
            'currency' => 'USD',
            'symbol' => '$',
            'min_amount' => 1,
            'max_amount' => 10000,
            'status' => true,
        ]);
    }
}
