<?php

use Carbon\Carbon;
use App\Models\BonusUser;
use App\Models\DepositSetting;

if (!function_exists('CheckBonus')) {
    /**
     * Check active bonus for a user
     *
     * @param int $userId
     * @return array|null
     */
    function CheckBonus($userId)
{
    $bonusUsers = BonusUser::where('user_id', $userId)
        ->latest()
        ->get();

    foreach ($bonusUsers as $bonusUser) {
        $depositSetting = DepositSetting::find($bonusUser->deposit_setting_id);

        if (!$depositSetting) {
            continue;
        }

        preg_match('/\d+/', $depositSetting->bonus_time, $matches);
        $hours = $matches ? (int)$matches[0] : 0;

        if ($hours <= 0) {
            continue; 
        }

        $createdAt = Carbon::parse($bonusUser->created_at);
        $expiresAt = $createdAt->copy()->addHours($hours);

        if (Carbon::now()->lessThanOrEqualTo($expiresAt)) {
            return [
                'title' => $depositSetting->title,
                'bonus_type' => $depositSetting->bonus_type,
                'providers' => $depositSetting->providers,
                'days' => $depositSetting->days,
                'wager' => $depositSetting->wager,
                'bonus_percentage' => $depositSetting->bonus_percentage,
                'minimum_bonus' => $depositSetting->minimum_bonus,
                'bonus_time' => $depositSetting->bonus_time,
                'maximum_claim_in_a_day' => $depositSetting->maximum_claim_in_a_day,
                'photo' => $depositSetting->photo,
                'expires_at' => $expiresAt->toDateTimeString(),
            ];
        }
    }

    return null; 
}


}
