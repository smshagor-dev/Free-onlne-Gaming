<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule your commands here instead of Kernel.php if you want
Schedule::command('lottery:draw-winners')->dailyAt('23:59')->withoutOverlapping();
Schedule::command('user:check-level')->dailyAt('00:30')->withoutOverlapping();
Schedule::command('demo:cleanup')->dailyAt('02:20')->withoutOverlapping();
