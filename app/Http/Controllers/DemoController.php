<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DemoController extends Controller
{
    public function login(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('catalog.index');
        }

        $token = (string) Str::uuid();
        $user = User::query()->create([
            'name' => 'Demo Player',
            'email' => 'demo-'.$token.'@example.invalid',
            'password' => Hash::make(Str::random(64)),
            'registration_type' => 'demo',
            'email_verified_at' => now(),
            'is_verified' => true,
            'balance' => 0,
            'available_balance' => 0,
            'bonus_balance' => 0,
            'cashback' => 0,
        ]);

        Auth::login($user);
        request()->session()->regenerate();
        request()->session()->put('demo_mode', true);
        return redirect()->route('catalog.index')->with('success', 'Demo mode started. Explore safely without financial actions.');
    }
}
