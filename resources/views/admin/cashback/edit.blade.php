@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">{{ isset($cashback) ? 'Edit' : 'Create' }} Cashback Setting</h1>

    <form action="{{ isset($cashback) ? route('admin.cashback.update', $cashback) : route('admin.cashback.store') }}" method="POST" class="space-y-6">
        @csrf
        @if(isset($cashback)) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Cashback Percentage -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Cashback Percentage</label>
                <select name="cashback_percentage" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i = 5; $i <= 100; $i += 5)
                        <option value="{{ $i }}" {{ old('cashback_percentage', $cashback->cashback_percentage ?? '') == $i ? 'selected' : '' }}>
                            {{ $i }}%
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Lose Calculation -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Lose Calculation</label>
                <select name="lose_calculation" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach([3,5,7,10,15,30,45,60,90] as $days)
                        <option value="{{ $days }}" {{ old('lose_calculation', $cashback->lose_calculation ?? '') == $days ? 'selected' : '' }}>
                            {{ $days }} days
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Wager -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Wager</label>
                <select name="wager" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i=5; $i<=15; $i++)
                        <option value="{{ $i }}" {{ old('wager', $cashback->wager ?? '') == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Playing Time -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Playing Time</label>
                <select name="playing_time" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach([6,12,24,48,72] as $hours)
                        <option value="{{ $hours }}" {{ old('playing_time', $cashback->playing_time ?? '') == $hours ? 'selected' : '' }}>
                            {{ $hours }} hours
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Activation Days -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Activation Day</label>
                <select name="activation_days" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                        <option value="{{ $day }}" {{ old('activation_days', $cashback->activation_days ?? '') == $day ? 'selected' : '' }}>
                            {{ $day }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Maximum Claim -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Maximum Claim</label>
                <select name="maximum_claim" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @for($i=1; $i<=5; $i++)
                        <option value="{{ $i }}" {{ old('maximum_claim', $cashback->maximum_claim ?? '') == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Level -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">Level</label>
                <select name="level_id" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ old('level_id', $cashback->level_id ?? '') == $level->id ? 'selected' : '' }}>
                            {{ $level->level }} - {{ $level->achievements }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                {{ isset($cashback) ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>
</div>
@endsection
