@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Send Bonus to User</h2>

        {{-- Search Form --}}
        <form action="{{ route('admin.bonus.search') }}" method="GET" class="mb-6">
            <div class="flex gap-2">
                <input 
                    type="text" 
                    name="query" 
                    placeholder="Enter User ID, Username or Email" 
                    class="flex-1 border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition"
                    value="{{ request('query') ?? '' }}"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Search
                </button>
            </div>
        </form>

        @isset($user)
        {{-- User Info Card --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $user->name ?? 'N/A' }}</h3>
                    <p class="text-gray-600">User Name:{{ $user->username }}</p>
                    <p class="text-sm text-gray-500 mt-1">User ID: {{ $user->user_id }}</p>
                </div>
                <button 
                    id="showBonusFormBtn" 
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium transition flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Send Bonus Now
                </button>
            </div>
        </div>

        {{-- Active Bonus List --}}
        @if($activeBonuses->count())
            <div class="mb-6">
                <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    Active Bonuses
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($activeBonuses as $bonus)
                        <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-medium text-gray-800">{{ $bonus->bonus_type }}</h5>
                                    <p class="text-gray-600">Amount: {{ number_format($bonus->bonus_amount, 2) }}</p>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    Active
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">
                                Expires at: {{ \Carbon\Carbon::parse($bonus->created_at)->addHours($bonus->depositSetting->bonus_time)->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Bonus Send Form (Initially Hidden) --}}
        <div id="bonusFormContainer" class="hidden bg-gray-50 border border-gray-200 rounded-xl p-5 mt-6">
            <h4 class="font-bold text-lg text-gray-800 mb-4">Send New Bonus</h4>
            <form action="{{ route('admin.bonus.send') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Setting</label>
                        <select name="deposit_setting_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            @foreach($depositSettings as $ds)
                                <option value="{{ $ds->id }}">{{ $ds->bonus_type }} ({{ $ds->bonus_percentage }}%)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bonus Amount</label>
                        <input type="number" name="bonus_amount" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Amount</label>
                        <input type="number" name="deposit_amount" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">User Deposit ID</label>
                        <select name="user_deposit_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                            <option value="">-- Select Deposit --</option>
                            @foreach($userDeposits as $deposit)
                                <option value="{{ $deposit->id }}">
                                    #{{ $deposit->id }} - Amount: {{ number_format($deposit->amount, 2) }} ({{ $deposit->created_at->format('Y-m-d H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        id="cancelBonusFormBtn" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2.5 rounded-lg font-medium transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-medium transition flex items-center"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Confirm & Send Bonus
                    </button>
                </div>
            </form>
        </div>
        @endisset
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showBonusFormBtn = document.getElementById('showBonusFormBtn');
    const cancelBonusFormBtn = document.getElementById('cancelBonusFormBtn');
    const bonusFormContainer = document.getElementById('bonusFormContainer');
    
    if (showBonusFormBtn && bonusFormContainer) {
        showBonusFormBtn.addEventListener('click', function() {
            bonusFormContainer.classList.remove('hidden');
            // Scroll to the form
            bonusFormContainer.scrollIntoView({ behavior: 'smooth' });
        });
    }
    
    if (cancelBonusFormBtn && bonusFormContainer) {
        cancelBonusFormBtn.addEventListener('click', function() {
            bonusFormContainer.classList.add('hidden');
        });
    }
});
</script>
@endsection