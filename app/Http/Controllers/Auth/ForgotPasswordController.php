<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function searchUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255'
        ]);

        $user = User::where('user_id', $request->identifier)
                    ->orWhere('username', $request->identifier)
                    ->orWhere('email', $request->identifier)
                    ->first();

        if ($user && !empty($user->email)) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with('status', trans(Password::RESET_LINK_SENT));
    }

    /**
     * Show email update form
     */
    public function showUpdateEmailForm($id)
    {
        return redirect()->route('auth.forgotPasswordForm')
            ->with('status', trans(Password::RESET_LINK_SENT));
    }

    /**
     * Handle email update and send code
     */
    public function updateEmail(Request $request, $id)
    {
        return redirect()->route('auth.forgotPasswordForm')
            ->with('status', trans(Password::RESET_LINK_SENT));
    }

    /**
     * Show verify form (with code input)
     */
    public function showVerifyForm($id)
    {
        return redirect()->route('auth.forgotPasswordForm')
            ->with('status', trans(Password::RESET_LINK_SENT));
    }

    /**
     * Verify code and allow password reset
     */
    public function verifyCode(Request $request, $id)
    {
        return redirect()->route('auth.forgotPasswordForm')
            ->with('status', trans(Password::RESET_LINK_SENT));
    }

    /**
     * Show password reset form
     */
    public function showResetPasswordForm($id)
    {
        return redirect()->route('password.request');
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request, $id)
    {
        return redirect()->route('password.request');
    }
}
