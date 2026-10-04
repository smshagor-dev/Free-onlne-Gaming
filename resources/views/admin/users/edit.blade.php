@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Edit User: {{ $user->name }}</h1>

    <div class="bg-white shadow-lg rounded-xl p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div>
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name*</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email*</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('mobile_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                        <select name="country" id="country"
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                            <option value="{{ $country->name }}" {{ $user->country === $country->name ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('country')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" id="currency"
                            class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-700">
                            <option value="">Select Currency</option>
                            @foreach($countries as $country)
                            <option value="{{ $country->currency }}" {{ $user->currency === $country->currency ? 'selected' : '' }}>
                                {{ $country->currency }}
                            </option>
                            @endforeach
                        </select>
                        @error('currency')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <!-- Date of Birth -->
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('date_of_birth')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Profile & Security -->
            <div>
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Profile & Security</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Profile Photo -->
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700">Profile Photo</label>
                        <input type="file" name="photo" id="photo"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                        @if($user->photo)
                        <div class="mt-3 flex items-center space-x-3">
                            <img src="{{ asset('storage/' . $user->photo) }}" alt="Current photo" class="h-20 w-20 object-cover rounded-full border">
                            <span class="text-sm text-gray-500">Current photo</span>
                        </div>
                        @endif
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password</p>
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                   
                </div>
            </div>

            <!-- Account Balances & KYC -->
            <div>
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Balances</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Balance -->
                    <div>
                        <label for="balance" class="block text-sm font-medium text-gray-700">Balance</label>
                        <input type="number" step="0.01" name="balance" id="balance" value="{{ old('balance', $user->balance) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Available Balance -->
                    <div>
                        <label for="available_balance" class="block text-sm font-medium text-gray-700">Available Balance</label>
                        <input type="number" step="0.01" name="available_balance" id="available_balance" value="{{ old('available_balance', $user->available_balance) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Bonus Balance -->
                    <div>
                        <label for="bonus_balance" class="block text-sm font-medium text-gray-700">Bonus Balance</label>
                        <input type="number" step="0.01" name="bonus_balance" id="bonus_balance" value="{{ old('bonus_balance', $user->bonus_balance) }}"
                            class="mt-1 w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Account Settings -->
            <div>
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Account Settings</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Verified -->
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="is_verified" id="is_verified" value="1"
                            {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="is_verified" class="text-sm text-gray-700">Verified User</label>
                    </div>

                    <!-- KYC Verified -->
                    <div class="flex items-center space-x-2">
                        <input type="checkbox" name="kyc_verified" id="kyc_verified" value="1"
                            {{ old('kyc_verified', $user->kyc_verified) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="kyc_verified" class="text-sm text-gray-700">KYC Verified</label>
                    </div>

                     <!-- Google 2FA -->
                     <div class="flex items-center space-x-2">
                        <input type="checkbox" name="google2fa_status" id="google2fa_status" value="1"
                            {{ old('google2fa_status', $user->google2fa_status) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="google2fa_status" class="text-sm text-gray-700">Enable Google 2FA</label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection