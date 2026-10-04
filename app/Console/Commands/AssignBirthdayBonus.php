<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DepositSetting;
use App\Services\BonusService;
use Carbon\Carbon;

class AssignBirthdayBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign birthday bonuses to users with birthday today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $setting = DepositSetting::where('bonus_type', 'Birthday Bonus')->first();

        if (!$setting) {
            $this->info("No Birthday Bonus setting found.");
            return;
        }

        $users = User::whereMonth('date_of_birth', Carbon::now()->month)
            ->whereDay('date_of_birth', Carbon::now()->day)
            ->get();

        $bonusService = app(BonusService::class);

        foreach ($users as $user) {
            // Fake a deposit for triggering bonus logic
            $deposit = new \App\Models\UserDeposit([
                'id' => 0,
                'user_id' => $user->id,
                'amount' => $setting->minimum_bonus,
                'gateway_id' => null
            ]);

            $bonusService->assignBonus($user, $deposit);
        }

        $this->info("Assigned birthday bonuses to " . count($users) . " users.");
    }
}
