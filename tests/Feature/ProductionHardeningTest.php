<?php

namespace Tests\Feature;

use App\Models\CatalogRecentGame;
use App\Models\CatalogSavedGame;
use App\Models\User;
use App\Models\UserGamePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_cleanup_deletes_only_expired_demo_users_and_cascades_personalization(): void
    {
        $expiredDemo = User::factory()->create([
            'registration_type' => 'demo',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        $freshDemo = User::factory()->create(['registration_type' => 'demo']);
        $realUser = User::factory()->create([
            'registration_type' => 'Email',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ]);

        CatalogSavedGame::create(['user_id' => $expiredDemo->id, 'provider' => 'rawg', 'provider_game_id' => '1', 'title' => 'Expired Saved']);
        CatalogRecentGame::create(['user_id' => $expiredDemo->id, 'provider' => 'rawg', 'provider_game_id' => '2', 'title' => 'Expired Recent', 'viewed_at' => now()]);
        UserGamePreference::create(['user_id' => $expiredDemo->id, 'preferred_genres' => ['action'], 'preferred_platforms' => ['pc']]);
        DB::table('sessions')->insert([
            'id' => 'expired-demo-session',
            'user_id' => $expiredDemo->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->artisan('demo:cleanup', ['--days' => 7])->assertExitCode(0);

        $this->assertNull(User::withTrashed()->find($expiredDemo->id));
        $this->assertNotNull(User::find($freshDemo->id));
        $this->assertNotNull(User::find($realUser->id));
        $this->assertDatabaseMissing('catalog_saved_games', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('catalog_recent_games', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('user_game_preferences', ['user_id' => $expiredDemo->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $expiredDemo->id]);
    }

    public function test_sitemap_and_error_pages_render_without_debug_details(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(route('catalog.index'), false);

        foreach ([403, 404, 419, 429, 500, 503] as $status) {
            $this->view("errors.$status")
                ->assertSee((string) $status)
                ->assertDontSee('Stack trace')
                ->assertDontSee(base_path());
        }
    }
}
