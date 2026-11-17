<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CryptoPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class CryptoPaymentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        Cache::put('gamemonetize.embeddable.all', [], 60);
        config(['crypto_payments' => [
            'enabled' => true,
            'url' => 'https://api.nowpayments.io',
            'api_key' => 'merchant-test-key',
            'ipn_secret' => 'ipn-test-secret',
            'currency' => 'USD',
            'minimum' => '1.00',
            'maximum' => '10000.00',
        ]]);
        Http::preventStrayRequests();
    }

    private function user(): User
    {
        return User::factory()->create([
            'registration_type' => 'email',
            'google2fa_status' => false,
            'is_banned' => false,
        ]);
    }

    private function fakeCreate(string $id = '4522625843'): void
    {
        Http::fake([
            'https://api.nowpayments.io/v1/invoice' => Http::response([
                'id' => $id,
                'invoice_url' => 'https://nowpayments.io/payment/?iid='.$id,
            ]),
        ]);
    }

    private function pending(User $user, string $amount = '55.00'): CryptoPayment
    {
        return CryptoPayment::query()->create([
            'user_id' => $user->id,
            'reference' => (string) Str::uuid(),
            'provider' => 'nowpayments',
            'amount' => $amount,
            'currency' => 'USD',
            'invoice_id' => '4522625843',
            'checkout_url' => 'https://nowpayments.io/payment/?iid=4522625843',
            'status' => 'pending',
        ]);
    }

    private function fakePayment(
        CryptoPayment $record,
        string $status = 'finished',
        string $amount = '55.00',
        ?string $reference = null,
        string $paymentId = '5077125051'
    ): void {
        Http::fake([
            'https://api.nowpayments.io/v1/payment/*' => Http::response([
                'payment_id' => $paymentId,
                'invoice_id' => $record->invoice_id,
                'order_id' => $reference ?? $record->reference,
                'price_amount' => $amount,
                'price_currency' => $record->currency,
                'payment_status' => $status,
            ]),
        ]);
    }

    /**
     * NOWPayments signs sorted JSON rather than the original wire bytes.
     * The webhook body intentionally uses a different key order.
     */
    private function webhook(CryptoPayment $record, bool $valid = true, string $paymentId = '5077125051'): \Illuminate\Testing\TestResponse
    {
        $payload = [
            'payment_status' => 'finished',
            'payment_id' => (int) $paymentId,
            'invoice_id' => (int) $record->invoice_id,
            'price_amount' => (float) $record->amount,
            'order_id' => $record->reference,
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        ksort($payload);
        $sorted = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $sorted, 'ipn-test-secret');

        return $this->call('POST', route('crypto.nowpayments.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_NOWPAYMENTS_SIG' => $valid ? $signature : 'forged',
        ], $body);
    }

    public function test_login_is_required_and_unconfigured_crypto_checkout_is_disabled(): void
    {
        $this->get(route('user.crypto.index'))->assertRedirect(route('login.form'));
        $this->post(route('user.crypto.store'), ['amount' => '10'])
            ->assertRedirect(route('login.form'));

        config(['crypto_payments.enabled' => false]);
        $this->actingAs($this->user())->get(route('user.crypto.index'))->assertOk()
            ->assertSee('Crypto checkout is currently unavailable')
            ->assertSee('disabled', false);

        $this->post(route('user.crypto.store'), ['amount' => '10'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('crypto_payments', 0);
        Http::assertNothingSent();
    }

    public function test_any_valid_amount_creates_an_invoice_and_user_can_see_hosted_url_and_history(): void
    {
        $this->actingAs($this->user());
        $this->fakeCreate();

        $this->post(route('user.crypto.store'), ['amount' => '55.25'])
            ->assertSessionHas('success');
        $record = CryptoPayment::query()->firstOrFail();

        $this->assertSame('55.25', $record->amount);
        $this->assertSame('USD', $record->currency);
        $this->assertSame('nowpayments', $record->provider);
        $this->assertSame('pending', $record->status);
        $this->assertSame('4522625843', $record->invoice_id);
        $this->assertSame('https://nowpayments.io/payment/?iid=4522625843', $record->checkout_url);

        $this->get(route('user.crypto.show', ['reference' => $record->reference]))->assertOk()
            ->assertSee('55.25 USD')
            ->assertSee('Continue to NOWPayments checkout')
            ->assertSee('https://nowpayments.io/payment/?iid=4522625843');
        $this->get(route('user.crypto.index'))->assertOk()
            ->assertSee('My payment history')
            ->assertSee('55.25')
            ->assertSee('10 USD')
            ->assertSee('20 USD')
            ->assertSee('55 USD');

        Http::assertSent(fn ($req) => $req->method() === 'POST'
            && $req->hasHeader('x-api-key', 'merchant-test-key')
            && $req->data()['price_amount'] === '55.25'
            && $req->data()['price_currency'] === 'usd'
            && $req->data()['order_id'] === $record->reference
            && $req->data()['ipn_callback_url'] === route('crypto.nowpayments.webhook'));

        $this->assertDatabaseCount('user_deposits', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_custom_amount_validation_rejects_negative_over_max_precision_and_exponents(): void
    {
        $this->actingAs($this->user());
        foreach (['0', '-5', '10000.01', '1.234', '1e3', 'abc'] as $amount) {
            $this->post(route('user.crypto.store'), ['amount' => $amount])
                ->assertSessionHasErrors('amount');
        }
        $this->assertDatabaseCount('crypto_payments', 0);
        Http::assertNothingSent();
    }

    public function test_demo_users_and_forged_checkout_urls_cannot_issue_live_invoices(): void
    {
        $demo = $this->user();
        $demo->update(['registration_type' => 'demo']);
        $this->actingAs($demo);
        $this->post(route('user.crypto.store'), ['amount' => '10'])->assertForbidden();
        $this->assertDatabaseCount('crypto_payments', 0);

        $this->actingAs($this->user());
        Http::fake(['https://api.nowpayments.io/*' => Http::response([
            'id' => '4522625843',
            'invoice_url' => 'https://evil.example.test/pay',
        ])]);
        $this->post(route('user.crypto.store'), ['amount' => '10'])
            ->assertRedirect(route('user.crypto.index'))->assertSessionHas('error');
        $this->assertSame('failed', CryptoPayment::query()->firstOrFail()->status);
    }

    public function test_signed_finished_payment_is_settled_once_and_never_credits_casino_balance(): void
    {
        $user = $this->user();
        $record = $this->pending($user);
        $originalBalance = $user->balance;
        $this->fakePayment($record);
        $this->webhook($record)->assertOk();

        $this->assertSame('settled', $record->fresh()->status);
        $this->assertSame('5077125051', $record->fresh()->provider_payment_id);
        $this->assertNotNull($record->fresh()->settled_at);
        $first = $record->fresh()->settled_at->toDateTimeString();
        $this->webhook($record)->assertOk();
        $this->assertSame($first, $record->fresh()->settled_at->toDateTimeString());
        $this->assertEquals($originalBalance, $user->fresh()->balance);
        $this->assertDatabaseCount('user_deposits', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_forged_webhook_signature_is_rejected_without_provider_request(): void
    {
        $record = $this->pending($this->user());
        $this->webhook($record, false)->assertUnauthorized();
        $this->assertSame('pending', $record->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_amount_or_order_mismatch_cannot_settle_a_payment(): void
    {
        $record = $this->pending($this->user());
        $this->fakePayment($record, 'finished', '999.00');
        $this->webhook($record)->assertStatus(503);
        $this->assertSame('pending', $record->fresh()->status);

        $this->fakePayment($record, 'finished', '55.00', (string) Str::uuid());
        $this->webhook($record)->assertStatus(503);
        $this->assertSame('pending', $record->fresh()->status);
    }

    public function test_confirmation_is_not_final_and_partial_payment_requires_review(): void
    {
        $record = $this->pending($this->user());
        $this->fakePayment($record, 'confirming');
        $this->webhook($record)->assertOk();
        $this->assertSame('processing', $record->fresh()->status);

        $this->fakePayment($record, 'partially_paid');
        $this->webhook($record)->assertOk();
        $this->assertSame('review', $record->fresh()->status);
        $this->assertNull($record->fresh()->settled_at);
    }

    public function test_two_provider_payments_for_same_invoice_require_review_not_double_settlement(): void
    {
        $record = $this->pending($this->user());
        $this->fakePayment($record);
        $this->webhook($record)->assertOk();

        $this->fakePayment($record, 'finished', '55.00', null, '6077125052');
        $this->webhook($record, true, '6077125052')->assertOk();
        $this->assertSame('review', $record->fresh()->status);
        $this->assertSame('multiple_payments', $record->fresh()->additional_status);
    }

    public function test_user_cannot_read_another_users_payment(): void
    {
        $record = $this->pending($this->user());
        $this->actingAs($this->user());
        $this->get(route('user.crypto.show', ['reference' => $record->reference]))->assertNotFound();
        $this->post(route('user.crypto.refresh', ['reference' => $record->reference]))->assertNotFound();
    }

    public function test_owner_can_recheck_verified_payment_and_admin_sees_audit_list(): void
    {
        $user = $this->user();
        $record = $this->pending($user);
        $record->update(['provider_payment_id' => '5077125051']);
        $this->fakePayment($record);

        $this->actingAs($user)->post(route('user.crypto.refresh', ['reference' => $record->reference]))
            ->assertRedirect(route('user.crypto.show', ['reference' => $record->reference]));
        $this->assertSame('settled', $record->fresh()->status);
        $this->get(route('admin.crypto.index'))->assertRedirect(route('admin.login'));

        $admin = Admin::query()->create([
            'name' => 'Payment Admin',
            'email' => 'payment-audit@example.test',
            'password' => Hash::make('test-pass'),
        ]);
        $this->actingAs($admin, 'admin');
        $this->get(route('admin.crypto.index'))->assertOk()
            ->assertSee('Crypto payment invoices')
            ->assertSee($record->reference)
            ->assertSee('55.00 USD')
            ->assertSee('Settled');
    }

    public function test_csrf_exemption_applies_only_to_the_signed_ipn(): void
    {
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringContainsString("'payments/crypto/nowpayments/webhook'", $bootstrap);
        $this->assertStringNotContainsString("validateCsrfTokens(except: ['*']", $bootstrap);
    }
}
