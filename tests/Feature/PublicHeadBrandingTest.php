<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicHeadBrandingTest extends TestCase
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

    public function test_public_head_does_not_leak_uncompiled_blade_statements(): void
    {
        Setting::create([
            'name' => 'Free Games',
            'seo_title' => 'Free Browser Games',
            'seo_description' => 'Play free games online.',
            'meta_tag' => 'arcade games, puzzle games',
            'favicon' => 'settings/favicon.png',
            'thumbnail_image' => 'settings/preview.png',
            'logo' => 'settings/logo.png',
        ]);
        Cache::forget('app.settings.first');

        $response = $this->get(route('games.viewIndex'))->assertOk();
        $html = $response->getContent();
        $beforeHeader = strstr($html, '<header class="fg-topbar">', true);

        $this->assertNotFalse($beforeHeader, 'The public header must still render.');
        foreach (['@if(', '@else', '@endif', '@php', '@endphp', '{{ ', '{!! ', '<?php'] as $fragment) {
            $this->assertStringNotContainsString($fragment, $beforeHeader, $fragment.' leaked into HTML');
        }

        $response
            ->assertSee('<title>Free Browser Games</title>', false)
            ->assertSee('<meta name="keywords" content="arcade games, puzzle games">', false)
            ->assertSee('<meta property="og:image" content=', false)
            ->assertSee('settings/favicon.png')
            ->assertSee('class="fg-brand-icon has-custom-logo"', false)
            ->assertSee('class="fg-brand-image"', false);
    }

    public function test_custom_logo_has_no_blue_tile_but_fallback_keeps_its_badge(): void
    {
        $css = file_get_contents(resource_path('views/layouts/theme.blade.php'));
        $this->assertStringContainsString('.fg-nav .fg-brand-icon.has-custom-logo', $css);
        $this->assertStringContainsString('background:transparent!important;', $css);
        $this->assertStringContainsString('box-shadow:none!important;', $css);

        Setting::create(['name' => 'Free Games']);
        Cache::forget('app.settings.first');

        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('class="fg-brand-icon has-fallback-logo"', false)
            ->assertDontSee('class="fg-brand-image"', false);
    }

    public function test_metadata_escapes_admin_supplied_keywords(): void
    {
        Setting::create([
            'name' => 'Free Games',
            'meta_tag' => 'arcade" ><script>alert(1)</script>',
        ]);
        Cache::forget('app.settings.first');

        $response = $this->get(route('games.viewIndex'))->assertOk();
        $response
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('arcade&quot; &gt;&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_public_head_works_without_settings_row(): void
    {
        Cache::forget('app.settings.first');
        $response = $this->get(route('games.viewIndex'))->assertOk();

        $html = $response->getContent();
        $beforeHeader = strstr($html, '<header class="fg-topbar">', true);
        $this->assertNotFalse($beforeHeader);

        foreach (['@if(', '@endif', '@php', '<?php'] as $fragment) {
            $this->assertStringNotContainsString($fragment, $beforeHeader);
        }

        $response->assertSee('class="fg-brand-icon has-fallback-logo"', false);
    }
}
