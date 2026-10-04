<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CasinoVipBonusController;
use Illuminate\Support\Facades\Cache;

class casinovipcache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casino:cachevip';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache casino vip games from API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Cache::put('casino_vip_response', app(CasinoVipBonusController::class)->getCasinoData(), 86400);
            $this->info('Casino vip games cached successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
