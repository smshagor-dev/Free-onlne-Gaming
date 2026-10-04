@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Create Cashback Settings</h1>

    <form action="{{ route('admin.cashback.store') }}" method="POST" class="space-y-6">
        @csrf

        @foreach($levels as $level)
        <div class="p-4 border rounded-md bg-gray-50">
            <h2 class="text-lg font-semibold mb-4">Level {{ $level->level }} - {{ $level->achievements }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Cashback Percentage (5% - 100%) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Cashback Percentage</label>
                    <select name="cashback[{{ $level->id }}][cashback_percentage]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($i=5; $i<=100; $i+=5)
                            <option value="{{ $i }}" {{ old('cashback.'.$level->id.'.cashback_percentage') == $i ? 'selected' : '' }}>{{ $i }}%</option>
                        @endfor
                    </select>
                </div>

                <!-- Lose Calculation -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Lose Calculation</label>
                    <select name="cashback[{{ $level->id }}][lose_calculation]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach([3,5,7,10,15,30,45,60,90] as $days)
                            <option value="{{ $days }}" {{ old('cashback.'.$level->id.'.lose_calculation') == $days ? 'selected' : '' }}>
                                {{ $days }} days
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Wager (5 - 15) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Wager</label>
                    <select name="cashback[{{ $level->id }}][wager]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($i=5; $i<=15; $i++)
                            <option value="{{ $i }}" {{ old('cashback.'.$level->id.'.wager') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Playing Time -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Playing Time</label>
                    <select name="cashback[{{ $level->id }}][playing_time]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach([6,12,24,48,72] as $hours)
                            <option value="{{ $hours }}" {{ old('cashback.'.$level->id.'.playing_time') == $hours ? 'selected' : '' }}>
                                {{ $hours }} hours
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Activation Day -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Activation Day</label>
                    <select name="cashback[{{ $level->id }}][activation_days]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                            <option value="{{ $day }}" {{ old('cashback.'.$level->id.'.activation_days') == $day ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Maximum Claim (1-5) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Maximum Claim</label>
                    <select name="cashback[{{ $level->id }}][maximum_claim]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($i=1; $i<=5; $i++)
                            <option value="{{ $i }}" {{ old('cashback.'.$level->id.'.maximum_claim') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>

            </div>
        </div>
        @endforeach

        <!-- Submit Button -->
        <div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors mt-4">
                Save Cashback Settings
            </button>
        </div>
    </form>
</div>
@endsection
