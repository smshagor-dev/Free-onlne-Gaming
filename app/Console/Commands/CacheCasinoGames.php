<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CasinoController;
use Illuminate\Support\Facades\Cache;

class CacheCasinoGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casino:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache casino games from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Cache::put('casino_raw_response', app(CasinoController::class)->getCasinoData(), 86400);
            $this->info('Casino games cached successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
