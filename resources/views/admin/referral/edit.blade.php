@extends('layouts.admin')

@section('title', 'Edit Referral Setting')

@section('content')
<div class="p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit Referral Setting</h2>

    <form action="{{ route('admin.referral.update', $referralSetting->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                <input type="text" name="level" id="level" value="{{ old('level', $referralSetting->level) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('level') border-red-500 @enderror" 
                    required>
                @error('level')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="register_user" class="block text-sm font-medium text-gray-700 mb-1">Registered Users Required</label>
                <input type="number" name="register_user" id="register_user" value="{{ old('register_user', $referralSetting->register_user) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('register_user') border-red-500 @enderror" 
                    required min="0">
                @error('register_user')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="total_deposit" class="block text-sm font-medium text-gray-700 mb-1">Total Deposit Required</label>
                <input type="number" step="0.01" name="total_deposit" id="total_deposit" value="{{ old('total_deposit', $referralSetting->total_deposit) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_deposit') border-red-500 @enderror" 
                    required min="0">
                @error('total_deposit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="commission" class="block text-sm font-medium text-gray-700 mb-1">Commission (%)</label>
                <input type="number" step="0.01" name="commission" id="commission" value="{{ old('commission', $referralSetting->commission) }}" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('commission') border-red-500 @enderror" 
                    required min="0">
                @error('commission')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.referral.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                Update Setting
            </button>
        </div>
    </form>
</div>
@endsection