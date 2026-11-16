<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Setting;
use Database\Seeders\CountrySeeder;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CountryCurrencySeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    private function admin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Country Admin',
            'email' => 'country-admin@example.test',
            'password' => Hash::make('safe-test-password'),
        ]);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    public function test_country_and_currency_seeders_populate_pinned_offline_reference_data(): void
    {
        $this->seed(CurrencySeeder::class);
        $this->seed(CountrySeeder::class);

        $this->assertDatabaseCount('currencies', 160);
        $this->assertDatabaseCount('countries', 249);

        $this->assertSame('৳', Currency::where('code', 'BDT')->firstOrFail()->symbol);
        $this->assertSame('Euro', Currency::where('code', 'EUR')->firstOrFail()->name);
        $this->assertSame('€', Country::where('iso', 'LV')->firstOrFail()->currency_symbol);
        $this->assertSame('Latvia', Country::where('iso', 'LV')->firstOrFail()->name);
        $this->assertSame('BDT', Country::where('iso', 'BD')->firstOrFail()->currency);
        $this->assertSame('BGD', Country::where('iso', 'BD')->firstOrFail()->iso3);
        $this->assertSame('880', Country::where('iso', 'BD')->firstOrFail()->dial);
        $this->assertSame('EUR', Country::where('iso', 'HR')->firstOrFail()->currency);
        $this->assertSame('EUR', Country::where('iso', 'BG')->firstOrFail()->currency);
        $this->assertSame('XCG', Country::where('iso', 'CW')->firstOrFail()->currency);
    }

    public function test_reseeding_preserves_customized_country_status_currency_and_primary_key(): void
    {
        $this->seed(CurrencySeeder::class);
        $this->seed(CountrySeeder::class);

        $country = Country::where('iso', 'BD')->firstOrFail();
        $id = $country->id;
        $country->update(['currency' => 'USD', 'currency_symbol' => '$', 'status' => false]);
        $euro = Currency::where('code', 'EUR')->firstOrFail();
        $euro->update(['symbol' => 'CUSTOM']);

        $this->seed(CurrencySeeder::class);
        $this->seed(CountrySeeder::class);
        $this->assertDatabaseCount('countries', 249);
        $this->assertDatabaseCount('currencies', 160);
        $this->assertSame($id, $country->fresh()->id);
        $this->assertSame('USD', $country->fresh()->currency);
        $this->assertSame('$', $country->fresh()->currency_symbol);
        $this->assertFalse((bool) $country->fresh()->status);
        $this->assertSame('CUSTOM', $euro->fresh()->symbol);
    }

    public function test_legacy_country_typo_is_corrected_but_local_edits_are_untouched(): void
    {
        $country = Country::create([
            'name' => 'Latitude', 'iso' => 'LV', 'iso3' => 'LVA',
            'dial' => '371', 'currency' => 'EUR',
            'currency_name' => 'Custom Euro', 'currency_symbol' => '€',
            'status' => false,
        ]);
        $id = $country->id;
        $this->seed(CountrySeeder::class);
        $country->refresh();

        $this->assertSame($id, $country->id);
        $this->assertSame('Latvia', $country->name);
        $this->assertSame('Custom Euro', $country->currency_name);
        $this->assertFalse((bool) $country->status);
    }

    public function test_smtp_tab_does_not_write_null_or_change_currency_fields(): void
    {
        $this->admin();
        $setting = Setting::create([
            'name' => 'Free Games',
            'site_currency' => 'EUR',
            'currency_symble' => '€',
            'MAIL_PASSWORD' => 'original-private-secret',
            'MAIL_HOST' => 'smtp.old.example',
        ]);
        $secret = $setting->getRawOriginal('MAIL_PASSWORD');

        $this->put(route('admin.setting.update', $setting->id), [
            'settings_tab' => 'mail',
            'site_currency' => '',
            'currency_symble' => '',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.new.example',
            'MAIL_PORT' => '587',
            'MAIL_PASSWORD' => '',
            'MAIL_FROM_ADDRESS' => 'sender@example.test',
        ])->assertRedirect()->assertSessionHas('success');

        $setting->refresh();
        $this->assertSame('EUR', $setting->site_currency);
        $this->assertSame('€', $setting->currency_symble);
        $this->assertSame($secret, $setting->getRawOriginal('MAIL_PASSWORD'));
        $this->assertSame('smtp.new.example', $setting->MAIL_HOST);
    }

    public function test_legacy_settings_form_without_active_tab_also_preserves_non_nullable_currency(): void
    {
        $this->admin();
        $setting = Setting::create([
            'name' => 'Free Games',
            'site_currency' => 'BDT',
            'currency_symble' => '৳',
        ]);

        $this->put(route('admin.setting.update', $setting->id), [
            'MAIL_HOST' => 'new.mail.test',
            'currency_symble' => '',
            'site_currency' => '',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('BDT', $setting->fresh()->site_currency);
        $this->assertSame('৳', $setting->fresh()->currency_symble);
        $this->assertSame('new.mail.test', $setting->fresh()->MAIL_HOST);
    }

    public function test_setting_editor_displays_seeded_currency_names_and_symbols(): void
    {
        $this->seed(CurrencySeeder::class);
        $this->admin();
        Setting::create([
            'name' => 'Free Games',
            'site_currency' => 'USD',
            'currency_symble' => '$',
        ]);

        $this->get(route('admin.setting.edit'))
            ->assertOk()
            ->assertSee('data-cms-active-tab', false)
            ->assertSee('Bangladeshi Taka')
            ->assertSee('data-symbol="৳"', false)
            ->assertSee('name="currency_symble"', false);
    }
}
