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
            'url' => 'https://pay.example.com',
            'store_id' => 'store_abc',
            'api_key' => 'secret-test-token',
            'webhook_secret' => 'signed-webhook-secret',
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

    private function fakeCreate(string $id = 'invoice_123456789'): void
    {
        Http::fake([
            'https://pay.example.com/api/v1/stores/store_abc/invoices' => Http::response([
                'id' => $id,
                'checkoutLink' => 'https://pay.example.com/i/'.$id,
            ]),
        ]);
    }

    private function pending(User $user, string $amount = '55.00'): CryptoPayment
    {
        return CryptoPayment::query()->create([
            'user_id' => $user->id,
            'reference' => (string) Str::uuid(),
            'amount' => $amount,
            'currency' => 'USD',
            'invoice_id' => 'invoice_123456789',
            'checkout_url' => 'https://pay.example.com/i/invoice_123456789',
            'status' => 'pending',
        ]);
    }

    private function fakeInvoice(CryptoPayment $payment, string $status, string $additional = 'None', ?string $amount = null, ?string $reference = null): void
    {
        Http::fake([
            'https://pay.example.com/api/v1/stores/store_abc/invoices/*' => Http::response([
                'id' => $payment->invoice_id,
                'storeId' => 'store_abc',
                'amount' => $amount ?? $payment->amount,
                'currency' => $payment->currency,
                'status' => $status,
                'additionalStatus' => $additional,
                'metadata' => ['orderId' => $reference ?? $payment->reference],
            ]),
        ]);
    }

    private function webhook(string $invoiceId, bool $valid = true): \Illuminate\Testing\TestResponse
    {
        $payload = ['type' => 'InvoiceSettled', 'invoiceId' => $invoiceId, 'storeId' => 'store_abc'];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $body, 'signed-webhook-secret');

        return $this->call('POST', route('crypto.btcpay.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_BTCPAY_SIG' => $valid ? $signature : 'sha256=forged',
        ], $body);
    }

    public function test_crypto_checkout_requires_login_and_is_disabled_without_real_store_config(): void
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

    public function test_user_can_create_any_valid_custom_amount_with_hosted_payment_url(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        $this->fakeCreate();
        $this->post(route('user.crypto.store'), ['amount' => '55.25'])
            ->assertSessionHas('success');
        $record = CryptoPayment::query()->firstOrFail();

        $this->assertSame($user->id, $record->user_id);
        $this->assertSame('55.25', $record->amount);
        $this->assertSame('USD', $record->currency);
        $this->assertSame('pending', $record->status);
        $this->assertSame('invoice_123456789', $record->invoice_id);
        $this->assertSame('https://pay.example.com/i/invoice_123456789', $record->checkout_url);

        $this->get(route('user.crypto.show', ['reference' => $record->reference]))->assertOk()
            ->assertSee('55.25 USD')
            ->assertSee('Continue to BTCPay checkout')
            ->assertSee('https://pay.example.com/i/invoice_123456789')
            ->assertSee('Check provider status');

        $this->get(route('user.crypto.index'))->assertOk()
            ->assertSee('My payment history')
            ->assertSee('55.25')
            ->assertSee('Pay with crypto')
            ->assertSee('10 USD')
            ->assertSee('20 USD')
            ->assertSee('55 USD');

        Http::assertSent(fn ($req) => $req->method() === 'POST'
            && $req->hasHeader('Authorization', 'token secret-test-token')
            && $req->data()['amount'] === '55.25'
            && $req->data()['metadata']['orderId'] === $record->reference
            && $req->data()['currency'] === 'USD');

        $this->assertSame(0, DB::table('user_deposits')->count());
        $this->assertSame(0, DB::table('transactions')->count());
    }

    public function test_custom_amount_validation_rejects_negative_over_limit_fraction_and_exponent(): void
    {
        $this->actingAs($this->user());
        foreach (['0', '-5', '10000.01', '1.234', '1e3', 'abc'] as $amount) {
            $this->post(route('user.crypto.store'), ['amount' => $amount])
                ->assertSessionHasErrors('amount');
        }
        $this->assertDatabaseCount('crypto_payments', 0);
        Http::assertNothingSent();
    }

    public function test_checkout_cannot_be_used_by_demo_users_or_redirected_to_untrusted_host(): void
    {
        $demo = $this->user();
        $demo->update(['registration_type' => 'demo']);
        $this->actingAs($demo);
        $this->post(route('user.crypto.store'), ['amount' => '10'])->assertForbidden();
        $this->assertDatabaseCount('crypto_payments', 0);

        $this->actingAs($this->user());
        Http::fake(['https://pay.example.com/*' => Http::response([
            'id' => 'invoice_123456789',
            'checkoutLink' => 'https://evil.example.test/pay',
        ])]);
        $this->post(route('user.crypto.store'), ['amount' => '10'])
            ->assertRedirect(route('user.crypto.index'))->assertSessionHas('error');
        $this->assertSame('failed', CryptoPayment::query()->firstOrFail()->status);
    }

    public function test_verified_settled_webhook_updates_only_its_invoice_once_without_wallet_credit(): void
    {
        $user = $this->user();
        $originalBalance = $user->balance;
        $record = $this->pending($user);
        $this->fakeInvoice($record, 'Settled');

        $this->webhook($record->invoice_id)->assertOk();
        $this->assertSame('settled', $record->fresh()->status);
        $this->assertNotNull($record->fresh()->settled_at);

        $firstSettledAt = $record->fresh()->settled_at->toDateTimeString();
        $this->webhook($record->invoice_id)->assertOk();
        $this->assertSame($firstSettledAt, $record->fresh()->settled_at->toDateTimeString());
        $this->assertEquals($originalBalance, $user->fresh()->balance);
        $this->assertDatabaseCount('user_deposits', 0);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_forged_webhook_is_rejected_before_looking_up_invoice(): void
    {
        $record = $this->pending($this->user());
        $this->webhook($record->invoice_id, false)->assertUnauthorized();
        $this->assertSame('pending', $record->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_changed_amount_or_invoice_metadata_cannot_mark_payment_settled(): void
    {
        $record = $this->pending($this->user(), '55.00');
        $this->fakeInvoice($record, 'Settled', 'None', '999.00');
        $this->webhook($record->invoice_id)->assertStatus(503);
        $this->assertSame('pending', $record->fresh()->status);

        $this->fakeInvoice($record, 'Settled', 'None', '55.00', (string) Str::uuid());
        $this->webhook($record->invoice_id)->assertStatus(503);
        $this->assertSame('pending', $record->fresh()->status);
    }

    public function test_processing_and_partially_paid_invoices_never_become_final_settled(): void
    {
        $user = $this->user();
        $record = $this->pending($user);
        $this->fakeInvoice($record, 'Processing');
        $this->webhook($record->invoice_id)->assertOk();
        $this->assertSame('processing', $record->fresh()->status);

        $this->fakeInvoice($record, 'Settled', 'PaidPartial');
        $this->webhook($record->invoice_id)->assertOk();
        $this->assertSame('review', $record->fresh()->status);
        $this->assertNull($record->fresh()->settled_at);
    }

    public function test_member_cannot_view_or_refresh_other_users_invoice(): void
    {
        $other = $this->user();
        $record = $this->pending($other);
        $this->actingAs($this->user());
        $this->get(route('user.crypto.show', ['reference' => $record->reference]))->assertNotFound();
        $this->post(route('user.crypto.refresh', ['reference' => $record->reference]))->assertNotFound();
        $this->get(route('user.crypto.index'))->assertOk()->assertDontSee('55.00 USD');
    }

    public function test_owner_may_refresh_provider_status_and_admin_sees_read_only_ledger(): void
    {
        $user = $this->user();
        $record = $this->pending($user);
        $this->fakeInvoice($record, 'Settled');

        $this->actingAs($user)
            ->post(route('user.crypto.refresh', ['reference' => $record->reference]))
            ->assertRedirect(route('user.crypto.show', ['reference' => $record->reference]));
        $this->assertSame('settled', $record->fresh()->status);

        $this->get(route('admin.crypto.index'))->assertRedirect(route('admin.login'));
        $admin = Admin::query()->create([
            'name' => 'Payments Admin',
            'email' => 'payments-admin@example.test',
            'password' => Hash::make('test-pass'),
        ]);
        $this->actingAs($admin, 'admin');
        $this->get(route('admin.crypto.index'))->assertOk()
            ->assertSee('Crypto payment invoices')
            ->assertSee($record->reference)
            ->assertSee('55.00 USD')
            ->assertSee('Settled');
    }

    public function test_webhook_exception_configuration_is_scoped_to_one_endpoint(): void
    {
        $code = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringContainsString("'payments/crypto/btcpay/webhook'", $code);
        $this->assertStringNotContainsString("validateCsrfTokens(except: ['*']", $code);
    }
}
