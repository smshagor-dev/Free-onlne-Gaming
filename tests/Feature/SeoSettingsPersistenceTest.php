<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoSettingsPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        Cache::put('gamemonetize.embeddable.all', [], 60);
        Http::preventStrayRequests();
    }

    private function admin(): void
    {
        $admin = Admin::create([
            'name' => 'SEO Editor',
            'email' => 'seo-admin@example.test',
            'password' => Hash::make('testing-password'),
        ]);
        $this->actingAs($admin, 'admin');
    }

    public function test_blank_legacy_seo_values_show_editable_defaults_without_writing_to_database(): void
    {
        $this->admin();
        $setting = Setting::create([
            'name' => 'Free Games',
            'title' => 'Custom Game Website',
            'description' => 'Choose and play browser games online.',
            'site_currency' => 'EUR',
            'currency_symble' => '€',
        ]);

        $this->get(route('admin.setting.edit', ['tab' => 'seo']))
            ->assertOk()
            ->assertSee('name="settings_tab" value="seo"', false)
            ->assertSee('id="seo_title"', false)
            ->assertSee('value="Custom Game Website"', false)
            ->assertSee('Choose and play browser games online.')
            ->assertSee('Save SEO metadata settings')
            ->assertSee('id="cms-panel-seo"', false)
            ->assertSee('aria-selected="true"', false);

        $this->assertNull($setting->fresh()->seo_title);
        $this->assertNull($setting->fresh()->seo_description);
        $this->assertNull($setting->fresh()->meta_tag);
    }

    public function test_seo_save_persists_and_reloads_exact_values_without_changing_smtp_and_currency(): void
    {
        $this->admin();
        $setting = Setting::create([
            'name' => 'Free Games',
            'title' => 'Existing site title',
            'seo_title' => null,
            'seo_description' => null,
            'site_currency' => 'BDT',
            'currency_symble' => '৳',
            'MAIL_HOST' => 'smtp.existing.example',
            'MAIL_PASSWORD' => 'private-existing-password',
        ]);
        $encrypted = $setting->getRawOriginal('MAIL_PASSWORD');

        $this->put(route('admin.setting.update', $setting), [
            'settings_tab' => 'seo',
            'seo_title' => 'Online Browser Games | Game Hub',
            'seo_description' => 'Enjoy puzzle, arcade and racing games in your browser.',
            'meta_tag' => 'browser games, arcade games, puzzle games',
            // This unexpected field MUST be ignored, not persisted.
            'MAIL_HOST' => 'smtp.attacker.invalid',
        ])->assertRedirect(route('admin.setting.edit', ['tab' => 'seo']))
            ->assertSessionHas('success', 'SEO metadata settings saved successfully.');

        $setting->refresh();
        $this->assertSame('Online Browser Games | Game Hub', $setting->seo_title);
        $this->assertSame('Enjoy puzzle, arcade and racing games in your browser.', $setting->seo_description);
        $this->assertSame('browser games, arcade games, puzzle games', $setting->meta_tag);
        $this->assertSame('Existing site title', $setting->title);
        $this->assertSame('smtp.existing.example', $setting->MAIL_HOST);
        $this->assertSame($encrypted, $setting->getRawOriginal('MAIL_PASSWORD'));
        $this->assertSame('BDT', $setting->site_currency);
        $this->assertSame('৳', $setting->currency_symble);

        $this->get(route('admin.setting.edit', ['tab' => 'seo']))
            ->assertOk()
            ->assertSee('value="Online Browser Games | Game Hub"', false)
            ->assertSee('Enjoy puzzle, arcade and racing games in your browser.')
            ->assertSee('value="browser games, arcade games, puzzle games"', false);
    }

    public function test_blank_seo_save_shows_validation_errors_instead_of_false_success(): void
    {
        $this->admin();
        $setting = Setting::create(['name' => 'Free Games']);

        $this->from(route('admin.setting.edit', ['tab' => 'seo']))
            ->put(route('admin.setting.update', $setting), [
                'settings_tab' => 'seo',
                'seo_title' => '',
                'seo_description' => '',
                'meta_tag' => '',
            ])->assertRedirect(route('admin.setting.edit', ['tab' => 'seo']))
            ->assertSessionHasErrors(['seo_title', 'seo_description'])
            ->assertSessionMissing('success');

        $this->assertNull($setting->fresh()->seo_title);
        $this->assertNull($setting->fresh()->seo_description);
    }

    public function test_general_and_smtp_form_submissions_do_not_erase_saved_seo_metadata(): void
    {
        $this->admin();
        $setting = Setting::create([
            'name' => 'Free Games',
            'seo_title' => 'Keep this SEO title',
            'seo_description' => 'Keep this SEO description.',
            'meta_tag' => 'keep, these, keywords',
            'site_currency' => 'EUR',
            'currency_symble' => '€',
        ]);

        $this->put(route('admin.setting.update', $setting), [
            'settings_tab' => 'mail',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'new.smtp.example',
            'currency_symble' => '',
        ])->assertRedirect(route('admin.setting.edit', ['tab' => 'mail']))
            ->assertSessionHas('success', 'SMTP & email settings saved successfully.');

        $setting->refresh();
        $this->assertSame('Keep this SEO title', $setting->seo_title);
        $this->assertSame('Keep this SEO description.', $setting->seo_description);
        $this->assertSame('keep, these, keywords', $setting->meta_tag);
        $this->assertSame('€', $setting->currency_symble);
        $this->assertSame('new.smtp.example', $setting->MAIL_HOST);
    }

    public function test_public_page_uses_saved_seo_title_description_and_keywords(): void
    {
        $setting = Setting::create([
            'name' => 'Free Games',
            'title' => 'Older site heading',
            'seo_title' => 'Play Free Browser Games - Free Games',
            'seo_description' => 'Fast free HTML5 games in your browser.',
            'meta_tag' => 'free games, HTML5 games',
        ]);

        // In a long-lived worker the next render should see the updated
        // setting after cache invalidation, without recreating the database.
        Cache::forget('app.settings.first');

        $this->get(route('games.viewIndex'))->assertOk()
            ->assertSee('<title>Play Free Browser Games - Free Games</title>', false)
            ->assertSee('<meta name="description" content="Fast free HTML5 games in your browser.">', false)
            ->assertSee('<meta property="og:title" content="Play Free Browser Games - Free Games">', false)
            ->assertSee('<meta property="og:description" content="Fast free HTML5 games in your browser.">', false)
            ->assertSee('<meta name="keywords" content="free games, HTML5 games">', false);
    }

    public function test_admin_settings_renders_five_isolated_forms(): void
    {
        $template = file_get_contents(resource_path('views/admin/settings/edit.blade.php'));

        foreach (['general', 'brand', 'seo', 'analytics', 'mail'] as $tab) {
            $this->assertStringContainsString('data-cms-settings-form="'.$tab.'"', $template);
            $this->assertStringContainsString('name="settings_tab" value="'.$tab.'"', $template);
        }

        $this->assertSame(5, substr_count($template, 'data-cms-settings-form="'));
        $this->assertStringNotContainsString('data-cms-active-tab', $template);
        $this->assertStringContainsString('Save SEO metadata settings', $template);
        $this->assertStringContainsString('Save SMTP & email settings', $template);
    }
}
