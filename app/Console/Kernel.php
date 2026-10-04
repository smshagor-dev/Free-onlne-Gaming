<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // Register your custom command
        \App\Console\Commands\DrawLotteryWinners::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run the lottery draw command daily at 00:00
        $schedule->command('lottery:draw-winners')->dailyAt('00:01');

        $schedule->command('bonus:check')->hourly();

        $schedule->command('bonus:birthday')->dailyAt('00:01');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
