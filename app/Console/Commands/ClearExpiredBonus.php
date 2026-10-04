<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BonusUser;
use App\Models\DepositSetting;
use App\Models\User;
use Carbon\Carbon;

class ClearExpiredBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:clear-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired bonuses and set bonus_balance to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $bonusUsers = BonusUser::with('depositSetting')->get();
        $clearedCount = 0;

        foreach ($bonusUsers as $bonusUser) {

            if (!$bonusUser->depositSetting) {
                continue; 
            }

            $createdAt = Carbon::parse($bonusUser->created_at);
            $bonusTime = strtolower($bonusUser->depositSetting->bonus_time); 

            if (str_contains($bonusTime, 'hour')) {
                $hours = intval($bonusTime);
                $expiryTime = $createdAt->copy()->addHours($hours);
            } elseif (str_contains($bonusTime, 'day')) {
                $days = intval($bonusTime);
                $expiryTime = $createdAt->copy()->addDays($days);
            } elseif (str_contains($bonusTime, 'month')) {
                $months = intval($bonusTime);
                $expiryTime = $createdAt->copy()->addMonths($months);
            } else {
                $expiryTime = $createdAt; 
            }

            if ($now->greaterThanOrEqualTo($expiryTime)) {
                $user = User::find($bonusUser->user_id);
                if ($user && $user->bonus_balance > 0) {
                    $user->bonus_balance = 0;
                    $user->save();
                    $clearedCount++;
                }
            }
        }

        $this->info("Expired bonuses cleared: {$clearedCount}");
    }
}
