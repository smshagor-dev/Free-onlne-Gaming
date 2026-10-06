<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule your commands here instead of Kernel.php if you want
Schedule::command('lottery:draw-winners')->dailyAt('23:59')->withoutOverlapping();
Schedule::command('bonus:check')->hourly()->withoutOverlapping();
Schedule::command('bonus:birthday')->dailyAt('00:01')->withoutOverlapping();
Schedule::command('casino:cache')->dailyAt('15:30')->withoutOverlapping();
Schedule::command('bonus:clear-expired')->hourly()->withoutOverlapping();
Schedule::command('user:check-level')->dailyAt('00:30')->withoutOverlapping();
Schedule::command('cashback:check')->hourly()->withoutOverlapping();
Schedule::command('vip:process-bonuses')->hourly()->withoutOverlapping();
Schedule::command('casinobonus:cache')->dailyAt('18:30')->withoutOverlapping();
Schedule::command('casino:cachecashback')->dailyAt('17:30')->withoutOverlapping();
Schedule::command('casino:cachevip')->dailyAt('16:30')->withoutOverlapping();
Schedule::command('demo:cleanup')->dailyAt('02:20')->withoutOverlapping();
