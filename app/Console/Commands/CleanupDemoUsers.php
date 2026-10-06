<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CleanupDemoUsers extends Command
{
    protected $signature = 'demo:cleanup {--hours= : Retention period in hours} {--dry-run : Report candidates without deleting them}';

    protected $description = 'Permanently remove expired demo users and their isolated session/personalization data.';

    public function handle(): int
    {
        $hours = $this->option('hours');
        $retentionHours = max(1, $hours === null || $hours === ''
            ? (int) config('security.demo_retention_hours', 24)
            : (int) $hours);
        $cutoff = now()->subHours($retentionHours);

        $query = User::withTrashed()
            ->where('registration_type', 'demo')
            ->where('created_at', '<', $cutoff);

        $candidateCount = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$candidateCount} demo user(s) eligible for cleanup after {$retentionHours} hour(s).");
            return self::SUCCESS;
        }

        $deleted = 0;

        $query->select('id')->orderBy('id')->chunkById(100, function ($users) use (&$deleted): void {
            $ids = $users->pluck('id')->all();
            if ($ids === []) {
                return;
            }

            DB::transaction(function () use ($ids, &$deleted): void {
                // The sessions table intentionally has no user FK in Laravel's default schema.
                DB::table('sessions')->whereIn('user_id', $ids)->delete();

                User::withTrashed()
                    ->where('registration_type', 'demo')
                    ->whereIn('id', $ids)
                    ->get()
                    ->each(function (User $user) use (&$deleted): void {
                        $user->forceDelete();
                        $deleted++;
                    });
            });
        });

        Log::info('Expired demo users cleaned.', [
            'retention_hours' => $retentionHours,
            'candidate_count' => $candidateCount,
            'deleted_count' => $deleted,
        ]);

        $this->info("Deleted {$deleted} expired demo user(s).");
        return self::SUCCESS;
    }
}
