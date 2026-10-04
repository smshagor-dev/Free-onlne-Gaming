<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CasinoCashbackController;
use Illuminate\Support\Facades\Cache;

class casinocashbackcache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casino:cachecashback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache casino games from API for cashback';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Cache::put('casino_cashback_response', app(CasinoCashbackController::class)->getCasinoData(), 86400);
            $this->info('Casino cashback games cached successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
