<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ReferralSetting;
use App\Models\User;
use App\Models\UserDeposit;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Notification;

class UserReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $referralSettings = ReferralSetting::orderBy('level', 'asc')->get();

        $referredUsers = User::where('referrer', $user->referral_code)->paginate(15);

        $totalReferredUsers = $referredUsers->count();

        $totalApprovedDeposit = UserDeposit::whereIn('user_id', $referredUsers->pluck('id'))
            ->where('status', 'approved')
            ->sum('amount');

        $currentLevel = $referralSettings->filter(function ($setting) use ($totalReferredUsers, $totalApprovedDeposit) {
            return $totalReferredUsers >= $setting->register_user
                && $totalApprovedDeposit >= $setting->total_deposit;
        })->last();

        $nextLevel = $referralSettings->firstWhere('id', '>', optional($currentLevel)->id) ?? null;
        $depositNeededForNextLevel = $nextLevel ? max($nextLevel->total_deposit - $totalApprovedDeposit, 0) : null;
        $usersNeededForNextLevel = $nextLevel ? max($nextLevel->register_user - $totalReferredUsers, 0) : null;

        $referralUsersData = $referredUsers->map(function ($referredUser) {
            $totalDeposit = UserDeposit::where('user_id', $referredUser->id)
                ->where('status', 'approved')
                ->sum('amount');
            return [
                'user' => $referredUser,
                'total_deposit' => $totalDeposit,
            ];
        });

        return view('user.referral.index', compact(
            'referralSettings',
            'referralUsersData',
            'referredUsers',
            'totalReferredUsers',
            'totalApprovedDeposit',
            'currentLevel',
            'nextLevel',
            'depositNeededForNextLevel',
            'usersNeededForNextLevel'
        ));
    }

    public function myrefarral()
    {
        $user = Auth::user();

        $referralSettings = ReferralSetting::orderBy('level', 'asc')->get();

        $referredUsers = User::where('referrer', $user->referral_code)->paginate(15);

        $totalReferredUsers = $referredUsers->count();

        $totalApprovedDeposit = UserDeposit::whereIn('user_id', $referredUsers->pluck('id'))
            ->where('status', 'approved')
            ->sum('amount');

        $currentLevel = $referralSettings->filter(function ($setting) use ($totalReferredUsers, $totalApprovedDeposit) {
            return $totalReferredUsers >= $setting->register_user
                && $totalApprovedDeposit >= $setting->total_deposit;
        })->last();

        $nextLevel = $referralSettings->firstWhere('id', '>', optional($currentLevel)->id) ?? null;
        $depositNeededForNextLevel = $nextLevel ? max($nextLevel->total_deposit - $totalApprovedDeposit, 0) : null;
        $usersNeededForNextLevel = $nextLevel ? max($nextLevel->register_user - $totalReferredUsers, 0) : null;

        $referralUsersData = $referredUsers->map(function ($referredUser) {
            $totalDeposit = UserDeposit::where('user_id', $referredUser->id)
                ->where('status', 'approved')
                ->sum('amount');
            return [
                'user' => $referredUser,
                'total_deposit' => $totalDeposit,
            ];
        });

        return view('user.referral.my_refarral', compact(
            'referralSettings',
            'referralUsersData',
            'referredUsers',
            'totalReferredUsers',
            'totalApprovedDeposit',
            'currentLevel',
            'nextLevel',
            'depositNeededForNextLevel',
            'usersNeededForNextLevel'
        ));
    }

    public function collectBalance()
    {
        $user = Auth::user();

        $referralSettings = ReferralSetting::orderBy('level', 'asc')->get();

        $referredUsers = User::where('referrer', $user->referral_code)->get();

        $totalReferralDeposit = UserDeposit::whereIn('user_id', $referredUsers->pluck('id'))
            ->where('status', 'approved')
            ->sum('amount');

        $eligibleSetting = $referralSettings->filter(function ($setting) use ($referredUsers, $totalReferralDeposit) {
            return $referredUsers->count() >= $setting->register_user
                && $totalReferralDeposit >= $setting->total_deposit;
        })->last();

        if (!$eligibleSetting) {
            return response()->json(['message' => 'You have not met the requirements for referral commission yet.'], 400);
        }

        $commissionAmount = ($totalReferralDeposit * $eligibleSetting->commission) / 100;

        $user->balance += $commissionAmount;
        $user->save();

        $microtime = microtime(true);
        $transactionNumber = substr(str_replace('.', '', $microtime) . date('s'), 0, 10);

        Transaction::create([
            'transaction_number' => $transactionNumber,
            'user_id' => $user->id,
            'amount' => $commissionAmount,
            'transaction_type' => 'Referral',
            'status' => 'approved',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Referral Commission Collected',
            'message' => "You have earned a commission of {$commissionAmount} for level {$eligibleSetting->level}.",
        ]);

        return response()->json([
            'message' => 'Referral commission collected successfully!',
            'commission_amount' => $commissionAmount,
            'current_balance' => $user->balance,
        ]);
    }
}
