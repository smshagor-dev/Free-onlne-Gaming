<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\CashbackSetting;
use App\Models\CashbackUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Mail\CashbackMail;
use Illuminate\Support\Facades\Mail;

class CashbackService
{
    /**
     * Try to calculate & credit cashback for a user.
     *
     * @param \App\Models\User $user
     * @return array ['status' => bool, 'message' => string, 'data' => array|null]
     */
    public function getCashback(User $user): array
    {
        // 1. load setting
        $setting = CashbackSetting::where('level_id', $user->level_id)->first();
        if (! $setting) {
            return ['status' => false, 'message' => 'No cashback setting for this level.'];
        }

        $now = Carbon::now();

        // 2. activation_days check (if present)
        if (!empty($setting->activation_days)) {
            $days = json_decode($setting->activation_days, true);
            if (!is_array($days)) {
                $days = array_map('trim', explode(',', $setting->activation_days));
            }
            $normalizedDays = array_map(fn($d) => ucfirst(strtolower($d)), $days);
            $today = $now->format('l'); // Monday, Tuesday, ...
            if (! in_array($today, $normalizedDays)) {
                return ['status' => false, 'message' => "Cashback cannot be claimed today ({$today})."];
            }
        }

        // 3. first-30-days eligibility check
        $createdAt = $user->created_at instanceof Carbon ? $user->created_at : Carbon::parse($user->created_at);
        $first30Start = $createdAt;
        $first30End   = $createdAt->copy()->addDays(180);

        $txCountFirst30 = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$first30Start, $first30End])
            ->count();

        if ($txCountFirst30 === 0) {
            return ['status' => false, 'message' => 'User had no transactions in the first 30 days after signup; not eligible.'];
        }

        // 4. loss window
        $loseDays = max(1, (int) $setting->lose_calculation);
        $periodStart = $now->copy()->subDays($loseDays);
        $periodEnd   = $now;

        // 5. check maximum_claim (pre-check)
        $claimsThisWindow = CashbackUser::where('user_id', $user->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->count();

        if ($setting->maximum_claim && $claimsThisWindow >= (int) $setting->maximum_claim) {
            return ['status' => false, 'message' => 'Maximum cashback claims reached for the current loss window.'];
        }

        // 6. sum user losses (support two data patterns)
        // Pattern A: trx column stores '-' and amount is positive
        $lossSum = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'lottary')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->where('trx', '-')
            ->sum('amount');

        // Pattern B fallback: amount is negative (sum absolute)
        if ($lossSum <= 0) {
            $lossSum = Transaction::where('user_id', $user->id)
                ->where('transaction_type', 'lottary')
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->where('amount', '<', 0)
                ->sum(DB::raw('ABS(amount)'));
        }

        $lossSum = (float) $lossSum;
        if ($lossSum <= 0.0) {
            return ['status' => false, 'message' => 'No qualifying losses during Your loss Account.'];
        }

        // 7. calculate cashback amount
        $percent = (float) $setting->cashback_percentage;
        $cashbackAmount = round($lossSum * ($percent / 100.0), 2);
        if ($cashbackAmount <= 0) {
            return ['status' => false, 'message' => 'Calculated cashback is zero.'];
        }

        // 8. commit within DB transaction and re-check maximum_claim (race safe)
        try {
            DB::transaction(function() use (&$user, $cashbackAmount, $percent, $lossSum, $setting, $periodStart, $periodEnd, $now) {
                // re-check and lock to prevent concurrent claims
                $claims = CashbackUser::where('user_id', $user->id)
                    ->whereBetween('created_at', [$periodStart, $periodEnd])
                    ->lockForUpdate()
                    ->count();

                if ($setting->maximum_claim && $claims >= (int) $setting->maximum_claim) {
                    throw new \Exception('Maximum claims reached during processing.');
                }

                // increment user cashback balance (make sure column exists)
                $user->increment('cashback', $cashbackAmount);

                // store cashback record
                $finishedTime = $now->copy()->addDays((int)$setting->playing_time); 
                CashbackUser::create([
                    'user_id' => $user->id,
                    'amount' => $cashbackAmount,
                    'finished_time' => $finishedTime,
                    'wager' => $setting->wager,
                    'playing_time' => $setting->playing_time,
                    'cashback_percentage' => $percent,
                    'source_loss' => $lossSum, // helpful for audits
                ]);

                // optional: keep audit trail by crediting a transaction
                Transaction::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'user_id' => $user->id,
                    'transaction_type' => 'cashback',
                    'amount' => $cashbackAmount,
                    'comments' => 'Cashback credited for losses between '.$periodStart->toDateTimeString().' and '.$periodEnd->toDateTimeString(),
                    'status' => 'approved'
                ]);
            });
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Failed to credit cashback: '.$e->getMessage()];
        }

        Notification::create([
            'user_id' => $user->id,
            'title'   => 'Cashback Credited',
            'message' => "You received {$cashbackAmount} as cashback for your recent losses.",
        ]);

        Mail::to($user->email)->send(new CashbackMail($user, $cashbackAmount));

        return [
            'status' => true,
            'message' => 'Cashback credited successfully.',
            'data' => [
                'cashback_amount' => $cashbackAmount,
                'loss_sum' => $lossSum,
                'cashback_percentage' => $percent,
                'period_start' => $periodStart->toDateTimeString(),
                'period_end' => $periodEnd->toDateTimeString(),
            ],
        ];
    }

    private function generateTransactionNumber()
    {

        $timePart = now()->format('His');
        $microPart = str_replace('.', '', (string) microtime(true));

        return substr($timePart . $microPart, 0, 10);
    }
}
