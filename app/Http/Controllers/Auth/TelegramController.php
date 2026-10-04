<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function handleTelegramCallback()
    {
        $data = request()->all();

        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $seconds = date('s');
        $seed = date('ymdHi') . $milliseconds . $seconds;
        $referralCode = strtoupper(base_convert($seed, 10, 36));
        $referralCode = str_pad(substr($referralCode, 0, 12), 12, Str::upper(Str::random(1)));

        if (!$this->checkTelegramAuthorization($data)) {
            abort(403, 'Invalid Telegram data');
        }

        $user = User::updateOrCreate(
            ['email' => $data['id'].'@telegram.com'], // Telegram does not give real email
            [
                'name' => $data['first_name'] . ' ' . ($data['last_name'] ?? ''),
                'photo' => 'default.png',
                'registration_type' => 'Telegram',
                'is_verified' => 1,
                'last_login_at' => now(),
                'referral_code' => $referralCode,
            ]
        );

        Auth::login($user);

        return redirect('/');
    }

    private function checkTelegramAuthorization($data)
    {
        $checkHash = $data['hash'];
        unset($data['hash']);
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash('sha256', env('TELEGRAM_BOT_TOKEN'), true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return strcmp($hash, $checkHash) === 0;
    }
}
