@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0b141d] px-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-xl border border-white/20">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Forgot Password</h2>
        <p class="text-gray-300 text-center mb-6">
            Enter your <span class="font-semibold text-white">Email</span>, 
            <span class="font-semibold text-white">Username</span>, or 
            <span class="font-semibold text-white">User ID</span> to reset your password.
        </p>

        <form action="{{ route('auth.searchUser') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Email / Username / User ID</label>
                <input type="text" name="identifier"
                    class="w-full px-4 py-2 rounded-lg bg-white/5 border border-gray-600 text-gray-100 
                           placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter your email, username or ID" required>
                @error('identifier') 
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 
                       rounded-lg shadow-md transition duration-300 ease-in-out">
                Continue
            </button>
        </form>
    </div>
</div>
@endsection
