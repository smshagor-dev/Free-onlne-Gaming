<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\SiteCmsConfig;
use App\Models\User;
use App\Models\UserLogin;
use App\Services\SiteCms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DynamicSiteCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        Cache::put('gamemonetize.embeddable.all', [
            ['id' => 'racer', 'name' => 'Super Racer', 'category' => 'Racing', 'image' => null],
            ['id' => 'puzzle', 'name' => 'Brain Puzzle', 'category' => 'Puzzle', 'image' => null],
            ['id' => 'action', 'name' => 'Action Hero', 'category' => 'Action', 'image' => null],
        ], 60);
        Http::preventStrayRequests();
    }

    private function admin(): Admin
    {
        $admin = Admin::create([
            'name' => 'CMS Editor',
            'email' => 'editor@example.test',
            'password' => Hash::make('editor-secret'),
        ]);
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    public function test_public_pages_have_default_values_before_configuration_exists(): void
    {
        $this->get(route('games.viewIndex'))->assertOk()
            ->assertSee('100% FREE TO PLAY')
            ->assertSee('Featured Games')
            ->assertSee('New Games');
        $this->get(route('site.manifest'))->assertOk()
            ->assertJsonPath('name', 'Free Games')
            ->assertJsonPath('start_url', '/')
            ->assertJsonPath('icons.0.sizes', '192x192');
    }

    public function test_only_admins_can_modify_cms_sections_or_read_user_activity(): void
    {
        $this->get(route('admin.site-builder.edit'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.user-activity.index'))->assertRedirect(route('admin.login'));
        $this->put(route('admin.site-builder.update', ['section'=>'footer']), [
            'text' => 'Not authorized',
            'contact_label' => 'Support',
            'show_contact' => 1,
            'show_games' => 0,
        ])->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('site_cms_configs', 0);
    }

    public function test_updating_homepage_replaces_only_that_section_and_preserves_settings_and_users(): void
    {
        $this->admin();
        $user = User::factory()->create(['name' => 'Existing Member']);
        $setting = Setting::create(['name' => 'Current Site', 'MAIL_PASSWORD' => 'keep-smtp-secret']);
        $currentSecret = $setting->getRawOriginal('MAIL_PASSWORD');

        $this->put(route('admin.site-builder.update',['section'=>'home']), [
            'badge' => 'PLAY ONLINE',
            'title_start' => 'Discover',
            'title_highlight' => 'Amazing Games',
            'title_end' => 'Every Day',
            'description' => 'A new selection of games.',
            'primary_cta' => 'Start Playing',
            'secondary_cta' => 'See Games',
            'featured_heading' => 'Our Picks',
            'new_heading' => 'Recently Added',
            'explore_heading' => 'Explore Everything',
            'show_categories' => 0,
            'show_featured' => 1,
            'show_new' => 0,
            'show_explore' => 1,
        ])->assertRedirect(route('admin.site-builder.edit',['section'=>'home']))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('site_cms_configs', 1);
        $this->get(route('games.viewIndex'))->assertOk()
            ->assertSee('Amazing Games')
            ->assertSee('PLAY ONLINE')
            ->assertSee('Our Picks')
            ->assertDontSee('Recently Added')
            ->assertDontSee('class="fg-categories"', false);

        $this->assertSame('Existing Member', $user->fresh()->name);
        $this->assertSame('Current Site', $setting->fresh()->name);
        $this->assertSame($currentSecret, $setting->fresh()->getRawOriginal('MAIL_PASSWORD'));
    }

    public function test_navigation_and_footer_controls_update_public_layout(): void
    {
        $this->admin();

        SiteCmsConfig::create(['section'=>'navigation', 'payload'=>array_replace(SiteCms::defaults('navigation'), [
            'home_label' => 'Start',
            'show_lottery' => false,
            'show_contact' => false,
            'category_labels' => 'Racing, Puzzle',
        ])]);
        SiteCmsConfig::create(['section'=>'footer', 'payload'=>array_replace(SiteCms::defaults('footer'), [
            'text' => 'Made for gamers',
            'contact_label' => 'Get in touch',
            'show_games' => true,
        ])]);
        SiteCms::clearCache();

        $this->get(route('games.viewIndex'))->assertOk()
            ->assertSee('Made for gamers')
            ->assertSee('Get in touch')
            ->assertSee('>Start</a>', false)
            ->assertDontSee('>Lottery</a>', false)
            ->assertDontSee('>Contact</a>', false);
    }

    public function test_pwa_settings_are_validated_and_live_manifest_updates(): void
    {
        $this->admin();
        $this->put(route('admin.site-builder.update',['section'=>'pwa']), [
            'name'=>'Free Games Hub',
            'short_name'=>'Games Hub',
            'description'=>'Install the game hub.',
            'theme_color'=>'#112233',
            'background_color'=>'#334455',
            'display'=>'minimal-ui',
        ])->assertRedirect()->assertSessionHas('success');

        $this->get(route('site.manifest'))->assertOk()
            ->assertJsonPath('name','Free Games Hub')
            ->assertJsonPath('theme_color','#112233')
            ->assertJsonPath('display','minimal-ui')
            ->assertJsonPath('icons.1.sizes','512x512')
            ->assertHeader('Cache-Control');

        $this->put(route('admin.site-builder.update',['section'=>'pwa']), [
            'name'=>'Oops','short_name'=>'Bad','description'=>'Bad',
            'theme_color'=>'javascript:alert(1)','background_color'=>'#334455',
            'display'=>'browser',
        ])->assertSessionHasErrors('theme_color');
        $this->get(route('site.manifest'))->assertJsonPath('theme_color','#112233');
    }

    public function test_external_catalog_can_be_hidden_without_erasing_game_cache(): void
    {
        $this->admin();
        SiteCmsConfig::create(['section'=>'catalog','payload'=>array_replace(SiteCms::defaults('catalog'),[
            'provider_enabled'=>false,
        ])]);
        SiteCms::clearCache();
        $this->get(route('playable.index'))->assertOk()->assertDontSee('Super Racer');
        $this->assertCount(3, Cache::get('gamemonetize.embeddable.all'));
    }

    public function test_navigation_rejects_gambling_categories_and_unknown_sections(): void
    {
        $this->admin();
        $payload = [
            'home_label'=>'Home', 'games_label'=>'Games','categories_label'=>'Categories',
            'lottery_label'=>'Lottery','contact_label'=>'Contact',
            'show_home'=>1,'show_games'=>1,'show_categories'=>1,'show_lottery'=>1,'show_contact'=>1,
            'category_labels'=>'Action, Casino, Puzzle',
        ];
        $this->put(route('admin.site-builder.update',['section'=>'navigation']),$payload)
            ->assertSessionHasErrors('category_labels');
        $this->put(route('admin.site-builder.update',['section'=>'unknown']),$payload)->assertNotFound();
    }

    public function test_member_activity_report_filters_records_without_displaying_secrets(): void
    {
        $this->admin();
        $user = User::factory()->create([
            'name'=>'Sample Member',
            'google2fa_secret'=>'hidden-private-key',
        ]);
        UserLogin::create([
            'user_id'=>$user->id,
            'ip_address'=>'192.0.2.10',
            'country'=>'Testland',
            'browser'=>'Test Browser',
            'device_type'=>'Desktop',
        ]);

        $this->get(route('admin.user-activity.index',['user_id'=>$user->id]))->assertOk()
            ->assertSee('Sample Member')
            ->assertSee('192.0.2.10')
            ->assertSee('Recorded sign-ins')
            ->assertDontSee('hidden-private-key');

        $this->get(route('admin.user-activity.index',[
            'from'=>'2020-01-01','to'=>'2021-01-01',
        ]))->assertSessionHasErrors('from');
    }
}
