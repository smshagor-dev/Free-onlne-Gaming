@extends('layouts.admin')

@section('title', 'Edit Deposit Setting')

@section('content')
<div class="p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-semibold text-gray-700 mb-6">Edit Deposit Setting</h2>

    <form action="{{ route('admin.depositsettings.update', $depositSetting) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $depositSetting->title) }}" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bonus Type -->
            <div>
                <label for="bonus_type" class="block text-sm font-medium text-gray-700 mb-1">Bonus Type</label>
                <select name="bonus_type" id="bonus_type" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Bonus Type</option>
                    @foreach($bonusTypes as $type)
                        <option value="{{ $type }}" {{ old('bonus_type', $depositSetting->bonus_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('bonus_type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wager -->
            <div>
                <label for="wager" class="block text-sm font-medium text-gray-700 mb-1">Wager</label>
                <input type="number" step="0.01" name="wager" id="wager" value="{{ old('wager', $depositSetting->wager) }}" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('wager')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bonus Percentage -->
            <div>
                <label for="bonus_percentage" class="block text-sm font-medium text-gray-700 mb-1">Bonus Percentage</label>
                <input type="number" step="0.01" name="bonus_percentage" id="bonus_percentage" value="{{ old('bonus_percentage', $depositSetting->bonus_percentage) }}" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('bonus_percentage')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Minimum Bonus -->
            <div>
                <label for="minimum_bonus" class="block text-sm font-medium text-gray-700 mb-1">Minimum Deposit</label>
                <input type="number" step="0.01" name="minimum_bonus" id="minimum_bonus" value="{{ old('minimum_bonus', $depositSetting->minimum_bonus) }}" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('minimum_bonus')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bonus Time -->
            <div>
                <label for="bonus_time" class="block text-sm font-medium text-gray-700 mb-1">Bonus Time</label>
                <select name="bonus_time" id="bonus_time" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Bonus Time</option>
                    <option value="12 hour" {{ old('bonus_time', $depositSetting->bonus_time) == '12 hour' ? 'selected' : '' }}>12 Hours</option>
                    <option value="24 hour" {{ old('bonus_time', $depositSetting->bonus_time) == '24 hour' ? 'selected' : '' }}>24 Hours</option>
                    <option value="3 days" {{ old('bonus_time', $depositSetting->bonus_time) == '3 days' ? 'selected' : '' }}>3 Days</option>
                    <option value="7 days" {{ old('bonus_time', $depositSetting->bonus_time) == '7 days' ? 'selected' : '' }}>7 Days</option>
                    <option value="15 days" {{ old('bonus_time', $depositSetting->bonus_time) == '15 days' ? 'selected' : '' }}>15 Days</option>
                    <option value="1 month" {{ old('bonus_time', $depositSetting->bonus_time) == '1 month' ? 'selected' : '' }}>1 Month</option>
                </select>
                @error('bonus_time')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Maximum Claim in a Day -->
            <div>
                <label for="maximum_claim_in_a_day" class="block text-sm font-medium text-gray-700 mb-1">Maximum Claim in a Day</label>
                <input type="number" name="maximum_claim_in_a_day" id="maximum_claim_in_a_day" value="{{ old('maximum_claim_in_a_day', $depositSetting->maximum_claim_in_a_day) }}" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('maximum_claim_in_a_day')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Providers Section - Visibility controlled by JavaScript -->
            <div id="providers-section" class="hidden md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Providers</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mt-1 p-3 bg-gray-50 rounded-md">
                    @foreach($gatewayGroups as $name => $ids)
                        <div class="flex items-center">
                            <input type="checkbox" name="providers[]" value="{{ $ids[0] }}" id="provider_{{ $ids[0] }}" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                {{ is_array(old('providers', $depositSetting->providers)) && in_array($ids[0], old('providers', $depositSetting->providers)) ? 'checked' : '' }}>
                            <label for="provider_{{ $ids[0] }}" class="ml-2 block text-sm text-gray-700">{{ $name }}</label>
                        </div>
                    @endforeach
                </div>
                @error('providers')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Days Section - Visibility controlled by JavaScript -->
            <div id="days-section" class="hidden md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Days</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mt-1 p-3 bg-gray-50 rounded-md">
                    @foreach($days as $day)
                        <div class="flex items-center">
                            <input type="checkbox" name="days[]" value="{{ $day }}" id="day_{{ $day }}" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                {{ is_array(old('days', $depositSetting->days)) && in_array($day, old('days', $depositSetting->days)) ? 'checked' : '' }}>
                            <label for="day_{{ $day }}" class="ml-2 block text-sm text-gray-700">{{ $day }}</label>
                        </div>
                    @endforeach
                </div>
                @error('days')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Photo -->
            @if($depositSetting->photo)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Photo</label>
                <img src="{{ Storage::url($depositSetting->photo) }}" alt="Current photo" class="h-20 w-20 object-cover rounded-md">
            </div>
            @endif

            <!-- New Photo -->
            <div class="{{ $depositSetting->photo ? '' : 'md:col-span-2' }}">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">New Photo</label>
                <input type="file" name="photo" id="photo" 
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('photo')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <a href="{{ route('admin.depositsettings.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md mr-3">
                Cancel
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                Update Setting
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bonusTypeSelect = document.getElementById('bonus_type');
        const providersSection = document.getElementById('providers-section');
        const daysSection = document.getElementById('days-section');
        const minDepositLabel = document.querySelector('label[for="minimum_bonus"]');
        
        function toggleSections() {
            const bonusType = bonusTypeSelect.value;
            
            // Hide both sections first
            providersSection.classList.add('hidden');
            daysSection.classList.add('hidden');
            
            // Show appropriate section based on selection
            if (bonusType === 'Provider') {
                providersSection.classList.remove('hidden');
            } else if (bonusType === 'Day') {
                daysSection.classList.remove('hidden');
            }

            if (bonusType === 'Welcome Bonus') {
                minDepositLabel.textContent = 'Get Bonus';
            } else {
                minDepositLabel.textContent = 'Minimum Deposit';
            }
        }
        
        // Set initial state based on current bonus type
        toggleSections();
        
        // Add change event listener
        bonusTypeSelect.addEventListener('change', toggleSections);
    });
</script>
@endsection