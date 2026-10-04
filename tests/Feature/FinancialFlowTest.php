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

    public function test_deposit_creates_consistent_owner_scoped_records(): void
    {
        Mail::fake();
        $user = $this->financialUser();
        $gateway = $this->gateway('Bank');

        $this->actingAs($user)
            ->post(route('user.deposit.store'), [
                'gateway_id' => $gateway->id,
                'amount' => 25.50,
            ])->assertRedirect(route('user.deposit.history'));

        $deposit = UserDeposit::firstOrFail();
        $transaction = Transaction::where('user_deposit_id', $deposit->id)->firstOrFail();

        $this->assertSame($user->id, $deposit->user_id);
        $this->assertSame('pending', $deposit->status);
        $this->assertSame($deposit->transaction_number, $transaction->transaction_number);
        $this->assertSame('pending', $transaction->status);
        $this->assertEquals(25.50, (float) $transaction->amount);

        $otherDeposit = UserDeposit::create([
            'user_id' => User::factory()->create()->id,
            'gateway_id' => $gateway->id,
            'amount' => 99,
            'status' => 'pending',
            'transaction_number' => 'other-deposit',
        ]);

        $this->actingAs($user)
            ->get(route('user.deposit.history'))
            ->assertOk()
            ->assertSee($deposit->transaction_number)
            ->assertDontSee($otherDeposit->transaction_number);
    }

    public function test_withdrawal_rejects_insufficient_balance_and_keeps_financial_records_consistent(): void
    {
        Mail::fake();
        $gateway = $this->gateway('Wallet');
        $insufficientUser = $this->financialUser(['available_balance' => 5]);

        $this->actingAs($insufficientUser)
            ->post(route('user.withdrew.store'), ['gateway_id' => $gateway->id, 'amount' => 10])
            ->assertRedirect(route('user.withdrew.history'));

        $this->assertDatabaseCount('user_withdrew', 0);
        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals(5.0, (float) $insufficientUser->fresh()->available_balance);

        $user = $this->financialUser(['available_balance' => 100]);
        $this->actingAs($user)
            ->post(route('user.withdrew.store'), ['gateway_id' => $gateway->id, 'amount' => 30])
            ->assertRedirect(route('user.withdrew.history'));

        $withdrawal = UserWithdrew::firstOrFail();
        $transaction = Transaction::where('user_withdrew_id', $withdrawal->id)->firstOrFail();
        $this->assertEquals(70.0, (float) $user->fresh()->available_balance);
        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame('pending', $transaction->status);
        $this->assertSame($withdrawal->transaction_number, $transaction->transaction_number);
    }

    public function test_admin_financial_status_updates_are_idempotent(): void
    {
        Mail::fake();
        $admin = Admin::create([
            'name' => 'Finance Admin',
            'email' => 'finance-admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $gateway = $this->gateway('Finance Gateway');
        $user = $this->financialUser(['balance' => 10, 'available_balance' => 80]);

        $deposit = UserDeposit::create([
            'user_id' => $user->id,
            'gateway_id' => $gateway->id,
            'amount' => 25,
            'status' => 'pending',
            'transaction_number' => 'deposit-approval',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'user_deposit_id' => $deposit->id,
            'amount' => 25,
            'status' => 'pending',
            'transaction_number' => 'deposit-approval',
            'transaction_type' => 'Deposit',
        ]);

        foreach (range(1, 2) as $_) {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.user.deposits.update-status', $deposit), ['status' => 'approved'])
                ->assertRedirect();
        }

        $this->assertEquals(35.0, (float) $user->fresh()->balance);
        $this->assertDatabaseHas('transactions', ['user_deposit_id' => $deposit->id, 'status' => 'approved']);

        $withdrawal = UserWithdrew::create([
            'user_id' => $user->id,
            'gateway_id' => $gateway->id,
            'amount' => 30,
            'status' => 'pending',
            'transaction_number' => 'withdraw-reject',
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'user_withdrew_id' => $withdrawal->id,
            'amount' => 30,
            'status' => 'pending',
            'transaction_number' => 'withdraw-reject',
            'transaction_type' => 'Withdrew',
        ]);

        foreach (range(1, 2) as $_) {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.user.withdrews.update-status', $withdrawal), ['status' => 'reject'])
                ->assertRedirect();
        }

        $this->assertEquals(110.0, (float) $user->fresh()->available_balance);
        $this->assertDatabaseHas('transactions', ['user_withdrew_id' => $withdrawal->id, 'status' => 'reject']);
    }

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
