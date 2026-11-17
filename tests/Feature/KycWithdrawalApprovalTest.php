<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KycWithdrawalApprovalTest extends TestCase
{
    use RefreshDatabase;

    private const ETH = '0xaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const TRON = 'T'.'A1B2C3D4E5F6G7H8J9KLMNPQRSTUVWXYZabc';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        config(['withdrawals' => [
            'enabled' => true,
            'currency' => 'USD',
            'minimum' => '10.00',
            'maximum' => '10000.00',
        ]]);
    }

    private function user(bool $kyc = true, string $balance = '200.00', string $available = '180.00'): User
    {
        return User::factory()->create([
            'kyc_verified' => $kyc,
            'is_banned' => false,
            'registration_type' => 'email',
            'google2fa_status' => false,
            'currency' => 'USD',
            'balance' => $balance,
            'available_balance' => $available,
        ]);
    }

    private function admin(): Admin
    {
        return Admin::query()->create([
            'name' => 'Withdrawal Reviewer',
            'email' => 'withdraw-reviewer@example.test',
            'password' => Hash::make('testpassword'),
        ]);
    }

    private function requestData(string $amount = '55.25'): array
    {
        return [
            'amount' => $amount,
            'asset' => 'USDT',
            'network' => 'erc20',
            'destination' => self::ETH,
        ];
    }

    private function createRequest(User $user, string $amount = '55.25'): WithdrawalRequest
    {
        $this->actingAs($user)
            ->post(route('user.withdrawals.store'), $this->requestData($amount))
            ->assertRedirect(route('user.withdrawals.index'))
            ->assertSessionHas('success');

        return WithdrawalRequest::query()->where('user_id', $user->id)->firstOrFail();
    }

    public function test_withdrawals_require_authentication_and_approved_kyc(): void
    {
        $this->get(route('user.withdrawals.index'))->assertRedirect(route('login.form'));
        $this->post(route('user.withdrawals.store'), $this->requestData())
            ->assertRedirect(route('login.form'));

        $user = $this->user(false);
        $this->actingAs($user)->get(route('user.withdrawals.index'))
            ->assertOk()
            ->assertSee('KYC verification is required')
            ->assertSee('disabled', false);

        $this->post(route('user.withdrawals.store'), $this->requestData())
            ->assertSessionHasErrors('kyc');

        $this->assertDatabaseCount('withdrawal_requests', 0);
        $this->assertSame('180.00', (string) $user->fresh()->available_balance);
    }

    public function test_approved_kyc_reserves_available_money_and_encrypts_wallet_destination(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);

        $this->assertSame('pending', $record->status);
        $this->assertSame('55.25', $record->amount);
        $this->assertSame('USDT', $record->asset);
        $this->assertSame('erc20', $record->network);
        $this->assertSame('USD', $record->currency);
        $this->assertSame(self::ETH, $record->destination);
        $this->assertSame('124.75', (string) $user->fresh()->available_balance);
        $this->assertSame('200.00', (string) $user->fresh()->balance);
        $this->assertNotEquals(self::ETH, DB::table('withdrawal_requests')->value('destination'));

        $this->get(route('user.withdrawals.index'))
            ->assertOk()
            ->assertSee('My withdrawal history')
            ->assertSee('55.25 USD')
            ->assertSee('Cancel request')
            ->assertDontSee(self::ETH);
    }

    public function test_user_can_cancel_only_pending_request_and_reservation_is_released_once(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);
        $this->post(route('user.withdrawals.cancel', ['reference' => $record->reference]))
            ->assertRedirect(route('user.withdrawals.index'));

        $this->assertSame('cancelled', $record->fresh()->status);
        $this->assertSame('180.00', (string) $user->fresh()->available_balance);

        $this->post(route('user.withdrawals.cancel', ['reference' => $record->reference]))
            ->assertSessionHasErrors('status');

        $this->assertSame('180.00', (string) $user->fresh()->available_balance);
    }

    public function test_second_active_request_or_overdraft_is_rejected(): void
    {
        $user = $this->user();
        $this->createRequest($user, '150.00');
        $this->post(route('user.withdrawals.store'), $this->requestData('30.00'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('withdrawal_requests', 1);
        $this->assertSame('30.00', (string) $user->fresh()->available_balance);

        $other = $this->user(true, '40.00', '35.00');
        $this->actingAs($other)
            ->post(route('user.withdrawals.store'), $this->requestData('55.25'))
            ->assertSessionHasErrors('amount');
        $this->assertSame('35.00', (string) $other->fresh()->available_balance);
    }

    public function test_admin_can_approve_but_only_record_an_external_payout_after_approval(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);
        $this->get(route('admin.withdrawals.index'))->assertRedirect(route('admin.login'));

        $this->actingAs($this->admin(), 'admin');
        $this->get(route('admin.withdrawals.index'))->assertOk()
            ->assertSee('Withdrawal requests')
            ->assertSee($record->reference);

        $this->get(route('admin.withdrawals.show', $record))->assertOk()
            ->assertSee(self::ETH)
            ->assertSee('Verified / approved');

        $txid = str_repeat('a', 64);
        $this->post(route('admin.withdrawals.paid', $record), [
            'txid' => $txid, 'confirm_external_transfer' => '1',
        ])->assertSessionHasErrors('status');

        $this->post(route('admin.withdrawals.approve', $record))
            ->assertRedirect(route('admin.withdrawals.show', $record))
            ->assertSessionHas('success');
        $this->assertSame('approved', $record->fresh()->status);
        $this->assertSame('124.75', (string) $user->fresh()->available_balance);
        $this->assertSame('200.00', (string) $user->fresh()->balance);

        $this->post(route('admin.withdrawals.paid', $record), [
            'txid' => $txid, 'confirm_external_transfer' => '1',
        ])->assertRedirect(route('admin.withdrawals.show', $record));

        $this->assertSame('paid_recorded', $record->fresh()->status);
        $this->assertSame($txid, $record->fresh()->payout_txid);
        $this->assertNotNull($record->fresh()->paid_at);
        $this->assertSame('124.75', (string) $user->fresh()->available_balance);
        $this->assertSame('144.75', (string) $user->fresh()->balance);

        $this->post(route('admin.withdrawals.paid', $record), [
            'txid' => $txid, 'confirm_external_transfer' => '1',
        ])->assertSessionHasErrors('status');
        $this->assertSame('144.75', (string) $user->fresh()->balance);
    }

    public function test_admin_rejection_restores_reservation_and_requires_reason(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);
        $this->actingAs($this->admin(), 'admin');
        $this->post(route('admin.withdrawals.reject', $record), [])
            ->assertSessionHasErrors('reason');

        $this->post(route('admin.withdrawals.reject', $record), [
            'reason' => 'Address does not match the verified beneficiary.',
        ])->assertRedirect(route('admin.withdrawals.show', $record));

        $this->assertSame('rejected', $record->fresh()->status);
        $this->assertSame('180.00', (string) $user->fresh()->available_balance);
        $this->assertSame('200.00', (string) $user->fresh()->balance);

        $this->post(route('admin.withdrawals.reject', $record), [
            'reason' => 'Repeated rejection attempt',
        ])->assertSessionHasErrors('status');

        $this->assertSame('180.00', (string) $user->fresh()->available_balance);
    }

    public function test_approval_rechecks_kyc_and_reject_remains_available_after_kyc_revocation(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);
        $user->update(['kyc_verified' => false]);
        $this->actingAs($this->admin(), 'admin');

        $this->post(route('admin.withdrawals.approve', $record))
            ->assertSessionHasErrors('kyc');
        $this->assertSame('pending', $record->fresh()->status);

        config(['withdrawals.enabled' => false]);
        $this->post(route('admin.withdrawals.reject', $record), [
            'reason' => 'KYC revoked and transfers paused.',
        ])->assertRedirect(route('admin.withdrawals.show', $record));

        $this->assertSame('180.00', (string) $user->fresh()->available_balance);
    }

    public function test_disabled_demo_banned_currency_and_invalid_chain_are_rejected(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        config(['withdrawals.enabled' => false]);

        $this->post(route('user.withdrawals.store'), $this->requestData())
            ->assertSessionHasErrors('amount');
        config(['withdrawals.enabled' => true]);

        $demo = $this->user();
        $demo->update(['registration_type' => 'demo']);
        $this->actingAs($demo)->post(route('user.withdrawals.store'), $this->requestData())
            ->assertForbidden();

        $user->update(['currency' => 'EUR']);
        $this->actingAs($user)->post(route('user.withdrawals.store'), $this->requestData())
            ->assertSessionHasErrors('amount');

        $user->update(['currency' => 'USD']);
        $this->post(route('user.withdrawals.store'), [
            ...$this->requestData(),
            'asset' => 'BTC',
            'network' => 'trc20',
        ])->assertSessionHasErrors('network');

        $this->post(route('user.withdrawals.store'), [
            ...$this->requestData(),
            'destination' => 'invalid-wallet',
        ])->assertSessionHasErrors('destination');

        $this->assertDatabaseCount('withdrawal_requests', 0);
    }

    public function test_one_member_cannot_cancel_another_members_request(): void
    {
        $owner = $this->user();
        $record = $this->createRequest($owner);
        $other = $this->user();
        $this->actingAs($other)->post(route('user.withdrawals.cancel', [
            'reference' => $record->reference,
        ]))->assertNotFound();

        $this->assertSame('pending', $record->fresh()->status);
        $this->assertSame('124.75', (string) $owner->fresh()->available_balance);
    }

    public function test_admin_cannot_record_payout_without_explicit_confirmation(): void
    {
        $user = $this->user();
        $record = $this->createRequest($user);
        $this->actingAs($this->admin(), 'admin');
        $this->post(route('admin.withdrawals.approve', $record))
            ->assertRedirect();

        $this->post(route('admin.withdrawals.paid', $record), ['txid' => str_repeat('f', 64)])
            ->assertSessionHasErrors('confirm_external_transfer');

        $this->assertSame('approved', $record->fresh()->status);
        $this->assertSame('200.00', (string) $user->fresh()->balance);
    }
}
