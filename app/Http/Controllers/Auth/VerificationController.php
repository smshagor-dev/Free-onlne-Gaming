<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\UserVerificationMail;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
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
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function showVerifyForm()
    {
        return view('auth.verify');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric'
        ]);

        $user = Auth::user();

        if (!$user || !hash_equals((string) $user->verification_code, (string) $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        Auth::login($user);

        return redirect('/home')->with('success', 'Your account has been verified!');
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = Auth::user();

        if (!$user || !hash_equals($user->email, (string) $request->input('email'))) {
            abort(403);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is already verified.'
            ]);
        }

        // Generate new verification code
        $verificationCode = rand(100000, 999999);
        $user->verification_code = $verificationCode;
        $user->save();

        // Send email
        Mail::raw("Your new verification code is: $verificationCode", function ($message) use ($user) {
            $message->to($user->email)
                ->subject("Resend Verification Code");
        });

        return response()->json([
            'success' => true,
            'message' => 'A new verification code has been sent to your email.'
        ]);
    }
}
