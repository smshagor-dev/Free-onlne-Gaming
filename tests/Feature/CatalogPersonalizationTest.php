<?php

namespace Tests\Feature;

use App\Models\CatalogRecentGame;
use App\Models\CatalogSavedGame;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatalogPersonalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array', 'games.rawg.key' => 'test-key', 'games.rawg.base_url' => 'https://rawg.test/api']);
    }

    private function fakeRawg(string $id = '10', string $name = 'Alpha', array $extra = []): void
    {
        Http::fake(['rawg.test/api/games/*' => Http::response(array_merge(['id' => (int) $id, 'name' => $name, 'genres' => [['name' => 'Action']], 'platforms' => [['platform' => ['name' => 'PC']]]], $extra), 200)]);
    }

    public function test_catalog_game_can_be_saved_without_duplicates_and_removed(): void
    {
        $user = User::factory()->create(); $this->fakeRawg();
        $this->actingAs($user)->post('/user/catalog/saved/rawg/10')->assertRedirect();
        $this->actingAs($user)->post('/user/catalog/saved/rawg/10')->assertRedirect();
        $this->assertDatabaseCount('catalog_saved_games', 1);
        $this->actingAs($user)->delete('/user/catalog/saved/rawg/10')->assertRedirect();
        $this->assertDatabaseCount('catalog_saved_games', 0);
    }

    public function test_saved_game_ownership_is_isolated(): void
    {
        $a = User::factory()->create(); $b = User::factory()->create();
        CatalogSavedGame::create(['user_id'=>$a->id,'provider'=>'rawg','provider_game_id'=>'1','title'=>'Private']);
        $this->actingAs($b)->delete('/user/catalog/saved/rawg/1')->assertRedirect();
        $this->assertDatabaseHas('catalog_saved_games', ['user_id'=>$a->id,'provider_game_id'=>'1']);
    }

    public function test_recently_viewed_is_deduplicated_ordered_and_limited(): void
    {
        $user = User::factory()->create();
        for ($i=1; $i<=55; $i++) CatalogRecentGame::create(['user_id'=>$user->id,'provider'=>'rawg','provider_game_id'=>(string)$i,'title'=>'G'.$i,'viewed_at'=>now()->subMinutes(55-$i)]);
        $this->fakeRawg('99','Newest');
        $this->actingAs($user)->get('/games/rawg/99')->assertOk();
        $this->assertSame(50, CatalogRecentGame::where('user_id',$user->id)->count());
        $this->assertSame('99', CatalogRecentGame::where('user_id',$user->id)->latest('viewed_at')->value('provider_game_id'));
    }

    public function test_preferences_and_personalized_home_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/user/catalog/settings', ['preferred_genres'=>['Action'],'preferred_platforms'=>['PC']])->assertRedirect();
        $this->assertDatabaseHas('user_game_preferences', ['user_id'=>$user->id]);
        Http::fake(['*' => Http::response([], 200)]);
        $this->actingAs($user)->get('/games')->assertOk()->assertSee('For You');
    }

    public function test_guest_save_route_requires_login(): void
    {
        $this->post('/user/catalog/saved/rawg/1')->assertRedirect('/login');
    }

    public function test_demo_login_creates_isolated_normal_user_and_blocks_financial_mutations(): void
    {
        $this->post('/demo')->assertRedirect('/games');
        $user = auth()->user();
        $this->assertSame('demo', $user->registration_type);
        $this->assertSame(0.0, (float) $user->balance);
        $this->post('/user/bonus/claim/welcome')->assertForbidden();
        $this->get('/casino/play')->assertForbidden();
    }

    public function test_demo_sessions_are_isolated(): void
    {
        $this->post('/demo'); $first = auth()->id(); auth()->logout(); session()->invalidate();
        $this->post('/demo'); $second = auth()->id();
        $this->assertNotSame($first, $second);
    }

    public function test_existing_casino_favorite_route_is_preserved(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes());
        $this->assertTrue($routes->contains(fn ($route) => $route->getName() === 'user.game.toggleFavourite'));
        $this->assertTrue($routes->contains(fn ($route) => $route->getName() === 'catalog.user.saved.store'));
    }
}
