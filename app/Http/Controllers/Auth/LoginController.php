<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\UserLogin;
use Jenssegers\Agent\Agent;
use App\Models\User;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $this->ensureIsNotRateLimited($request);

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->has('remember');
        $throttleKey = $this->throttleKey($request);

        if (
            Auth::attempt(['email' => $login, 'password' => $password], $remember) ||
            Auth::attempt(['username' => $login, 'password' => $password], $remember) ||
            Auth::attempt(['user_id' => $login, 'password' => $password], $remember)
        ) {
            RateLimiter::clear($throttleKey);

            if (!Auth::user()->is_verified) {
                Auth::logout();
                return back()->with('error', 'Your account is not verified. Please check your email.');
            }

            // 🔹 Detect IP and device info
            $ip = $request->ip();

            // Optional: Get location from ip-api.com (free, no API key needed)
            $location = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}"), true);

            $agent = new Agent();
            $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
            $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
            $deviceName = $agent->device() ?: ($agent->platform() . ' ' . $agent->version($agent->platform()));

            // 🔹 Store login info
            UserLogin::create([
                'user_id'    => Auth::id(),
                'ip_address' => $ip,
                'country'    => $location['country'] ?? null,
                'region'     => $location['regionName'] ?? null,
                'city'       => $location['city'] ?? null,
                'browser'    => $browser,
                'device_type' => $deviceType,
                'device_name' => $deviceName,

            ]);

            // 🔹 Update last login time
            $user = Auth::user();

            if (is_null($user->last_login_at)) {
                $user->update(['last_login_at' => now()]);
                return redirect()->route('bonus.page');
            }

            $user->last_login_at = now();
            $user->save();

            $request->session()->regenerate();
            if ($request->filled('redirect_to') && $this->isSafeRedirect($request->input('redirect_to'))) {
                return redirect()->to($request->input('redirect_to'));
            }

            return redirect()->intended('/');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->with('error', 'Invalid credentials.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    protected function authenticated(Request $request, $user)
    {
        if (is_null($user->last_login_at)) {
            $user->update(['last_login_at' => now()]);
            return redirect()->route('bonus.page');
        }

        $user->update(['last_login_at' => now()]);


        if ($request->filled('redirect_to')) {
            if ($this->isSafeRedirect($request->input('redirect_to'))) {
                return redirect($request->input('redirect_to'));
            }
        }

        return redirect()->intended($this->redirectPath());
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('login')).'|'.$request->ip());
    }

    private function isSafeRedirect(string $target): bool
    {
        if ($target === '' || str_starts_with($target, '//')) {
            return false;
        }

        $parts = parse_url($target);

        if ($parts === false) {
            return false;
        }

        if (!isset($parts['host'])) {
            return str_starts_with($target, '/');
        }

        return $parts['host'] === parse_url(config('app.url'), PHP_URL_HOST);
    }

    protected function guard()
    {
        return Auth::guard('web');
    }
}
