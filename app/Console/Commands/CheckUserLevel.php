<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Level;

class CheckUserLevel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:check-level';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update user level based on points';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting user level check...');

        $users = User::all();

        $levels = Level::orderBy('points', 'asc')->get();

        foreach ($users as $user) {
            $userPoints = $user->points; 
            $userLevelId = null;

            foreach ($levels as $level) {
                if ($userPoints >= $level->points) {
                    $userLevelId = $level->id;
                } else {
                    break; 
                }
            }

            if ($userLevelId && $user->level_id != $userLevelId) {
                $user->level_id = $userLevelId;
                $user->save();
                $this->info("User {$user->id} level updated to {$userLevelId}");
            }
        }

        $this->info('User level check completed.');
    }
}
