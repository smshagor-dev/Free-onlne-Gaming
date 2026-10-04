<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $facebookUser = Socialite::driver('facebook')->stateless()->user();

        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $seconds = date('s');
        $seed = date('ymdHi') . $milliseconds . $seconds;
        $referralCode = strtoupper(base_convert($seed, 10, 36));
        $referralCode = str_pad(substr($referralCode, 0, 12), 12, Str::upper(Str::random(1)));

        $user = User::updateOrCreate(
            ['email' => $facebookUser->getEmail()],
            [
                'name' => $facebookUser->getName(),
                'email' => $facebookUser->getEmail(),
                'registration_type' => 'Facebook',
                'photo' => 'default.png',
                'is_verified' => 1,
                'last_login_at' => now(),
                'email_verified_at' => now(),
                'referral_code' => $referralCode,
            ]
        );

        Auth::login($user);

        return redirect('/'); // change to dashboard route
    }

}
