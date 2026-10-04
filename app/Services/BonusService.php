<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDeposit;
use App\Models\DepositSetting;
use App\Models\BonusUser;
use App\Models\Transaction;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class BonusService
{

    public function giveWelcomeBonus(User $user)
    {
        $setting = DepositSetting::where('bonus_type', 'Welcome Bonus')->first();
        if (!$setting) return;

        $bonusAmount = $setting->minimum_bonus;

        // Save to bonus_users
        BonusUser::create([
            'user_id'            => $user->id,
            'deposit_setting_id' => $setting->id,
            'bonus_type'         => 'Welcome Bonus',
            'user_deposit_id'    => null,
            'bonus_amount'       => $bonusAmount,
            'deposit_amount'     => 0,
        ]);

        // Update balance
        $user->increment('bonus_balance', $bonusAmount);

        // Save transaction
        $transaction = Transaction::create([
            'transaction_number' => $this->generateTransactionNumber(),
            'user_id'            => $user->id,
            'transaction_type'   => 'bonus',
            'amount'             => $bonusAmount,
            'status'             => 'approved',
            'comments'           => 'Welcome Bonus granted',
        ]);

        // Save notification
        $title = "Welcome Bonus!";
        $message = "Hello {$user->name},\n\n"
            . "You have received a Welcome Bonus of {$bonusAmount}.\n"
            . "Transaction #: {$transaction->transaction_number}\n\n"
            . "Enjoy your bonus and good luck!";

        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'is_read' => 0,
        ]);

        // Send email
        Mail::raw($message, function ($mail) use ($user, $title) {
            $mail->to($user->email)->subject($title);
        });
    }

    public function giveFirstDepositBonus(User $user, UserDeposit $deposit)
    {
        $setting = DepositSetting::where('bonus_type', 'First Deposit Bonus')->first();
        if (!$setting) return;

        // Only if user has no prior bonuses
        if (BonusUser::where('user_id', $user->id)
            ->where('bonus_type', 'First Deposit Bonus')
            ->exists()) {
            return;
        }

        if ($deposit->status !== 'approved') {
            return; // only apply after approval
        }

        if ($deposit->amount < $setting->minimum_bonus) {
            return; // not eligible
        }

        $bonusAmount = $setting->minimum_bonus * ($setting->bonus_percentage / 100);

        // Save to bonus_users
        BonusUser::create([
            'user_id'            => $user->id,
            'deposit_setting_id' => $setting->id,
            'bonus_type'         => 'First Deposit Bonus',
            'user_deposit_id'    => $deposit->id,
            'bonus_amount'       => $bonusAmount,
            'deposit_amount'     => $deposit->amount,
        ]);

        // Update balance
        $user->increment('bonus_balance', $bonusAmount);

        // Save transaction
        $transaction = Transaction::create([
            'transaction_number' => $this->generateTransactionNumber(),
            'user_id'            => $user->id,
            'transaction_type'   => 'bonus',
            'amount'             => $bonusAmount,
            'status'             => 'approved',
            'comments'           => 'First Deposit Bonus applied',
        ]);

        // Save notification
        $title = "First Deposit Bonus!";
        $message = "Hello {$user->name},\n\n"
            . "You have received a First Deposit Bonus of {$bonusAmount}.\n"
            . "Deposit Amount: {$deposit->amount}\n"
            . "Transaction #: {$transaction->transaction_number}\n\n"
            . "Enjoy your bonus and good luck!";

        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'is_read' => 0,
        ]);

        // Send email
        Mail::raw($message, function ($mail) use ($user, $title) {
            $mail->to($user->email)->subject($title);
        });
    }



    public function assignBonus(User $user, UserDeposit $deposit)
    {
        if ($user->bonus_balance > 0) {
            return;
        }

        $settings = DepositSetting::all();

        foreach ($settings as $setting) {
            $eligible = false;

            $todayClaimCount = BonusUser::where('user_id', $user->id)
                ->where('deposit_setting_id', $setting->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($setting->maximum_claim_in_a_day !== null && $todayClaimCount >= $setting->maximum_claim_in_a_day) {
                continue; 
            }

            switch ($setting->bonus_type) {
                case 'First Deposit Bonus':
                    if ($user->deposits()->count() == 1) {
                        $eligible = true;
                    }
                    break;

                case 'Provider':
                    if ($deposit->gateway && in_array($deposit->gateway->name, $setting->providers ?? [])) {
                        $eligible = true;
                    }
                    break;

                case 'Day':
                    $todayDay = now()->format('l'); 
                    if (in_array($todayDay, $setting->days ?? [])) {
                        $eligible = true;
                    }
                    break;

                case 'Birthday Bonus':
                    if ($user->date_of_birth && Carbon::parse($user->date_of_birth)->isBirthday()) {
                        $eligible = true;
                    }
                    break;

                case 'Welcome Bonus':
                    if ($user->created_at->isToday()) {
                        $eligible = true;
                    }
                    break;

                case 'Special Bonus':
                    $todayDay = now()->format('l');
                    if (
                        ($deposit->gateway && in_array($deposit->gateway->name, $setting->providers ?? []))
                        || (in_array($todayDay, $setting->days ?? []))
                    ) {
                        $eligible = true;
                    }
                    break;
            }

            if ($eligible && $deposit->amount >= $setting->minimum_bonus) {
                $bonusAmount = ($deposit->amount * ($setting->bonus_percentage / 100));

                // Save in bonus_users
                BonusUser::create([
                    'user_id'            => $user->id,
                    'deposit_setting_id' => $setting->id,
                    'user_deposit_id'    => $deposit->id,
                    'bonus_amount'       => $bonusAmount,
                    'deposit_amount'     => $deposit->amount,
                    'bonus_type'         => $setting->bonus_type,
                ]);

                // Update user bonus_balance
                $user->increment('bonus_balance', $bonusAmount);

                // Save transaction
                $transaction = Transaction::create([
                    'transaction_number' => $this->generateTransactionNumber(),
                    'user_id'            => $user->id,
                    'transaction_type'   => 'bonus',
                    'amount'             => $bonusAmount,
                    'status'             => 'approved',
                    'comments'           => 'Bonus applied from deposit ID ' . $deposit->id,
                ]);

                // Prepare notification
                $title   = "Bonus Received!";
                $message = "Hello {$user->name},\n\n"
                    . "You have received a bonus of {$bonusAmount}.\n\n"
                    . "Deposit Amount: {$deposit->amount}\n"
                    . "Bonus Type: {$setting->bonus_type}\n"
                    . "Transaction #: {$transaction->transaction_number}\n"
                    . "Status: Approved\n\n"
                    . "Enjoy your bonus and good luck!";

                // Save notification
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => $title,
                    'message' => $message,
                    'is_read' => 0,
                ]);

                // Send email
                Mail::raw($message, function ($mail) use ($user, $title) {
                    $mail->to($user->email)->subject($title);
                });

                break;
            }
        }
    }

    private function generateTransactionNumber()
    {

        $timePart = now()->format('His');
        $microPart = str_replace('.', '', (string) microtime(true));

        return substr($timePart . $microPart, 0, 10);
    }

    public function releaseBonus(User $user, BonusUser $bonusUser)
    {
        $setting = $bonusUser->depositSetting;

        $expiryTime = Carbon::parse($bonusUser->created_at)->add($this->parseBonusTime($setting->bonus_time));
        if (now()->greaterThan($expiryTime)) {
            $user->bonus_balance = 0.00;
            $user->save();

            $bonusUser->update(['status' => 'expired']);

            return false;
        }

        $requiredWager = $bonusUser->bonus_amount * $setting->wager;
        $userWagered   = $this->getUserTotalWager($user, $bonusUser->id);

        if ($userWagered >= $requiredWager) {
            $user->available_balance += $bonusUser->bonus_amount;
            $user->bonus_balance = 0.00;
            $user->save();

            $bonusUser->update(['status' => 'completed']);

            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'user_id'            => $user->id,
                'transaction_type'   => 'Bonus Release',
                'amount'             => $bonusUser->bonus_amount,
                'status'             => 'approved',
                'comments'           => 'Bonus released to main account',
            ]);

            // Notification
            $title   = "Bonus Released!";
            $message = "Hello {$user->name},\n\n"
                . "Your bonus of {$bonusUser->bonus_amount} has been released to your account.\n\n"
                . "Transaction #: {$transaction->transaction_number}\n"
                . "Status: Approved\n\n"
                . "Thank you for playing!";

            Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'is_read' => 0,
            ]);

            Mail::raw($message, function ($mail) use ($user, $title) {
                $mail->to($user->email)->subject($title);
            });

            return true;
        }

        return false;
    }

    /**
     * Helper: Parse bonus_time into Carbon interval
     */
    private function parseBonusTime($bonusTime)
    {
        switch ($bonusTime) {
            case '12 hour':
                return now()->addHours(12)->diff(now());
            case '24 hour':
                return now()->addDay()->diff(now());
            case '3 days':
                return now()->addDays(3)->diff(now());
            case '7 days':
                return now()->addDays(7)->diff(now());
            case '15 days':
                return now()->addDays(15)->diff(now());
            case '1 month':
                return now()->addMonth()->diff(now());
            default:
                return now()->addDays(7)->diff(now()); // fallback
        }
    }

    /**
     * Helper: Calculate user total wager amount
     * You must implement depending on your system (bets/transactions table).
     */
    private function getUserTotalWager(User $user, $bonusUserId)
    {
        // Example: If you track wagers in transactions with type "bet"
        return Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'bet')
            ->where('bonus_user_id', $bonusUserId)
            ->sum('amount');
    }
}
