@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0b141d] px-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-xl border border-white/20">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Update Email</h2>

        <form action="{{ route('auth.updateEmail', $user->id) }}" method="POST" class="space-y-5">
            @csrf

            <!-- User ID -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">User ID</label>
                <input type="text" name="user_id" value="{{ $user->user_id }}" readonly
                    class="w-full px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-600 text-gray-400 
                           cursor-not-allowed placeholder-gray-500">
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Username</label>
                <input type="text" name="username" value="{{ $user->username }}" readonly
                    class="w-full px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-600 text-gray-400 
                           cursor-not-allowed placeholder-gray-500">
            </div>

            <!-- New Email -->
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Enter Email Address</label>
                <input type="email" name="email" 
                    class="w-full px-4 py-2 rounded-lg bg-white/5 border border-gray-600 text-gray-100 
                           placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter your email address" required>
                @error('email') 
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Submit -->
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 
                       rounded-lg shadow-md transition duration-300 ease-in-out">
                Update & Send Code
            </button>
        </form>
    </div>
</div>
@endsection
