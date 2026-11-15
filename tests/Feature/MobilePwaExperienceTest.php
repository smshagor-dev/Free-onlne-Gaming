<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobilePwaExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::put('gamemonetize.embeddable.all', [], 60);
        Http::preventStrayRequests();
    }

    public function test_homepage_exposes_responsive_mobile_layout_and_pwa_assets(): void
    {
        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('Mobile-first homepage corrections')
            ->assertSee('grid-template-columns:repeat(2,minmax(0,1fr))', false)
            ->assertSee('rel="icon" type="image/svg+xml"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('rel="manifest"', false);

        $css = file_get_contents(resource_path('views/layouts/theme.blade.php'));
        $this->assertStringContainsString('Compact, predictable mobile header layout', $css);
        $this->assertStringContainsString('.fg-nav .fg-nav-search', $css);
        $this->assertStringContainsString('grid-template-columns:minmax(0,1fr) auto auto', $css);
    }

    public function test_manifest_uses_the_free_games_name_and_valid_png_icons(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('Free Games', $manifest['name']);
        $this->assertSame('Free Games', $manifest['short_name']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);

        $this->assertCount(2, $manifest['icons']);
        foreach ([192, 512] as $index => $size) {
            $icon = $manifest['icons'][$index];
            $this->assertSame($size.'x'.$size, $icon['sizes']);
            $this->assertSame('image/png', $icon['type']);
            $path = public_path(ltrim($icon['src'], '/'));
            $this->assertFileExists($path);
            $dimensions = getimagesize($path);
            $this->assertIsArray($dimensions);
            $this->assertSame($size, $dimensions[0]);
            $this->assertSame($size, $dimensions[1]);
            $this->assertSame('image/png', $dimensions['mime']);
        }

        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertGreaterThan(100, filesize(public_path('favicon.ico')));
    }

    public function test_service_worker_registers_and_only_caches_public_static_assets(): void
    {
        $registration = file_get_contents(resource_path('js/pwa.js'));
        $worker = file_get_contents(public_path('sw.js'));
        $entry = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('import \'./pwa\'', $entry);
        $this->assertStringContainsString('serviceWorker.register("/sw.js"', $registration);
        $this->assertStringContainsString('const STATIC_URLS = [', $worker);
        $this->assertStringContainsString('cache.addAll(STATIC_URLS)', $worker);
        $this->assertStringContainsString('"/private-files/"', $worker);
        $this->assertStringContainsString('"/api/"', $worker);
        $this->assertStringContainsString('"/sm-shagor/free-games/admin-main/control-back-office/"', $worker);
        $this->assertStringNotContainsString('cache.put(', $worker);
    }
}
