@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-6xl">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">VIP Cashback</h1>
            <p class="text-gray-400">Track and claim your cashback rewards</p>
            <p class="text-gray-400">Depends on your Current Level#{{$user->level->level ?? '0' }} {{$user->level->achievements ?? ''}} For get Cashback</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Current Balance Card -->
            <div class="bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-700 transform transition-transform hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-white">Current Balance</h2>
                    <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-1">Total Cashback Balance</p>
                    <div class="flex items-end">
                        <span class="text-3xl font-bold text-green-400">{{ number_format($user->cashback, 2) }}</span>
                        <span class="text-green-400 ml-1">{{$user->currency}}</span>
                    </div>
                </div>
                
                <a href="{{ route('casino.cashback.index') }}" class="block w-full py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 text-white font-medium rounded-xl text-center transition-all duration-300 shadow-lg hover:shadow-blue-500/20">
                    Play Now
                </a>
            </div>

            <!-- Available Cashback Card -->
            <div class="bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-700 transform transition-transform hover:scale-[1.02]">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-white">Ready to Claim</h2>
                    <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-400 text-sm mb-1">Available Cashback Amount</p>
                    <div class="flex items-end">
                        <span class="text-3xl font-bold text-green-400">{{ number_format($availableCashback, 2) }}</span>
                        <span class="text-green-400 ml-1">{{$user->currency}}</span>
                    </div>
                </div>
                
                <button id="claimBtn" class="w-full py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-medium rounded-xl transition-all duration-300 shadow-lg hover:shadow-green-500/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Claim Cashback
                </button>
            </div>
        </div>

        <!-- Cashback Rules Section -->
        @if($cashbackSetting)
        <div class="bg-gray-800 rounded-2xl p-6 shadow-lg border border-gray-700">
            <div class="flex items-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-semibold text-white">Cashback Rules & Information</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-700/50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center mr-3">
                            <span class="text-blue-400 font-bold">%</span>
                        </div>
                        <h4 class="text-white font-medium">Cashback Percentage</h4>
                    </div>
                    <p class="text-gray-300 text-sm">{{ $cashbackSetting->cashback_percentage }}%</p>
                </div>
                
                <div class="bg-gray-700/50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-purple-500/20 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-medium">Wager Requirement</h4>
                    </div>
                    <p class="text-gray-300 text-sm">{{ $cashbackSetting->wager }}x</p>
                </div>
                
                <div class="bg-gray-700/50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-medium">Maximum Claim</h4>
                    </div>
                    <p class="text-gray-300 text-sm">{{ $cashbackSetting->maximum_claim }} Time every {{ $cashbackSetting->lose_calculation }} Days</p>
                </div>
                
                <div class="bg-gray-700/50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-medium">Lose Calculation</h4>
                    </div>
                    <p class="text-gray-300 text-sm">Every {{ $cashbackSetting->lose_calculation }} Days</p>
                </div>
                
                <div class="bg-gray-700/50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h4 class="text-white font-medium">Required Playing Time</h4>
                    </div>
                    <p class="text-gray-300 text-sm">Available {{ $cashbackSetting->playing_time }} Hours</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Toast Notification (hidden by default) -->
<div id="toast" class="fixed top-4 right-4 p-4 bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300">
    <div class="flex items-center">
        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <p class="text-white font-medium" id="toast-message">Cashback claimed successfully!</p>
        </div>
    </div>
</div>

<script>
document.getElementById('claimBtn')?.addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Processing...
    `;
    btn.disabled = true;
    
    fetch("{{ route('user.cashback.claim') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            // Show success toast
            showToast(data.message || 'Cashback claimed successfully!');
            setTimeout(() => location.reload(), 2000);
        } else {
            // Show error toast
            showToast(data.message || 'Error claiming cashback', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        showToast('An error occurred. Please try again.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    
    // Set message and color based on type
    toastMessage.textContent = message;
    
    if (type === 'error') {
        toast.classList.remove('border-green-500');
        toast.classList.add('border-red-500');
    } else {
        toast.classList.remove('border-red-500');
        toast.classList.add('border-green-500');
    }
    
    // Show toast
    toast.classList.remove('translate-x-full');
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
    }, 3000);
}
</script>
@endsection