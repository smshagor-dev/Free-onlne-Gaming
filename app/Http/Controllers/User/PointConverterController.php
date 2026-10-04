<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\GamePoint;
use App\Models\VipBonus;
use App\Models\Notification;
use App\Mail\pointConvertMail;
use Illuminate\Support\Facades\Mail;

class PointConverterController extends Controller
{
    public function showConverter()
    {
        $user = auth::user();

        $gamePoint = GamePoint::first();

        $minPoints = $gamePoint->points_amount ?? 1000;
        $getBalance = $gamePoint->get_balance ?? 10;

        $setting = DB::table('settings')->first();
        $currency = $setting->site_currency ?? 'Bonus';

        $transactions = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'Point Convert')
            ->orderBy('created_at', 'desc')
            ->paginate(10);



        return view('user.point_converter', [
            'user' => $user,
            'minPoints' => $minPoints,
            'getBalance' => $getBalance,
            'currency' => $currency,
            'transactions' => $transactions
        ]);
    }

    public function convertPoints(Request $request)
    {
        $user = auth::user();

        // Validate user input
        $request->validate([
            'point_amount' => 'required|integer|min:1',
        ]);

        $convertPoints = $request->point_amount;

        if ($user->level_id < 1) {
            return response()->json(['error' => 'You must be at least level 1 to convert points.'], 403);
        }

        if ($convertPoints > $user->available_points) {
            return response()->json(['error' => 'You do not have enough available points.'], 400);
        }

        $gamePoint = GamePoint::first();
        $minPoints = $gamePoint->points_amount ?? 1000;
        $getBalance = $gamePoint->get_balance ?? 10;

        if ($convertPoints < $minPoints) {
            return response()->json(['error' => "You must convert at least {$minPoints} points."], 400);
        }

        $convertedAmount = ($convertPoints / $minPoints) * $getBalance;
        $pointsToDeduct = $convertPoints;

        DB::transaction(function () use ($user, $pointsToDeduct, $convertedAmount) {

            $transactionNumber = substr(str_replace('.', '', microtime(true)), -10);

            Transaction::create([
                'transaction_number' => $transactionNumber,
                'user_id'            => $user->id,
                'point_amount'       => $pointsToDeduct,
                'amount'             => $convertedAmount,
                'transaction_type'   => 'Point Convert',
                'status'             => 'approved'
            ]);

            // Update user
            $user->available_points -= $pointsToDeduct;
            $user->vip_bonus += $convertedAmount;
            $user->save();

            // Insert into vip_bonuses
            VipBonus::create([
                'user_id' => $user->id,
                'bonus_amount' => $convertedAmount,
                'playing_time' => 24,
                'wager' => 4
            ]);

            // Store notification
            $title = 'Points Converted to Bonus';
            $message = "You have successfully converted {$pointsToDeduct} points into {$convertedAmount} bonus. Transaction ID: {$transactionNumber}.";

            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message
            ]);

            // Send email (make sure you have VipBonusMail Mailable)
            Mail::to($user->email)->send(new pointConvertMail($title, $message, $user));
        });

        return response()->json([
            'success' => true,
            'message' => 'Points converted successfully!',
            'converted_amount' => $convertedAmount
        ]);
    }
}
