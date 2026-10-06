<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CleanupDemoUsers extends Command
{
    protected $signature = 'demo:cleanup {--days= : Override DEMO_RETENTION_DAYS for this run} {--dry-run : Count records without deleting them}';

    protected $description = 'Delete expired demo users and their dependent data.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('demo.retention_days', 7));
        $days = max(1, $days);
        $cutoff = now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');
        $deleted = 0;

        User::withTrashed()
            ->where('registration_type', 'demo')
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$deleted, $dryRun): void {
                $ids = $users->pluck('id')->all();

                if ($ids === []) {
                    return;
                }

                if (! $dryRun) {
                    DB::table('sessions')->whereIn('user_id', $ids)->delete();
                    User::withTrashed()->whereKey($ids)->forceDelete();
                }

                $deleted += count($ids);
            });

        $this->info(($dryRun ? 'Matched' : 'Deleted').' '.$deleted.' expired demo user(s).');

        return self::SUCCESS;
    }
}
