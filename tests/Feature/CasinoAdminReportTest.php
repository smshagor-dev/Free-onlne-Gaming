<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Game;
use App\Models\GameOpen;
use App\Models\GamesCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CasinoAdminReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    private function loginAdmin(): void
    {
        $admin = Admin::create([
            'name' => 'Casino Report Admin',
            'email' => 'casino-report-admin@example.test',
            'password' => Hash::make('test-password'),
        ]);
        $this->actingAs($admin, 'admin');
    }

    private function recordCasinoSession(User $user, string $game, string $sessionId): int
    {
        return DB::table('casino_game_sessions')->insertGetId([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'game_name' => $game,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function recordCasinoTransaction(User $user, ?int $sessionId, string $action, string $amount, string $status = 'approved', bool $verified = true): void
    {
        static $nextId = 0;
        $nextId++;

        DB::table('transactions')->insert([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'amount' => $amount,
            'status' => $status,
            'provider_name' => $verified ? 'test-provider' : null,
            'provider_transaction_id' => $verified ? 'event-'.$nextId : null,
            'provider_action' => $action,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_casino_reports_require_admin_guard(): void
    {
        $this->get(route('admin.casino.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.casino.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_empty_report_does_not_invent_wagers_or_gameplay(): void
    {
        $this->loginAdmin();

        $this->get(route('admin.casino.index'))
            ->assertOk()
            ->assertSee('Casino games &amp; player reports', false)
            ->assertSee('Casino game table')
            ->assertSee('Player-by-game table')
            ->assertSee('Locally managed casino-style games')
            ->assertSee('No casino sessions recorded yet.')
            ->assertSee('No matching player activity records.')
            ->assertSee('no transaction-level currency')
            ->assertSee('class="cms-sidebar"', false)
            ->assertSee('Casino games &amp; activity', false)
            ->assertDontSee('Approve wager')
            ->assertDontSee('Settle bet');
    }

    public function test_session_and_provider_transaction_totals_are_correct_without_join_multiplication(): void
    {
        $memberA = User::factory()->create(['name' => 'Casino Member A']);
        $memberB = User::factory()->create(['name' => 'Casino Member B']);
        $s1 = $this->recordCasinoSession($memberA, 'Sample Roulette', 's1');
        $s2 = $this->recordCasinoSession($memberA, 'Sample Roulette', 's2');
        $s3 = $this->recordCasinoSession($memberB, 'Sample Roulette', 's3');
        $this->recordCasinoSession($memberB, 'Sample Slots', 's4');

        $this->recordCasinoTransaction($memberA, $s1, 'bet', '10.50');
        $this->recordCasinoTransaction($memberA, $s1, 'bet', '2.00');
        $this->recordCasinoTransaction($memberA, $s1, 'win', '7.25');
        $this->recordCasinoTransaction($memberA, $s2, 'bet', '3.00');
        $this->recordCasinoTransaction($memberB, $s3, 'win', '1.00');

        // Not confirmed provider wager actions; must be excluded.
        $this->recordCasinoTransaction($memberA, $s1, 'bet', '600.00', 'pending');
        $this->recordCasinoTransaction($memberA, $s1, 'win', '500.00', 'reject');
        $this->recordCasinoTransaction($memberA, $s1, 'bet', '400.00', 'approved', false);
        $this->recordCasinoTransaction($memberA, $s1, 'rollback', '100.00');
        $this->recordCasinoTransaction($memberA, null, 'bet', '200.00');
        $this->recordCasinoTransaction($memberB, $s1, 'bet', '100.00'); // wrong session owner

        $this->loginAdmin();
        $response = $this->get(route('admin.casino.index'))->assertOk();

        $response->assertSee('Sample Roulette')
            ->assertSee('Sample Slots')
            ->assertSee('Casino Member A')
            ->assertSee('Casino Member B')
            ->assertSee('15.50')  // exact approved bet sum
            ->assertSee('8.25')   // exact approved win sum
            ->assertSee('15.50')  // player A across both Roulette sessions
            ->assertSee('7.25')
            ->assertDontSee('600.00')
            ->assertDontSee('500.00')
            ->assertDontSee('400.00')
            ->assertDontSee('200.00')
            ->assertDontSee('100.00');

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/Sample Roulette<\/strong>[\s\S]*?2\s*<small[^>]*>\(unique\)<\/small>[\s\S]*?<td[^>]*>3<\/td>/', $html);
        $this->assertMatchesRegularExpression('/Sample Slots<\/strong>[\s\S]*?1\s*<small[^>]*>\(unique\)<\/small>[\s\S]*?<td[^>]*>1<\/td>/', $html);
    }

    public function test_filters_are_read_only_and_do_not_change_database_rows(): void
    {
        $member = User::factory()->create(['name' => 'Filter Member']);
        $this->recordCasinoSession($member, 'Demo Poker', 'first');
        $this->recordCasinoSession($member, 'Demo Blackjack', 'second');
        $this->loginAdmin();

        $this->get(route('admin.casino.index', ['search' => 'Poker', 'user_id' => $member->id]))
            ->assertOk()
            ->assertSee('Demo Poker')
            ->assertDontSee('Demo Blackjack');

        $this->get(route('admin.casino.index', ['search' => str_repeat('x', 81)]))
            ->assertSessionHasErrors('search');

        $this->assertSame(2, DB::table('casino_game_sessions')->count());
        $this->assertSame(0, DB::table('transactions')->count());
    }

    public function test_locally_managed_casino_game_uses_existing_open_counter_not_fake_bets(): void
    {
        $category = GamesCategory::create(['name' => 'Casino']);
        $tag = Tag::create(['category_id' => $category->id, 'tag_name' => 'Card', 'slug' => 'card']);
        $game = Game::create([
            'name' => 'Classic Blackjack',
            'category_id' => $category->id,
            'tag_id' => $tag->id,
            'games_url' => 'https://example.test/blackjack',
        ]);
        $game->play_time = 13;
        $game->save();
        $member = User::factory()->create();
        GameOpen::create(['user_id' => $member->id, 'game_id' => $game->id, 'points' => 0]);

        $this->loginAdmin();
        $this->get(route('admin.casino.index'))->assertOk()
            ->assertSee('Classic Blackjack')
            ->assertSee('13')
            ->assertSee(route('admin.games.edit', $game->id), false)
            ->assertSee('Casino')
            ->assertSee('not linked to provider settlement sessions');
    }

    public function test_game_names_are_escaped_and_casino_activity_endpoint_is_get_only(): void
    {
        $user = User::factory()->create();
        $name = '<script>alert("casino")</script>';
        $this->recordCasinoSession($user, $name, 'escaped');
        $this->loginAdmin();

        $this->get(route('admin.casino.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;casino&quot;)&lt;/script&gt;', false)
            ->assertDontSee($name, false);

        $this->post(route('admin.casino.index'))->assertStatus(405);
    }
}
