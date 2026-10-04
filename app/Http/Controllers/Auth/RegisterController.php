<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/verify';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:50', 'unique:users'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'country' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'referrer' => ['nullable', 'string', 'max:50'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $verificationCode = rand(100000, 999999);

        $photoPath = null;
        if (isset($data['photo'])) {
            $photoPath = $data['photo']->store('photos', 'public');
        }

        $ip = request()->ip();
        $location = Http::timeout(2)->get("http://ip-api.com/json/{$ip}")->json();

        $agent = new Agent();
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $deviceName = $agent->device() ?: ($agent->platform() . ' ' . $agent->version($agent->platform()));


        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $seconds = date('s');
        $seed = date('ymdHi') . $milliseconds . $seconds;
        $referralCode = strtoupper(base_convert($seed, 10, 36));
        $referralCode = str_pad(substr($referralCode, 0, 12), 12, Str::upper(Str::random(1)));


        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'registration_type' => 'Email',
            'mobile_number' => $data['mobile_number'] ?? null,
            'verification_code' => $verificationCode,
            'is_verified' => false,
            'photo' => $photoPath,
            'username' => $data['username'],
            'user_location' => $data['user_location'] ?? null,
            'remember_token' => null,
            'remember' => $data['remember'] ?? null,
            'country' => $data['country'] ?? null,
            'currency' => $data['currency'] ?? null,

            'user_ip' => $ip,
            'user_browser' => $browser,
            'device_type' => $deviceType,
            'device_name' => $deviceName,
            'user_country' => $location['country'] ?? null,
            'user_region' => $location['regionName'] ?? null,
            'user_city' => $location['city'] ?? null,
            'referral_code' => $referralCode,
            'referrer' => $data['referrer'] ?? null,
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        // Send verification email
        Mail::raw("Your verification code is: {$user->verification_code}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject("Please Verify Your Account");
        });

        // Redirect to verification page with success message
        return redirect()->route('verify.form')
            ->with('success', 'Registration complete! Please check your email for the verification code.')
            ->with('email', $user->email);
    }

    public function getCountries()
    {
        $countries = Country::where('status', 1)
            ->get(['name', 'currency', 'dial']);
        return response()->json($countries, 200);
    }


    public function quickRegistration(Request $request)
    {
        $microtime = microtime(true);
        $micro = substr(str_replace('.', '', $microtime), 0, 10);
        $username = 'User' . $micro;

        $password = Str::random(8);

        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $seconds = date('s');
        $seed = date('ymdHi') . $milliseconds . $seconds;
        $referralCode = strtoupper(base_convert($seed, 10, 36));
        $referralCode = str_pad(substr($referralCode, 0, 12), 12, Str::upper(Str::random(1)));

        // Validate input
        $data = $request->validate([
            'country' => 'nullable|string',
            'currency' => 'nullable|string',
            'referrer' => 'nullable|string|max:50',
        ]);

        // Get IP & location
        $ip = $request->ip();
        $location = Http::timeout(2)->get("http://ip-api.com/json/{$ip}")->json();
        $agent = new \Jenssegers\Agent\Agent();
        $browser = $agent->browser() . ' ' . $agent->version($agent->browser());
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $deviceName = $agent->device() ?: ($agent->platform() . ' ' . $agent->version($agent->platform()));

        // Create user
        $user = User::create([
            'username' => $username,
            'password' => $password,
            'email'        => $username . '@mail.com',
            'registration_type' => 'Quick',
            'country' => $data['country'] ?? ($location['country'] ?? null),
            'currency' => $data['currency'] ?? null,
            'is_verified' => 1,
            'referral_code' => $referralCode,
            'photo' => 'default.png',
            'user_ip' => $ip,
            'user_browser' => $browser,
            'device_type' => $deviceType,
            'device_name' => $deviceName,
            'user_country' => $location['country'] ?? null,
            'user_region' => $location['regionName'] ?? null,
            'user_city' => $location['city'] ?? null,
            'referrer' => $data['referrer'] ?? null,
        ]);

        return view('auth.register_success', [
            'username' => $username,
            'password' => $password
        ]);
    }
}
