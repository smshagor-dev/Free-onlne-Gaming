<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lottary;
use App\Http\Controllers\LottaryController;
use Carbon\Carbon;

class DrawLotteryWinners extends Command
{

    protected $signature = 'lottery:draw-winners';
    protected $description = 'Draw lottery winners';


    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $lotteries = Lottary::whereDate('draw_date', $today)
                            ->where('is_draw', 0)
                            ->get();

        $controller = new LottaryController();

        foreach ($lotteries as $lottery) {
            $controller->drawLotteryWinners($lottery->id);
            $this->info("Lottery ID {$lottery->id} drawn successfully.");
        }

        $this->info('All lotteries for today have been drawn.');
    }
}
