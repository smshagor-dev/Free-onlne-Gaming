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
        // Laravel 12 schedules are registered in routes/console.php.
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
