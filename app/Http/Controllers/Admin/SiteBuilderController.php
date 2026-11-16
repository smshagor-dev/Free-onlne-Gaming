<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteCmsConfig;
use App\Services\SiteCms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SiteBuilderController extends Controller
{
    private const RULES = [
        'home' => [
            'badge' => ['required', 'string', 'max:80'],
            'title_start' => ['required', 'string', 'max:45'],
            'title_highlight' => ['required', 'string', 'max:45'],
            'title_end' => ['required', 'string', 'max:55'],
            'description' => ['required', 'string', 'max:300'],
            'primary_cta' => ['required', 'string', 'max:32'],
            'secondary_cta' => ['required', 'string', 'max:32'],
            'featured_heading' => ['required', 'string', 'max:65'],
            'new_heading' => ['required', 'string', 'max:65'],
            'explore_heading' => ['required', 'string', 'max:65'],
            'show_categories' => ['required', 'boolean'],
            'show_featured' => ['required', 'boolean'],
            'show_new' => ['required', 'boolean'],
            'show_explore' => ['required', 'boolean'],
        ],
        'navigation' => [
            'home_label' => ['required', 'string', 'max:25'],
            'games_label' => ['required', 'string', 'max:25'],
            'categories_label' => ['required', 'string', 'max:25'],
            'lottery_label' => ['required', 'string', 'max:25'],
            'contact_label' => ['required', 'string', 'max:25'],
            'show_home' => ['required', 'boolean'],
            'show_games' => ['required', 'boolean'],
            'show_categories' => ['required', 'boolean'],
            'show_lottery' => ['required', 'boolean'],
            'show_contact' => ['required', 'boolean'],
            'category_labels' => ['required', 'string', 'max:350'],
        ],
        'footer' => [
            'text' => ['required', 'string', 'max:200'],
            'contact_label' => ['required', 'string', 'max:32'],
            'show_contact' => ['required', 'boolean'],
            'show_games' => ['required', 'boolean'],
        ],
        'catalog' => [
            'featured_limit' => ['required', 'integer', 'between:1,12'],
            'new_limit' => ['required', 'integer', 'between:1,12'],
            'page_size' => ['required', 'integer', 'between:12,96'],
            'suggestion_limit' => ['required', 'integer', 'between:1,12'],
            'category_bar_count' => ['required', 'integer', 'between:1,10'],
            'provider_enabled' => ['required', 'boolean'],
        ],
        'pwa' => [
            'name' => ['required', 'string', 'max:45'],
            'short_name' => ['required', 'string', 'max:24'],
            'description' => ['required', 'string', 'max:160'],
            'theme_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'display' => ['required', Rule::in(['standalone', 'minimal-ui', 'browser'])],
        ],
    ];

    public function edit(Request $request)
    {
        $selected = $request->query('section', 'home');
        abort_unless(array_key_exists($selected, self::RULES), 404);

        return view('admin.site-builder.edit', [
            'selected' => $selected,
            'sections' => SiteCms::sections(),
            'config' => SiteCms::section($selected),
        ]);
    }

    public function update(Request $request, string $section): RedirectResponse
    {
        abort_unless(array_key_exists($section, self::RULES), 404);
        $validated = $request->validate(self::RULES[$section]);

        if ($section === 'navigation') {
            $categories = collect(explode(',', $validated['category_labels']))
                ->map(static fn ($name) => trim($name))
                ->filter()
                ->unique(static fn ($name) => mb_strtolower($name))
                ->take(15)
                ->values();
            if ($categories->isEmpty() || $categories->contains(
                static fn ($name) => mb_strlen($name) > 35 ||
                    preg_match('/casino|slots?|roulette|blackjack|baccarat|poker|gambling/i', $name)
            )) {
                throw ValidationException::withMessages([
                    'category_labels' => 'Use 1–15 ordinary game categories, no gambling categories, up to 35 characters each.',
                ]);
            }
            $validated['category_labels'] = $categories->implode(', ');
        }

        // Keep other sections untouched. No legacy settings or member data is deleted.
        DB::transaction(static function () use ($section, $validated): void {
            SiteCmsConfig::updateOrCreate(['section' => $section], ['payload' => $validated]);
        });
        SiteCms::clearCache();

        return redirect()->route('admin.site-builder.edit', ['section' => $section])
            ->with('success', 'The '.$section.' configuration has been saved.');
    }
}
