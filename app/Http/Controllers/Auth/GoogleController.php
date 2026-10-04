<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $seconds = date('s');
        $seed = date('ymdHi') . $milliseconds . $seconds;
        $referralCode = strtoupper(base_convert($seed, 10, 36));
        $referralCode = str_pad(substr($referralCode, 0, 12), 12, Str::upper(Str::random(1)));

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'registration_type' => 'Google',
                'photo' => 'default.png',
                'is_verified' => 1,
                'last_login_at' => now(),
                'email_verified_at' => now(),
                'referral_code' => $referralCode,
            ]
        );

        Auth::login($user);

        return redirect('/'); 
    }
}
