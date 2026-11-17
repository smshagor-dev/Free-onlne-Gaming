<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Gateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentGatewayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    private function loginAdmin(): void
    {
        $admin = Admin::create([
            'name' => 'Gateway Admin',
            'email' => 'gateway-admin@example.test',
            'password' => Hash::make('test-password'),
        ]);
        $this->actingAs($admin, 'admin');
    }

    private function validInput(): array
    {
        return [
            'name' => 'Test Provider (configuration only)',
            'currency' => 'eur',
            'symbol' => '€',
            'min_amount' => '10.00',
            'max_amount' => '300.00',
            'instruction' => 'Internal note only.',
            'withdraw_instruction' => 'Do not enable payouts without approval.',
        ];
    }

    public function test_payment_gateway_management_is_admin_only(): void
    {
        $this->get(route('admin.gateways.index'))
            ->assertRedirect(route('admin.login'));
        $this->get(route('admin.gateways.create'))
            ->assertRedirect(route('admin.login'));
        $this->post(route('admin.gateways.store'), $this->validInput())
            ->assertRedirect(route('admin.login'));

        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('admin.gateways.store'), $this->validInput())
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount('gateways', 0);
    }

    public function test_new_gateway_configuration_defaults_inactive_and_does_not_credit_deposits(): void
    {
        $this->loginAdmin();
        $this->post(route('admin.gateways.store'), $this->validInput())
            ->assertRedirect(route('admin.gateways.index'))
            ->assertSessionHas('success');

        $gateway = Gateway::query()->firstOrFail();
        $this->assertSame('EUR', $gateway->currency);
        $this->assertSame('€', $gateway->symbol);
        $this->assertEquals(10.0, (float) $gateway->min_amount);
        $this->assertEquals(300.0, (float) $gateway->max_amount);
        $this->assertFalse($gateway->status);
        $this->assertDatabaseCount('user_deposits', 0);
        $this->assertDatabaseCount('transactions', 0);

        $this->get(route('admin.gateways.index'))->assertOk()
            ->assertSee('Payment gateway management')
            ->assertSee('Not integrated')
            ->assertSee('Inactive')
            ->assertSee('Test Provider (configuration only)')
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee(route('admin.gateways.edit', $gateway), false);
    }

    public function test_admin_edit_preserves_gateway_status_and_legacy_financial_records(): void
    {
        $this->loginAdmin();
        $gateway = Gateway::query()->create([
            'name' => 'Existing Gateway',
            'currency' => 'USD',
            'symbol' => '$',
            'min_amount' => 1,
            'max_amount' => 1000,
            'status' => true,
            'instruction' => 'Retain me',
        ]);

        $input = $this->validInput();
        $input['name'] = 'Updated Provider';
        // A forged status=0/1 should be ignored; activation is not offered.
        $input['status'] = 0;
        $this->put(route('admin.gateways.update', $gateway), $input)
            ->assertRedirect(route('admin.gateways.index'));

        $gateway->refresh();
        $this->assertTrue($gateway->status);
        $this->assertSame('Updated Provider', $gateway->name);
        $this->assertSame('EUR', $gateway->currency);

        $this->put(route('admin.gateways.update', $gateway), [
            ...$this->validInput(),
            'status' => 1,
        ])->assertRedirect(route('admin.gateways.index'));

        $this->assertTrue($gateway->fresh()->status);
        $this->assertSame(0, DB::table('user_deposits')->count());
        $this->assertSame(0, DB::table('transactions')->count());
    }

    public function test_gateway_form_rejects_invalid_currency_ranges_and_restricts_text_lengths(): void
    {
        $this->loginAdmin();

        $this->post(route('admin.gateways.store'), [
            'name' => '<script>invalid</script>',
            'currency' => 'NOT_A_CODE',
            'symbol' => '$',
            'min_amount' => '200.00',
            'max_amount' => '10.00',
            'instruction' => str_repeat('x', 4001),
        ])->assertSessionHasErrors(['currency', 'max_amount', 'instruction']);

        $this->assertDatabaseCount('gateways', 0);
    }

    public function test_gateway_page_does_not_include_checkout_or_live_wager_endpoints(): void
    {
        $this->loginAdmin();
        $this->get(route('admin.gateways.index'))->assertOk()
            ->assertSee('CONFIGURATION ONLY')
            ->assertSee('Not integrated')
            ->assertSee('No user balance or casino transaction is credited');

        $this->get(route('admin.gateways.create'))->assertOk()
            ->assertSee('No API keys, card data, deposits, or payouts are processed.');

        $this->post(route('admin.gateways.index'))->assertStatus(405);
    }
}
