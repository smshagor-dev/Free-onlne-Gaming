<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SiteCmsConfig;
use App\Services\GameEmbedPolicy;
use App\Services\SiteCms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GameEmbedPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'app.url' => 'https://freegames.example']);
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_all_ad_features_default_off_and_external_ads_remain_provider_controlled(): void
    {
        $policy = app(GameEmbedPolicy::class);

        $local = $policy->resolve('https://freegames.example/games/local.html');
        $this->assertSame('same_origin', $local['mode']);
        $this->assertFalse($local['cleanup_owned_ads']);
        $this->assertFalse($local['provider_ad_free']);

        $remote = $policy->resolve('https://html5.gamemonetize.co/testgame/');
        $this->assertSame('third_party', $remote['mode']);
        $this->assertFalse($remote['cleanup_owned_ads']);
        $this->assertFalse($remote['provider_ad_free']);
        $this->assertSame('https://html5.gamemonetize.co/testgame/', $remote['url']);
        $this->assertStringContainsString('provider', $remote['notice']);
    }

    public function test_only_exact_same_origin_can_enable_owned_ad_cleanup(): void
    {
        SiteCmsConfig::create(['section' => 'embeds', 'payload' => [
            'owned_ad_cleanup_enabled' => true,
            'prefer_provider_ad_free' => false,
        ]]);
        SiteCms::clearCache();

        $policy = app(GameEmbedPolicy::class);
        $this->assertTrue($policy->resolve('https://freegames.example/mygame')['cleanup_owned_ads']);
        $this->assertTrue($policy->resolve('https://freegames.example:443/mygame')['cleanup_owned_ads']);
        $this->assertFalse($policy->resolve('https://freegames.example:8443/mygame')['cleanup_owned_ads']);
        $this->assertFalse($policy->resolve('https://games.freegames.example/mygame')['cleanup_owned_ads']);
        $this->assertFalse($policy->resolve('https://freegames.example.attacker.test/mygame')['cleanup_owned_ads']);
    }

    public function test_no_unverified_provider_can_enable_an_ad_free_parameter(): void
    {
        SiteCmsConfig::create(['section' => 'embeds', 'payload' => [
            'owned_ad_cleanup_enabled' => true,
            'prefer_provider_ad_free' => true,
        ]]);
        SiteCms::clearCache();

        $policy = app(GameEmbedPolicy::class);
        $this->assertSame('https://html5.gamemonetize.co/a/?foo=bar',
            $policy->resolve('https://html5.gamemonetize.co/a/?foo=bar')['url']);
        $this->assertFalse($policy->resolve('https://html5.gamemonetize.co/a/')['provider_ad_free']);

        config(['game_embeds.approved_ad_free_providers' => [
            'html5.gamemonetize.co' => [
                'verified' => false, 'parameter' => 'ads', 'value' => 'off',
            ],
        ]]);
        $this->assertFalse($policy->resolve('https://html5.gamemonetize.co/a/')['provider_ad_free']);
    }

    public function test_explicitly_verified_official_contract_is_applied_only_to_exact_provider(): void
    {
        SiteCmsConfig::create(['section' => 'embeds', 'payload' => [
            'owned_ad_cleanup_enabled' => false,
            'prefer_provider_ad_free' => true,
        ]]);
        SiteCms::clearCache();
        config(['game_embeds.approved_ad_free_providers' => [
            'licensed.example' => [
                'verified' => true, 'parameter' => 'official_mode', 'value' => 'adfree',
            ],
        ]]);

        $policy = app(GameEmbedPolicy::class);
        $remote = $policy->resolve('https://licensed.example/launch?id=42#play');
        $this->assertTrue($remote['provider_ad_free']);
        $this->assertFalse($remote['cleanup_owned_ads']);
        $this->assertSame('https://licensed.example/launch?id=42&official_mode=adfree#play', $remote['url']);
        $this->assertSame('third_party', $remote['mode']);

        $unlisted = $policy->resolve('https://licensed.example.attacker.test/launch');
        $this->assertFalse($unlisted['provider_ad_free']);
        $this->assertSame('https://licensed.example.attacker.test/launch', $unlisted['url']);
    }

    public function test_unsafe_urls_are_rejected_and_never_get_ad_config(): void
    {
        $policy = app(GameEmbedPolicy::class);
        foreach ([
            'javascript:alert(1)',
            'data:text/html,hi',
            '//freegames.example/launch',
            'ftp://freegames.example/launch',
            'http://thirdparty.example/game',
            'https://user:pass@freegames.example/launch',
            'not-a-url',
        ] as $url) {
            $embed = $policy->resolve($url);
            $this->assertSame('blocked', $embed['mode'], $url);
            $this->assertSame('', $embed['url']);
            $this->assertFalse($embed['cleanup_owned_ads']);
            $this->assertFalse($embed['provider_ad_free']);
        }
    }

    public function test_admin_cms_toggles_preserve_other_sections_and_reject_guest_writes(): void
    {
        $route = route('admin.site-builder.update', ['section' => 'embeds']);
        $this->put($route, [
            'owned_ad_cleanup_enabled' => 1, 'prefer_provider_ad_free' => 1,
        ])->assertRedirect(route('admin.login'));

        $admin = Admin::create([
            'name' => 'Embed Admin',
            'email' => 'embed-admin@example.test',
            'password' => Hash::make('testing-password'),
        ]);
        $this->actingAs($admin, 'admin')->put($route, [
            'owned_ad_cleanup_enabled' => 1, 'prefer_provider_ad_free' => 1,
        ])->assertRedirect(route('admin.site-builder.edit', ['section' => 'embeds']))
            ->assertSessionHas('success');

        $this->assertTrue(SiteCms::section('embeds')['owned_ad_cleanup_enabled']);
        $this->assertTrue(SiteCms::section('embeds')['prefer_provider_ad_free']);
        $this->assertDatabaseCount('site_cms_configs', 1);
        $this->get(route('admin.site-builder.edit', ['section' => 'embeds']))
            ->assertOk()
            ->assertSee('Game Iframe Policy')
            ->assertSee('cross-origin iframe');

        $this->put($route, [
            'owned_ad_cleanup_enabled' => 'script',
            'prefer_provider_ad_free' => 1,
        ])->assertSessionHasErrors('owned_ad_cleanup_enabled');
    }

    public function test_external_catalog_iframe_is_unmodified_when_provider_is_not_approved(): void
    {
        Cache::put('gamemonetize.embeddable.all', [[
            'id' => 'racer',
            'name' => 'Racing Game',
            'category' => 'Racing',
            'url' => 'https://html5.gamemonetize.co/racer/',
            'image' => null,
        ]], 60);

        $this->get(route('playable.play', ['id' => 'racer']))
            ->assertOk()
            ->assertSee('data-embed-mode="third_party"', false)
            ->assertSee('data-owned-ad-cleanup="0"', false)
            ->assertSee('src="https://html5.gamemonetize.co/racer/"', false);
    }

    public function test_frontend_cleanup_is_strictly_limited_to_owned_first_party_elements(): void
    {
        $js = file_get_contents(resource_path('js/game-embed-policy.js'));

        $this->assertStringContainsString('iframe[data-embed-mode="same_origin"][data-owned-ad-cleanup="1"]', $js);
        $this->assertStringContainsString("requested.origin !== window.location.origin", $js);
        $this->assertStringContainsString("windowInFrame.location.origin !== window.location.origin", $js);
        $this->assertStringContainsString("['[data-owned-ad]', '.owned-game-ad']", $js);
        $this->assertStringContainsString('MutationObserver', $js);
        $this->assertStringNotContainsString('postMessage(', $js);
        $this->assertStringContainsString("import './game-embed-policy'", file_get_contents(resource_path('js/app.js')));
    }
}
