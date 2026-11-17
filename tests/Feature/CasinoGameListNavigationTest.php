<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Game;
use App\Models\GamesCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CasinoGameListNavigationTest extends TestCase
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

    private function localGame(string $name = 'Safe Demo Poker'): Game
    {
        $category = GamesCategory::create(['name' => 'Casino']);
        $tag = Tag::create(['category_id' => $category->id, 'tag_name' => 'Cards', 'slug' => 'cards']);
        $game = Game::create([
            'name' => $name,
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'image' => 'https://images.example.test/poker.png',
            'games_url' => 'https://example.test/owned-demo-game',
        ]);
        $game->play_time = 17;
        $game->save();

        return $game;
    }

    private function casinoSession(User $user, string $game, string $session): int
    {
        return DB::table('casino_game_sessions')->insertGetId([
            'user_id' => $user->id, 'game_name' => $game, 'session_id' => $session,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function wager(User $user, int $sessionId, string $action, string $amount, string $id): void
    {
        DB::table('transactions')->insert([
            'user_id' => $user->id, 'session_id' => $sessionId,
            'provider_name' => 'test-provider', 'provider_transaction_id' => $id,
            'provider_action' => $action, 'status' => 'approved',
            'amount' => $amount, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function admin(): void
    {
        $admin = Admin::create([
            'name' => 'Casino Admin', 'email' => 'casino-admin-list@example.test',
            'password' => Hash::make('example-password'),
        ]);
        $this->actingAs($admin, 'admin');
    }

    public function test_front_header_and_profile_sidebar_link_to_casino_game_list(): void
    {
        $game = $this->localGame();

        $this->get(route('games.viewIndex'))->assertOk()
            ->assertSee(route('casino.games.index'), false)
            ->assertSee('Casino games');

        $this->get(route('casino.games.index'))->assertOk()
            ->assertSee('Safe Demo Poker')
            ->assertSee('https://images.example.test/poker.png')
            ->assertSee(route('games.open', ['id' => $game->id]), false)
            ->assertSee('17 recorded opens')
            ->assertSee('Sign in to play');

        $user = User::factory()->create(['google2fa_status' => false, 'is_banned' => false]);
        $this->actingAs($user)->get(route('user.dashboard'))
            ->assertOk()
            ->assertSee('My casino activity')
            ->assertSee(route('casino.games.index'), false)
            ->assertSee(route('user.casino.activity'), false);
    }

    public function test_user_activity_is_private_to_the_current_member(): void
    {
        $mine = User::factory()->create(['google2fa_status' => false, 'is_banned' => false]);
        $other = User::factory()->create(['google2fa_status' => false, 'is_banned' => false]);
        $mineSession = $this->casinoSession($mine, 'Private Roulette', 'me-one');
        $otherSession = $this->casinoSession($other, 'Other Members Only', 'you-one');
        $this->wager($mine, $mineSession, 'bet', '13.00', 'bet-me');
        $this->wager($mine, $mineSession, 'win', '8.00', 'win-me');
        $this->wager($other, $otherSession, 'bet', '900.00', 'bet-other');

        $this->get(route('user.casino.activity'))
            ->assertRedirect(route('login.form'));

        $this->actingAs($mine)->get(route('user.casino.activity'))
            ->assertOk()
            ->assertSee('Private Roulette')
            ->assertSee('13.00')
            ->assertSee('8.00')
            ->assertDontSee('Other Members Only')
            ->assertDontSee('900.00');
    }

    public function test_admin_nine_column_game_list_shows_real_local_metadata_and_signed_net(): void
    {
        $local = $this->localGame('Sample Poker');
        $user = User::factory()->create();
        $positive = $this->casinoSession($user, 'Casino Positive', 'win-one');
        $negative = $this->casinoSession($user, 'Casino Negative', 'loss-one');

        $this->wager($user, $positive, 'bet', '19.00', 'positive-bet');
        $this->wager($user, $positive, 'win', '5.00', 'positive-win');
        $this->wager($user, $negative, 'bet', '2.00', 'negative-bet');
        $this->wager($user, $negative, 'win', '9.00', 'negative-win');

        $this->admin();
        $response = $this->get(route('admin.casino.index'))->assertOk()
            ->assertSee('Image')
            ->assertSee('Game name')
            ->assertSee('Play URL')
            ->assertSee('Play time')
            ->assertSee('Total bet')
            ->assertSee('Total win')
            ->assertSee('Admin win / lose')
            ->assertSee('Sample Poker')
            ->assertSee('https://images.example.test/poker.png')
            ->assertSee('https://example.test/owned-demo-game')
            ->assertSee(route('games.open', ['id' => $local->id]), false)
            ->assertSee('Casino Positive')
            ->assertSee('Casino Negative')
            ->assertSee('+14.00')
            ->assertSee('-7.00')
            ->assertSee('casino-net is-positive')
            ->assertSee('casino-net is-negative');

        $this->assertSame(1, substr_count($response->getContent(), '>Sample Poker</strong>'));
        $this->assertStringContainsString('This is not verified operator profit', $response->getContent());
    }

    public function test_identically_named_local_and_provider_games_remain_separate_records(): void
    {
        $this->localGame('Matching Roulette');
        $user = User::factory()->create();
        $session = $this->casinoSession($user, 'Matching Roulette', 'different-source');
        $this->wager($user, $session, 'bet', '50.00', 'matching-bet');

        $this->admin();
        $response = $this->get(route('admin.casino.index'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(2, substr_count($html, '>Matching Roulette</strong>'));
        $this->assertStringContainsString('Locally managed game', $html);
        $this->assertStringContainsString('Recorded provider session', $html);
        $response->assertSee('50.00');
        // The local entry is not assigned the provider's gross bet amount
        // just because the two sources happen to share a game name.
        preg_match('/<tr>\\s*<td>[\\s\\S]*?<strong>Matching Roulette<\\/strong>[\\s\\S]*?Locally managed game[\\s\\S]*?<\\/tr>/', $html, $matches);
        $this->assertNotEmpty($matches, 'The local-source row should exist.');
        $this->assertStringNotContainsString('50.00', $matches[0]);
    }

    public function test_public_casino_search_does_not_expose_other_game_categories(): void
    {
        $this->localGame('Casino Explorer');
        $this->get(route('casino.games.index', ['search' => 'Missing']))
            ->assertOk()
            ->assertDontSee('Casino Explorer');

        $this->get(route('casino.games.index', ['search' => str_repeat('X', 81)]))
            ->assertSessionHasErrors('search');

        $this->post(route('user.casino.activity'))->assertStatus(405);
    }
}
