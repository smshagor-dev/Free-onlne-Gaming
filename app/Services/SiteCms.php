<?php

namespace App\Services;

use App\Models\SiteCmsConfig;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SiteCms
{
    public const CACHE_KEY = 'cms.site-config.v1';

    private const DEFAULTS = [
        'home' => [
            'badge' => '100% FREE TO PLAY',
            'title_start' => 'Play',
            'title_highlight' => 'Free Games',
            'title_end' => 'Online Anytime',
            'description' => 'Discover free browser games. No download required. Just pure fun!',
            'primary_cta' => 'Play Now',
            'secondary_cta' => 'Browse Games',
            'featured_heading' => 'Featured Games',
            'new_heading' => 'New Games',
            'explore_heading' => 'Explore More Games',
            'show_categories' => true,
            'show_featured' => true,
            'show_new' => true,
            'show_explore' => true,
        ],
        'navigation' => [
            'home_label' => 'Home',
            'games_label' => 'Games',
            'categories_label' => 'Categories',
            'lottery_label' => 'Lottery',
            'contact_label' => 'Contact',
            'show_home' => true,
            'show_games' => true,
            'show_categories' => true,
            'show_lottery' => true,
            'show_contact' => true,
            'category_labels' => 'Action, Adventure, Racing, Shooting, Sports, Puzzle, Multiplayer, Simulation, Arcade, Strategy',
        ],
        'footer' => [
            'text' => 'Free browser games',
            'contact_label' => 'Contact',
            'show_contact' => true,
            'show_games' => false,
            'show_privacy' => false,
        ],
        'catalog' => [
            'featured_limit' => 6,
            'new_limit' => 6,
            'page_size' => 48,
            'suggestion_limit' => 8,
            'category_bar_count' => 8,
            'provider_enabled' => true,
        ],
        'embeds' => [
            'owned_ad_cleanup_enabled' => false,
            'prefer_provider_ad_free' => false,
        ],
        'pwa' => [
            'name' => 'Free Games',
            'short_name' => 'Free Games',
            'description' => 'Play free browser games and discover favorites.',
            'theme_color' => '#071827',
            'background_color' => '#071827',
            'display' => 'standalone',
        ],
    ];

    public static function defaults(string $section): array
    {
        return self::DEFAULTS[$section] ?? [];
    }

    public static function sections(): array
    {
        return array_keys(self::DEFAULTS);
    }

    public static function section(string $section): array
    {
        $defaults = self::defaults($section);
        if ($defaults === []) return [];

        try {
            $saved = Cache::remember(self::CACHE_KEY, now()->addMinutes(10), static function (): array {
                return SiteCmsConfig::query()->pluck('payload', 'section')->all();
            });
            $values = $saved[$section] ?? [];
        } catch (Throwable) {
            // Existing sites must keep rendering before migrations and during outages.
            $values = [];
        }

        return array_replace($defaults, is_array($values) ? array_intersect_key($values, $defaults) : []);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
