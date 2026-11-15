<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveGameSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_suggestions_match_the_playable_catalog_without_external_requests(): void
    {
        Cache::put('gamemonetize.embeddable.all', [
            ['id' => 'alpha', 'name' => 'Alpha Racer', 'category' => 'Racing', 'image' => 'https://images.test/alpha.jpg'],
            ['id' => 'beta', 'name' => 'Beta Puzzle', 'category' => 'Puzzle', 'image' => null],
            ['id' => 'casino', 'name' => 'Alpha Casino', 'category' => 'Casino', 'image' => null],
        ], 60);

        $this->getJson(route('playable.suggest', ['q' => 'ALPHA']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alpha Racer')
            ->assertJsonPath('data.0.category', 'Racing')
            ->assertJsonPath('data.0.image', 'https://images.test/alpha.jpg')
            ->assertJsonPath('data.0.url', route('playable.play', ['id' => 'alpha']));

        $this->getJson(route('playable.suggest', ['q' => 'notfound']))
            ->assertOk()
            ->assertExactJson(['data' => []]);

        Http::assertNothingSent();
    }

    public function test_suggestions_are_bounded_to_eight_items(): void
    {
        $games = [];
        for ($i = 0; $i < 15; $i++) {
            $games[] = [
                'id' => 'game'.$i,
                'name' => 'Racing Game '.$i,
                'category' => 'Racing',
                'image' => null,
            ];
        }
        Cache::put('gamemonetize.embeddable.all', $games, 60);

        $this->getJson(route('playable.suggest', ['q' => 'racing']))
            ->assertOk()
            ->assertJsonCount(8, 'data');
    }

    public function test_suggestion_query_is_validated(): void
    {
        $this->getJson(route('playable.suggest', ['q' => 'x']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');

        $this->getJson(route('playable.suggest', ['q' => str_repeat('a', 81)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_navigation_search_is_after_the_menu_and_keeps_a_get_fallback(): void
    {
        Cache::put('gamemonetize.embeddable.all', [], 60);
        $response = $this->get(route('playable.index'))->assertOk();
        $html = $response->getContent();

        $menuPos = strpos($html, 'class="fg-nav-links"');
        $searchPos = strpos($html, 'class="fg-nav-search"');
        $actionsPos = strpos($html, 'class="fg-actions"');

        $this->assertNotFalse($menuPos);
        $this->assertNotFalse($searchPos);
        $this->assertNotFalse($actionsPos);
        $this->assertTrue($menuPos < $searchPos && $searchPos < $actionsPos);
        $response->assertSee('data-suggest-url="'.route('playable.suggest').'"', false)
            ->assertSee('aria-autocomplete="list"', false)
            ->assertSee('method="GET"', false);
    }
}
