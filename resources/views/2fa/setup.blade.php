@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
            Set Up Two-Factor Authentication
        </h2>
        <p class="mt-2 text-center text-sm text-gray-300">
            Secure your account with 2FA
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-[#1a2634] py-8 px-4 shadow-2xl sm:rounded-lg sm:px-10 border border-gray-700">
            <div class="mb-6">
                <p class="text-sm text-gray-300 mb-4">
                    Scan the QR code below with your authenticator app (like Google Authenticator or Authy).
                </p>
                
                <div class="flex justify-center mb-4 p-2 bg-white rounded-lg">
                    {!! $qrCode !!}
                </div>
                
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-300">Manual setup code:</p>
                    <div class="mt-1 bg-[#243142] p-3 rounded-md text-sm font-mono text-gray-200 break-all">
                        {{ $user->google2fa_secret }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('2fa.enable') }}">
                @csrf
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-300">
                        Enter OTP from your app
                    </label>
                    <div class="mt-1">
                        <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required 
                               class="appearance-none block w-full px-3 py-2 border border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-[#243142] text-white">
                    </div>
                    @error('otp')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Enable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection