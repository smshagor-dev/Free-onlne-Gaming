<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\VipBonus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProcessVipBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vip:process-bonuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process VIP bonuses for eligible users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting VIP bonus processing...');
        
        // Get users with vip_bonus > 0
        $users = User::where('vip_bonus', '>', 0)->get();
        
        $processedCount = 0;
        $creditedCount = 0;
        $expiredCount = 0;
        
        foreach ($users as $user) {
            $this->info("Processing user ID: {$user->id}, VIP Bonus: {$user->vip_bonus}");
            
            // Get user's active VIP bonuses (not expired)
            $vipBonuses = VipBonus::where('user_id', $user->id)
                ->get()
                ->filter(function ($bonus) {
                    $expiresAt = Carbon::parse($bonus->created_at)->addHours($bonus->playing_time);
                    return $expiresAt > now(); 
                });
            
            foreach ($vipBonuses as $bonus) {
                $processedCount++;

                $requiredWager = $bonus->bonus_amount * $bonus->wager;
                
                if ($user->vip_bonus >= $requiredWager) {
                    DB::transaction(function () use ($user, $bonus) {
                        $user->available_balance += $bonus->bonus_amount;
                        $user->vip_bonus = 0; 
                        $user->save();
                        
                        $bonus->delete();
                    });
                    
                    $this->info("Credited bonus {$bonus->bonus_amount} to user {$user->id}");
                    $creditedCount++;
                } else {
                    $this->info("User {$user->id} has not met wager requirement: {$user->vip_bonus}/{$requiredWager}");
                }
            }
            
            $userExpiredBonuses = VipBonus::where('user_id', $user->id)
                ->get()
                ->filter(function ($bonus) {
                    $expiresAt = Carbon::parse($bonus->created_at)->addHours($bonus->playing_time);
                    return $expiresAt <= now(); 
                });
            
            foreach ($userExpiredBonuses as $expiredBonus) {
                $expiredBonus->delete();
                $expiredCount++;
            }
        }
        
        $this->info("VIP bonus processing completed.");
        $this->info("Processed: {$processedCount} bonuses");
        $this->info("Credited: {$creditedCount} bonuses");
        $this->info("Cleaned up: {$expiredCount} expired bonuses");
        
        return Command::SUCCESS;
    }
}
