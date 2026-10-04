<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BonusUser;
use App\Models\CashbackSetting;
use App\Models\Gateway;
use App\Models\Level;
use App\Models\ReferralSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDeposit;
use App\Models\VipBonus;
use App\Models\DepositSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BonusCashbackReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_bonus_is_granted_once_with_an_audit_transaction(): void
    {
        Mail::fake();
        DepositSetting::create([
            'title' => 'Welcome',
            'bonus_type' => 'Welcome Bonus',
            'minimum_bonus' => 15,
        ]);
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('user.bonus.claim.welcome'))
            ->assertRedirect(route('my.bonus'));
        $this->actingAs($user)
            ->post(route('user.bonus.claim.welcome'))
            ->assertRedirect(route('my.bonus'));

        $this->assertEquals(15.0, (float) $user->fresh()->bonus_balance);
        $this->assertSame(1, BonusUser::where('user_id', $user->id)->where('bonus_type', 'Welcome Bonus')->count());
        $this->assertSame(1, Transaction::where('user_id', $user->id)->where('transaction_type', 'bonus')->count());
    }

    public function test_cashback_claim_creates_one_credit_and_respects_claim_limit(): void
    {
        Mail::fake();
        $level = Level::create(['points' => 0, 'level' => 1]);
        $user = $this->user(['level_id' => $level->id, 'cashback' => 0]);
        CashbackSetting::create([
            'level_id' => $level->id,
            'cashback_percentage' => 10,
            'lose_calculation' => 1,
            'wager' => 1,
            'playing_time' => 24,
            'activation_days' => json_encode([now()->format('l')]),
            'maximum_claim' => 1,
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'transaction_number' => 'cashback-loss',
            'transaction_type' => 'lottary',
            'trx' => '-',
            'amount' => 100,
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->postJson(route('user.cashback.claim'))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.cashback_amount', 10);

        $this->actingAs($user)
            ->postJson(route('user.cashback.claim'))
            ->assertOk()
            ->assertJsonPath('status', false);

        $this->assertEquals(10.0, (float) $user->fresh()->cashback);
        $this->assertSame(1, Transaction::where('user_id', $user->id)->where('transaction_type', 'cashback')->count());
    }

    public function test_referral_commission_uses_referred_deposits_and_creates_a_transaction(): void
    {
        $gateway = Gateway::create([
            'name' => 'Referral Gateway',
            'currency' => 'USD',
            'symbol' => '$',
            'min_amount' => 1,
            'max_amount' => 10000,
            'status' => true,
        ]);
        $referrer = $this->user(['referral_code' => 'REF-CODE', 'balance' => 0]);
        $referred = $this->user(['referrer' => 'REF-CODE']);
        ReferralSetting::create([
            'level' => 'Level 1',
            'register_user' => 1,
            'total_deposit' => 50,
            'commission' => 10,
        ]);
        UserDeposit::create([
            'user_id' => $referred->id,
            'gateway_id' => $gateway->id,
            'amount' => 100,
            'status' => 'approved',
            'transaction_number' => 'referred-deposit',
        ]);

        $this->actingAs($referrer)
            ->postJson(route('user.referral.collectBalance'))
            ->assertOk()
            ->assertJsonPath('commission_amount', 10);

        $this->assertEquals(10.0, (float) $referrer->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $referrer->id,
            'transaction_type' => 'Referral',
            'amount' => 10,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_assign_a_vip_bonus_with_a_financial_audit_record(): void
    {
        Mail::fake();
        $admin = Admin::create([
            'name' => 'VIP Admin',
            'email' => 'vip-admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $user = $this->user(['vip_bonus' => 0]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.vipbonuses.store'), [
                'user_id' => $user->id,
                'bonus_amount' => 25,
                'playing_time' => 24,
                'wager' => 5,
            ])->assertRedirect(route('admin.vipbonuses.index'));

        $this->assertEquals(25.0, (float) $user->fresh()->vip_bonus);
        $this->assertSame(1, VipBonus::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'transaction_type' => 'VIP Bonus',
            'amount' => 25,
            'status' => 'approved',
        ]);
    }

    private function user(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'google2fa_status' => false,
            'is_banned' => false,
            'kyc_verified' => true,
            'balance' => 0,
        ], $overrides));
    }
}
