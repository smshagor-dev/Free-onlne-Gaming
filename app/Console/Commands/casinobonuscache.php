<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CasinoBonusController;
use Illuminate\Support\Facades\Cache;

class casinobonuscache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casinobonus:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache casino bonus games from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Cache::put('casino_bonus_response', app(CasinoBonusController::class)->getCasinoData(), 86400);
            $this->info('Casino bonus games cached successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
