<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Database\Eloquent\Builder;

/**
 * The site's locally managed casino-style games only. Never unfilters
 * the remote GameMonetize feed or assumes an arbitrary provider is approved.
 */
class CasinoGameCatalog
{
    public static function query(): Builder
    {
        return Game::query()->where(function (Builder $query): void {
            foreach (['casino', 'slot', 'roulette', 'poker', 'blackjack', 'baccarat'] as $word) {
                $query->orWhere('name', 'like', '%'.$word.'%')
                    ->orWhereHas('category', function (Builder $category) use ($word): void {
                        $category->where('name', 'like', '%'.$word.'%');
                    });
            }
        });
    }
}
