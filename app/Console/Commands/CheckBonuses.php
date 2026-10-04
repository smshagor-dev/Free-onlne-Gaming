<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BonusUser;
use App\Models\User;
use App\Services\BonusService;
use Carbon\Carbon;

class CheckBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check bonuses and expire or release them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bonusService = app(BonusService::class);

        $bonuses = BonusUser::with(['user', 'depositSetting'])->get();

        $activeBonuses = $bonuses->filter(function ($bonusUser) {
            if (!$bonusUser->depositSetting) {
                return false;
            }

            $createdAt = Carbon::parse($bonusUser->created_at);

            $bonusTime = strtolower($bonusUser->depositSetting->bonus_time);

            [$value, $unit] = explode(' ', $bonusTime, 2);

            $value = (int) $value;

            $expiryTime = $createdAt->copy()->add($value, $unit);

            return now()->lt($expiryTime);
        });

        foreach ($activeBonuses as $bonusUser) {
            $user = $bonusUser->user;
            $bonusService->releaseBonus($user, $bonusUser);
        }

        $this->info("Checked " . count($activeBonuses) . " active bonuses.");
    }
}
