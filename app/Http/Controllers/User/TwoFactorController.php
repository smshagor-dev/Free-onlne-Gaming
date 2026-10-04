<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class TwoFactorController extends Controller
{
    public function setup()
    {
        $google2fa = new Google2FA();
        $user = auth::user();

        $appName = Setting::first()->name ?? 'MyApp';

        // Generate secret if not already set
        if (!$user->google2fa_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->google2fa_secret = $secret;
            $user->save();
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $appName,
            $user->email,
            $user->google2fa_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($qrCodeUrl);

        return view('2fa.setup', compact('qrCode', 'user'));
    }

    public function enable(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $google2fa = new Google2FA();
        $user = auth::user();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

        if ($valid) {
            $user->google2fa_status = true;
            $user->save();

            return redirect()->route('games.viewIndex')->with('success', '2FA Enabled!');
        }

        return back()->withErrors(['otp' => 'Invalid OTP, try again.']);
    }

    public function disable()
    {
        $user = auth::user();
        $user->google2fa_secret = null;
        $user->google2fa_status = false;
        $user->save();

        return redirect()->route('games.viewIndex')->with('success', '2FA Disabled!');
    }

    public function verifyForm()
    {
        return view('2fa.verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric']);

        $google2fa = new Google2FA();
        $user = auth::user();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

        if ($valid) {
            session(['2fa_verified' => true]);
            return redirect('/')->with('success', '2FA Verified!');
        }

        return back()->withErrors(['otp' => 'Invalid OTP']);
    }

    public function toggle2fa(Request $request)
    {
        $request->validate([
            'otp' => 'nullable|numeric', 
            'action' => 'required|in:enable,disable'
        ]);

        $user = auth::user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if ($request->action === 'enable') {
            // verify the OTP
            if (!$request->otp) {
                return back()->withErrors(['otp' => 'OTP is required to enable 2FA']);
            }

            $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp);

            if ($valid) {
                $user->google2fa_status = true;
                $user->save();

                return redirect()->route('games.viewIndex')->with('success', '2FA has been enabled!');
            }

            return back()->withErrors(['otp' => 'Invalid OTP, please try again.']);
        }

        if ($request->action === 'disable') {
            $user->google2fa_secret = null;
            $user->google2fa_status = false;
            $user->save();

            return redirect()->route('games.viewIndex')->with('success', '2FA has been disabled!');
        }
    }
}
