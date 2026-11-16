<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SharedPortalSidebarTest extends TestCase
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

    public function test_user_dashboard_uses_same_fixed_sidebar_structure_as_admin(): void
    {
        $user = User::factory()->create([
            'name' => 'Sidebar Player',
            'google2fa_status' => false,
            'is_banned' => false,
        ]);

        $this->actingAs($user)->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('class="cms-shell"', false)
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee('class="cms-sidebar-scroll"', false)
            ->assertSee('class="cms-sidebar-foot"', false)
            ->assertSee('class="cms-stage"', false)
            ->assertSee('class="cms-topbar"', false)
            ->assertSee('data-cms-backdrop', false)
            ->assertSee('data-cms-menu-toggle', false)
            ->assertSee('aria-controls="cms-sidebar"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('PLAYER COMMAND CENTER')
            ->assertSee('Sidebar Player')
            ->assertSee('aria-current="page"', false)
            ->assertSee(route('user.profile'), false)
            ->assertSee(route('catalog.user.library'), false)
            ->assertSee(route('user.games.viewFavorites'), false)
            ->assertSee(route('user.notifications.index'), false)
            ->assertSee(route('2fa.setup'), false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertDontSee('class="account-nav"', false)
            ->assertDontSee('class="fg-topbar"', false)
            ->assertDontSee('ADMIN COMMAND CENTER');
    }

    public function test_other_authenticated_user_pages_share_the_sidebar(): void
    {
        $user = User::factory()->create([
            'google2fa_status' => false,
            'is_banned' => false,
        ]);

        foreach ([
            route('user.profile'),
            route('user.games.viewFavorites'),
        ] as $uri) {
            $this->actingAs($user)->get($uri)
                ->assertOk()
                ->assertSee('class="cms-sidebar"', false)
                ->assertSee('PLAYER COMMAND CENTER')
                ->assertDontSee('class="account-nav"', false);
        }
    }

    public function test_admin_portal_keeps_its_exact_sidebar_and_separate_logout(): void
    {
        $admin = Admin::create([
            'name' => 'CMS Admin',
            'email' => 'shared-admin@example.test',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee('ADMIN COMMAND CENTER')
            ->assertSee('data-cms-menu-toggle', false)
            ->assertSee('action="'.route('admin.logout').'"', false)
            ->assertDontSee('PLAYER COMMAND CENTER');
    }

    public function test_public_pages_and_guests_do_not_receive_the_user_sidebar(): void
    {
        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('class="fg-topbar"', false)
            ->assertDontSee('class="cms-sidebar"', false);

        $this->get(route('user.dashboard'))->assertRedirect(route('login.form'));
    }

    public function test_shared_sidebar_mobile_controls_and_assets_are_present(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $partial = file_get_contents(resource_path('views/layouts/partials/portal-sidebar.blade.php'));
        $style = file_get_contents(resource_path('views/layouts/partials/player-portal-style.blade.php'));
        $adminJs = file_get_contents(resource_path('js/admin-cms.js'));

        $this->assertStringContainsString("@vite('resources/js/admin-cms.js')", $layout);
        $this->assertStringContainsString("layouts.partials.player-portal", $layout);
        $this->assertStringContainsString("layouts.partials.portal-sidebar", file_get_contents(resource_path('views/layouts/partials/player-portal.blade.php')));
        $this->assertStringContainsString("class=\"cms-sidebar\"", $partial);
        $this->assertStringContainsString("position:fixed", file_get_contents(resource_path('css/admin-cms.css')));
        $this->assertStringContainsString("cms-menu-open", $adminJs);
        $this->assertStringContainsString("@media(max-width:900px)", file_get_contents(resource_path('css/admin-cms.css')));
        $this->assertStringContainsString("@media(max-width:900px)", $style);
    }
}
