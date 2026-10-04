@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0b141d] px-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-xl border border-white/20">
        <h2 class="text-2xl font-bold text-white text-center mb-4">Verify Your Email</h2>
        <p class="text-gray-300 text-center mb-6">
            We’ve sent a <span class="font-semibold text-white">6-digit code</span> to 
            <b class="text-blue-400">{{ $user->email }}</b>. Enter it below:
        </p>

        <form action="{{ route('auth.verifyCode', $user->id) }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Verification Code</label>
                <input type="text" name="code" 
                    class="w-full px-4 py-2 rounded-lg bg-white/5 border border-gray-600 text-gray-100 
                           placeholder-gray-400 tracking-widest text-center text-lg font-semibold 
                           focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                    placeholder="••••••" required>
                @error('code') 
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <button type="submit" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 
                       rounded-lg shadow-md transition duration-300 ease-in-out">
                Verify
            </button>
        </form>
    </div>
</div>
@endsection
