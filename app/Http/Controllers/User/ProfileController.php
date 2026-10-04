<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;


class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['favorites']);
    
        // Paginate separately
        $userLogins = $user->user_logins()->orderBy('created_at', 'desc')->paginate(10, ['*'], 'logins_page');
        $gameOpens = $user->game_opens()->orderBy('updated_at', 'desc')->paginate(10, ['*'], 'games_page');
    
        return view('user.profile.index', compact('user', 'userLogins', 'gameOpens'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('user.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'date_of_birth' => 'nullable|date',
        ]);

        $emailChanged = $request->email !== $user->email;

        $user->name = $request->name;
        $user->mobile_number = $request->mobile;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $user->photo = $path;
        }

        if ($emailChanged) {
            $user->email = $request->email;
            $user->is_verified = 0; // set as not verified
            $verificationCode = rand(100000, 999999);
            $user->verification_code = $verificationCode;
    
            // Send verification email
            Mail::raw("Your verification code is: {$user->verification_code}", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject("Please Verify Your Email");
            });
        }

        $user->save();

        if ($emailChanged) {
            return redirect()->route('verify.form')->with('success', 'A verification code has been sent to your new email. Please verify.');
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password changed successfully!');
    }
}
