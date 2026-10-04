@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#0b141d] px-4">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md p-8 rounded-2xl shadow-xl border border-white/20">
        <h2 class="text-2xl font-bold text-white text-center mb-6">Reset Password</h2>

        <form action="{{ route('auth.resetPassword', $user->id) }}" method="POST" 
              class="space-y-5" 
              x-data="{ 
                  showPass: false, 
                  showConfirm: false, 
                  password: '', 
                  confirm: '', 
                  get strength() {
                      if (this.password.length < 6) return 'Weak';
                      if (/[A-Z]/.test(this.password) && /[0-9]/.test(this.password) && this.password.length >= 8) return 'Strong';
                      return 'Medium';
                  },
                  get match() {
                      return this.password && this.confirm 
                          ? this.password === this.confirm 
                          : null;
                  }
              }">

            @csrf

            <!-- New Password -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-200 mb-2">New Password</label>
                <input :type="showPass ? 'text' : 'password'" name="password" x-model="password"
                    class="w-full px-4 py-2 pr-10 rounded-lg bg-white/5 border border-gray-600 text-gray-100 
                           placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Enter new password" required>
                <!-- Toggle Eye -->
                <button type="button" @click="showPass = !showPass" 
                    class="absolute right-3 top-9 text-gray-400 hover:text-white focus:outline-none">
                    <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 
                                 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 
                                 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 
                                 0-8.268-2.943-9.542-7a9.956 9.956 0 
                                 012.107-3.592M9.88 9.88A3 3 0 
                                 0114.12 14.12M3 3l18 18" />
                    </svg>
                </button>
                <!-- Strength -->
                <p class="mt-2 text-sm" 
                   :class="strength === 'Weak' ? 'text-red-400' : (strength === 'Medium' ? 'text-yellow-400' : 'text-green-400')">
                   Password strength: <span x-text="strength"></span>
                </p>
                @error('password') 
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="relative">
                <label class="block text-sm font-medium text-gray-200 mb-2">Confirm Password</label>
                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" x-model="confirm"
                    class="w-full px-4 py-2 pr-10 rounded-lg bg-white/5 border border-gray-600 text-gray-100 
                           placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Confirm new password" required>
                <!-- Toggle Eye -->
                <button type="button" @click="showConfirm = !showConfirm" 
                    class="absolute right-3 top-9 text-gray-400 hover:text-white focus:outline-none">
                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 
                                 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 
                                 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirm" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" 
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 
                                 0-8.268-2.943-9.542-7a9.956 9.956 0 
                                 012.107-3.592M9.88 9.88A3 3 0 
                                 0114.12 14.12M3 3l18 18" />
                    </svg>
                </button>
                <!-- Match / Mismatch -->
                <p class="mt-2 text-sm" 
                   x-show="match !== null" 
                   :class="match ? 'text-green-400' : 'text-red-400'">
                   <span x-text="match ? 'Passwords match ✅' : 'Passwords do not match ❌'"></span>
                </p>
            </div>

            <!-- Submit -->
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 
                       rounded-lg shadow-md transition duration-300 ease-in-out">
                Reset Password
            </button>
        </form>
    </div>
</div>

<!-- Alpine.js -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
