<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    private function loginAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'CMS Administrator',
            'email' => 'cms-admin@example.test',
            'password' => Hash::make('test-admin-password'),
        ]);
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    public function test_cms_pages_and_user_data_require_admin_authentication(): void
    {
        $routes = [
            route('admin.dashboard'),
            route('admin.setting.edit'),
            route('admin.finance.index'),
            route('admin.users.view', ['id' => 5]),
        ];
        foreach ($routes as $route) {
            $this->get($route)->assertRedirect(route('admin.login'));
        }
        $this->post(route('admin.setting.mail.test'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_dashboard_displays_actual_user_counts_and_charts(): void
    {
        $this->loginAdmin();
        User::factory()->count(3)->create();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard overview')
            ->assertSee('Registered users')
            ->assertSee('Daily registrations')
            ->assertSee('Game opens')
            ->assertSee('Deposit amounts')
            ->assertSee('Withdrawal amounts')
            ->assertSee('3 joined in the last 14 days')
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.setting.edit'), false);
    }

    public function test_full_settings_form_preserves_secret_and_covers_configurations(): void
    {
        $this->loginAdmin();
        $setting = Setting::create([
            'name' => 'Old name',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.example.test',
            'MAIL_PORT' => '587',
            'MAIL_USERNAME' => 'sender@example.test',
            'MAIL_PASSWORD' => 'old-secret-password',
            'MAIL_FROM_ADDRESS' => 'notifications@example.test',
            'MAIL_FROM_NAME' => 'Free Games',
            'google_analytics_code' => 'G-TESTVALUE',
            'adsense_code' => 'ca-pub-demo',
        ]);

        $originalRaw = $setting->getRawOriginal('MAIL_PASSWORD');
        $this->assertStringStartsWith('encrypted:v1:', $originalRaw);

        $this->get(route('admin.setting.edit'))->assertOk()
            ->assertSee('SMTP & email')
            ->assertSee('Google Analytics')
            ->assertSee('value="" autocomplete="new-password"', false)
            ->assertDontSee('old-secret-password');

        $this->put(route('admin.setting.update', $setting), [
            'name' => 'New Free Games',
            'MAIL_PASSWORD' => '',
            'MAIL_HOST' => 'smtp.newhost.test',
        ])->assertRedirect()->assertSessionHas('success');

        $setting->refresh();
        $this->assertSame('New Free Games', $setting->name);
        $this->assertSame('smtp.newhost.test', $setting->MAIL_HOST);
        $this->assertSame($originalRaw, $setting->getRawOriginal('MAIL_PASSWORD'));
        $this->assertSame('old-secret-password', $setting->MAIL_PASSWORD);
        $this->assertSame('G-TESTVALUE', $setting->google_analytics_code);
        $this->assertSame('ca-pub-demo', $setting->adsense_code);
        $this->assertSame('notifications@example.test', $setting->MAIL_FROM_ADDRESS);

        $this->put(route('admin.setting.update', $setting), [
            'MAIL_PASSWORD' => 'new-secret-password',
        ])->assertRedirect()->assertSessionHas('success');

        $setting->refresh();
        $this->assertSame('new-secret-password', $setting->MAIL_PASSWORD);
        $this->assertStringStartsWith('encrypted:v1:', $setting->getRawOriginal('MAIL_PASSWORD'));
        $this->assertStringNotContainsString('new-secret-password', $setting->getRawOriginal('MAIL_PASSWORD'));
        $this->assertArrayNotHasKey('MAIL_PASSWORD', $setting->toArray());
    }

    public function test_legacy_plaintext_password_is_still_readable_without_migration_side_effects(): void
    {
        $this->loginAdmin();
        DB::table('settings')->insert([
            'name' => 'Existing site',
            'MAIL_HOST' => 'legacy.smtp.test',
            'MAIL_PASSWORD' => 'existing-plain-password',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $setting = Setting::firstOrFail();
        $this->assertSame('existing-plain-password', $setting->MAIL_PASSWORD);
        $this->get(route('admin.setting.edit'))->assertOk()
            ->assertDontSee('existing-plain-password');
        $this->put(route('admin.setting.update', $setting), ['name' => 'Updated site'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('existing-plain-password', DB::table('settings')->value('MAIL_PASSWORD'));
    }

    public function test_mail_test_does_not_send_when_smtp_driver_is_not_enabled(): void
    {
        $this->loginAdmin();
        Mail::fake();
        config(['mail.default' => 'log']);

        $this->post(route('admin.setting.mail.test'))
            ->assertRedirect()
            ->assertSessionHas('error');
        Mail::assertNothingSent();
    }

    public function test_finance_and_member_profile_are_read_only_and_do_not_show_secrets(): void
    {
        $this->loginAdmin();
        $user = User::factory()->create([
            'name' => 'Member Details',
            'google2fa_secret' => 'PRIVATE-TWOFA-SECRET',
            'reset_token' => 'PRIVATE-RESET-TOKEN',
        ]);

        $this->get(route('admin.users.view', ['id' => $user->id]))
            ->assertOk()
            ->assertSee('Identity & account', false)
            ->assertSee('Wallet, points & bonuses', false)
            ->assertSee('Recent account sign-ins')
            ->assertDontSee('PRIVATE-TWOFA-SECRET')
            ->assertDontSee('PRIVATE-RESET-TOKEN');

        $this->get(route('admin.finance.index'))
            ->assertOk()
            ->assertSee('Financial oversight')
            ->assertSee('Deposits')
            ->assertSee('Withdrawals')
            ->assertSee('Transactions');
    }

    public function test_legacy_global_settings_redirects_to_real_form(): void
    {
        $this->loginAdmin();
        $this->get(route('admin.settings.global'))->assertRedirect(route('admin.setting.edit'));
    }
}
