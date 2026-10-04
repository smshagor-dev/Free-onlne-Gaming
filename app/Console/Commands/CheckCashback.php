<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CashbackUser;
use Carbon\Carbon;

class CheckCashback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashback:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check users cashback and update available balance based on wager and playing time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cashback check...');

        $cashbackUsers = CashbackUser::with('user')->get();

        foreach ($cashbackUsers as $cashback) {
            $user = $cashback->user;

            if (!$user) continue;

            $initialAmount = $cashback->amount;
            $wagerRequirement = $cashback->wager;
            $playingTimeHours = $cashback->playing_time;
            $finishedTime = Carbon::parse($cashback->created_at)->addHours($playingTimeHours);

            $this->info("Checking user {$user->id}, cashback: {$initialAmount}");

            $totalNeeded = $initialAmount * $wagerRequirement;

            // Calculate user's total play amount within cashback window
            // For simplicity, we assume $user->played_amount tracks total played within timeframe
            // You need to replace this with your actual calculation
            $totalPlayed = $user->played_amount ?? 0;

            if (Carbon::now()->greaterThanOrEqualTo($finishedTime)) {
                // Time finished
                if ($totalPlayed >= $totalNeeded) {
                    // User completed wager requirement -> add initial cashback to available balance
                    $user->available_balance += $initialAmount;
                    $user->save();
                    $this->info("User {$user->id} met wager requirement. Added {$initialAmount} to available balance.");
                } else {
                    $this->info("User {$user->id} did NOT meet wager requirement. Cashback expired.");
                }

                // Reset cashback in user table if needed
                $user->cashback_balance = 0;
                $user->save();
            } else {
                $this->info("User {$user->id} still within playing time. Waiting...");
            }
        }

        $this->info('Cashback check finished.');
    }
}
