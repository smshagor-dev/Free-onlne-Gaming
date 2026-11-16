<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlayerDashboardDesignTest extends TestCase
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

    public function test_player_dashboard_still_requires_authentication(): void
    {
        $this->get(route('user.dashboard'))->assertRedirect(route('login.form'));
    }

    public function test_reference_design_renders_for_the_real_user_without_static_mock_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Alex Player',
            'google2fa_status' => false,
            'is_banned' => false,
        ]);

        $this->actingAs($user)->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('player-dashboard-route')
            ->assertSee('class="player-welcome"', false)
            ->assertSee('Ready for your next')
            ->assertSee('Alex Player?')
            ->assertSee('images/player-dashboard-controller.svg')
            ->assertSee('FREE TO PLAY. YOUR WAY.')
            ->assertSee('class="player-stats"', false)
            ->assertSee('Favorites')
            ->assertSee('Saved games')
            ->assertSee('Games played')
            ->assertSee('Lottery tickets')
            ->assertSee('Continue playing')
            ->assertSee('Your favorites')
            ->assertSee('No games played yet')
            ->assertSee('Your next favorite is waiting')
            ->assertSee('LOTTERY CORNER')
            ->assertSee('My tickets')
            ->assertSee('View winning results')
            ->assertSee('Profile &amp; password', false)
            ->assertSee('Account security')
            ->assertSee('Gaming preferences')
            ->assertSee('PLAYER COMMAND CENTER')
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee('class="cms-topbar"', false)
            ->assertSee('data-cms-menu-toggle', false)
            ->assertSee('aria-label="Player navigation"', false)
            ->assertSee('class="cms-footer"', false)
            ->assertDontSee('class="account-nav"', false)
            ->assertDontSee('class="fg-topbar"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="POST"', false);

        $this->assertFileExists(public_path('images/player-dashboard-controller.svg'));
    }

    public function test_user_display_name_is_escaped_in_hero(): void
    {
        $user = User::factory()->create([
            'name' => '<img src=x onerror=alert(1)>',
            'google2fa_status' => false,
            'is_banned' => false,
        ]);

        $this->actingAs($user)->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_dashboard_has_responsive_layouts_and_usable_empty_states(): void
    {
        $stylesheet = file_get_contents(resource_path('views/user/dashboard-style.blade.php'));

        $this->assertStringContainsString('grid-template-columns:repeat(4,minmax(0,1fr))', $stylesheet);
        $this->assertStringContainsString('grid-template-columns:repeat(2,minmax(0,1fr))', $stylesheet);
        $this->assertStringContainsString('@media(max-width:900px)', $stylesheet);
        $this->assertStringContainsString('@media(max-width:650px)', $stylesheet);
        $this->assertStringContainsString('@media(max-width:365px)', $stylesheet);
        $this->assertStringContainsString('.player-welcome', $stylesheet);
        $this->assertStringContainsString('.player-empty', $stylesheet);
        $this->assertStringContainsString('.player-dashboard-route .account-nav', $stylesheet);
        $this->assertStringContainsString('prefers-reduced-motion', $stylesheet);
    }
}
