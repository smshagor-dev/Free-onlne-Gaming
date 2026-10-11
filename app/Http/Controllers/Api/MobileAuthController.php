<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class MobileAuthController extends Controller
{
    public function register(Request $r)
    {
        $r->merge(['email' => Str::lower((string) $r->input('email'))]);
        $d = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:255|unique:users,email', 'username' => 'required|alpha_dash|max:50|unique:users,username', 'password' => 'required|string|min:8|max:128|confirmed']);
        $u = User::create(['name' => $d['name'], 'email' => Str::lower($d['email']), 'username' => $d['username'], 'password' => Hash::make($d['password']), 'registration_type' => 'Email', 'is_verified' => false]);
        $this->sendCode($u);

        return response()->json(['message' => 'Account created. Check your email for a verification code.'], 201);
    }

    private function codeKey(string $email): string
    {
        return 'mobile.verify.'.hash('sha256', Str::lower($email));
    }

    private function sendCode(User $u): void
    {
        $code = (string) random_int(100000, 999999);
        Mail::raw('Your FreeGames verification code is '.$code.'. It expires in 10 minutes.', fn ($m) => $m->to($u->email)->subject('Verify your FreeGames account'));
        Cache::put($this->codeKey($u->email), hash('sha256', $code), now()->addMinutes(10));
    }

    public function resend(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255']);
        $u = User::where('email', Str::lower($d['email']))->first();
        if ($u && ! $u->is_verified && ! $u->is_banned) {
            $this->sendCode($u);
        }

        return response()->json(['message' => 'If verification is needed, a code has been sent.']);
    }

    public function verify(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255', 'code' => 'required|digits:6']);
        $key = $this->codeKey($d['email']);
        $hash = Cache::get($key);
        $u = User::where('email', Str::lower($d['email']))->first();
        if (! $hash || ! hash_equals($hash, hash('sha256', $d['code'])) || ! $u || $u->is_banned) {
            throw ValidationException::withMessages(['code' => 'Invalid or expired verification code.']);
        }
        $u->update(['is_verified' => true, 'email_verified_at' => now(), 'verification_code' => null]);
        Cache::forget($key);

        return response()->json(['message' => 'Email verified. You can now sign in.']);
    }

    public function login(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255', 'password' => 'required|string|max:128', 'device_name' => 'required|string|max:80', 'otp' => 'nullable|digits:6']);
        $u = User::where('email', Str::lower($d['email']))->first();
        if (! $u || ! Hash::check($d['password'], $u->password)) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }
        abort_if($u->is_banned, 403, 'Account is restricted.');
        abort_unless($u->is_verified, 403, 'Email verification required.');
        if ($u->google2fa_status) {
            if (empty($d['otp']) || ! $u->google2fa_secret || ! (new Google2FA)->verifyKey($u->google2fa_secret, $d['otp'], 1)) {
                throw ValidationException::withMessages(['otp' => 'A valid authenticator code is required.']);
            }
        }
        if ($u->google2fa_status && ! Cache::add('mobile.otp.'.hash('sha256', $u->id.'|'.$d['otp']), true, now()->addMinutes(2))) {
            throw ValidationException::withMessages(['otp' => 'This authenticator code has already been used.']);
        }

        return $this->issue($u, $d['device_name']);
    }

    private function issue(User $u, string $device)
    {
        $raw = bin2hex(random_bytes(32));
        $expiry = now()->addDays(30);
        MobileApiToken::create(['user_id' => $u->id, 'device_name' => $device, 'token_hash' => hash('sha256', $raw), 'auth_fingerprint' => MobileApiToken::fingerprint($u), 'expires_at' => $expiry]);

        return response()->json(['data' => ['access_token' => $raw, 'token_type' => 'Bearer', 'expires_at' => $expiry->toIso8601String(), 'user' => $this->profile($u)]]);
    }

    public function profile(User $u): array
    {
        return $u->only(['id', 'name', 'username', 'email', 'photo', 'country', 'mobile_number', 'is_verified', 'google2fa_status']);
    }

    public function me(Request $r)
    {
        return response()->json(['data' => $this->profile($r->user())]);
    }

    public function update(Request $r)
    {
        $d = $r->validate(['name' => 'sometimes|required|string|max:100', 'country' => 'sometimes|nullable|string|max:100', 'mobile_number' => 'sometimes|nullable|string|max:20']);
        $r->user()->update($d);

        return $this->me($r);
    }

    public function logout(Request $r)
    {
        $r->attributes->get('mobile_token')->delete();

        return response()->json(['message' => 'Signed out.']);
    }

    public function logoutAll(Request $r)
    {
        MobileApiToken::where('user_id', $r->user()->id)->delete();

        return response()->json(['message' => 'All mobile sessions revoked.']);
    }

    public function devices(Request $r)
    {
        return response()->json(['data' => MobileApiToken::where('user_id', $r->user()->id)->where('expires_at', '>', now())->get(['id', 'device_name', 'expires_at', 'last_used_at'])->map(fn ($device) => [...$device->toArray(), 'current' => $device->id === $r->attributes->get('mobile_token')->id])]);
    }

    public function revoke(Request $r, string $id)
    {
        MobileApiToken::where('user_id', $r->user()->id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Device revoked.']);
    }

    public function refresh(Request $r)
    {
        return DB::transaction(function () use ($r) {
            $old = MobileApiToken::whereKey($r->attributes->get('mobile_token')->id)->lockForUpdate()->firstOrFail();
            $old->delete();

            return $this->issue($r->user(), $old->device_name);
        });
    }

    public function password(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|string|max:128', 'password' => 'required|string|min:8|max:128|confirmed']);
        if (! Hash::check($d['current_password'], $r->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Incorrect password.']);
        }
        DB::transaction(function () use ($r, $d) {
            $r->user()->update(['password' => Hash::make($d['password']), 'remember_token' => Str::random(60)]);
            MobileApiToken::where('user_id', $r->user()->id)->delete();
        });

        return response()->json(['message' => 'Password changed. Sign in again on all mobile devices.']);
    }

    public function forgot(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255']);
        Password::sendResetLink(['email' => Str::lower($d['email'])]);

        return response()->json(['message' => 'If the account exists, a password reset link has been sent.']);
    }

    public function reset(Request $r)
    {
        $d = $r->validate(['email' => 'required|email|max:255', 'token' => 'required|string|max:255', 'password' => 'required|string|min:8|max:128|confirmed']);
        $status = Password::reset($d, function (User $u, string $password) {
            DB::transaction(function () use ($u, $password) {
                $u->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                MobileApiToken::where('user_id', $u->id)->delete();
            });
            event(new \Illuminate\Auth\Events\PasswordReset($u));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['token' => 'Invalid or expired reset token.']);
        }

        return response()->json(['message' => 'Password reset. Sign in again.']);
    }

    public function setupTwoFactor(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|string|max:128']);
        if (! Hash::check($d['current_password'], $r->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Incorrect password.']);
        }
        abort_if($r->user()->google2fa_status, 409, 'Two-factor authentication is already enabled.');
        $g = new Google2FA;
        $secret = $g->generateSecretKey();
        Cache::put('mobile.2fa.'.$r->attributes->get('mobile_token')->id, $secret, now()->addMinutes(10));

        return response()->json(['data' => ['secret' => $secret, 'otpauth_url' => $g->getQRCodeUrl(config('app.name'), $r->user()->email, $secret), 'expires_in' => 600]]);
    }

    public function enableTwoFactor(Request $r)
    {
        $d = $r->validate(['otp' => 'required|digits:6']);
        $key = 'mobile.2fa.'.$r->attributes->get('mobile_token')->id;
        $secret = Cache::get($key);
        if (! $secret || ! (new Google2FA)->verifyKey($secret, $d['otp'], 1)) {
            throw ValidationException::withMessages(['otp' => 'Invalid or expired setup code.']);
        }
        DB::transaction(function () use ($r, $secret) {
            $r->user()->update(['google2fa_secret' => $secret, 'google2fa_status' => true]);
            MobileApiToken::where('user_id', $r->user()->id)->delete();
        });
        Cache::forget($key);

        return response()->json(['message' => 'Two-factor authentication enabled. Sign in again.']);
    }

    public function disableTwoFactor(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|string|max:128', 'otp' => 'required|digits:6']);
        $u = $r->user();
        if (! Hash::check($d['current_password'], $u->password) || ! $u->google2fa_status || ! $u->google2fa_secret || ! (new Google2FA)->verifyKey($u->google2fa_secret, $d['otp'], 1)) {
            throw ValidationException::withMessages(['otp' => 'Password or authenticator code is incorrect.']);
        }
        DB::transaction(function () use ($u) {
            $u->update(['google2fa_status' => false, 'google2fa_secret' => null]);
            MobileApiToken::where('user_id', $u->id)->delete();
        });

        return response()->json(['message' => 'Two-factor authentication disabled. Sign in again.']);
    }

    public function deleteAccount(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|string|max:128', 'otp' => 'nullable|digits:6']);
        $u = $r->user();
        if (! Hash::check($d['current_password'], $u->password)) {
            throw ValidationException::withMessages(['current_password' => 'Incorrect password.']);
        }
        if ($u->google2fa_status && (empty($d['otp']) || ! (new Google2FA)->verifyKey($u->google2fa_secret, $d['otp'], 1))) {
            throw ValidationException::withMessages(['otp' => 'A valid authenticator code is required.']);
        }
        DB::transaction(function () use ($u) {
            MobileApiToken::where('user_id', $u->id)->delete();
            \App\Models\MobileGameState::where('user_id', $u->id)->delete();
            $u->delete();
        });

        return response()->json(['message' => 'Account closed. Historical records are retained.']);
    }
}
