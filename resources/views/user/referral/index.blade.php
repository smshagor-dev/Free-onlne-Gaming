@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] text-gray-100 py-8 px-4">
    <div class="container mx-auto max-w-5xl">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">Referral Program</h1>
            <p class="text-blue-300">Invite friends and earn rewards through our referral program</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-[#1a2636] rounded-xl p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-700 p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-300">Referred Users</p>
                        <p class="text-xl font-bold text-white">{{ $totalReferredUsers }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#1a2636] rounded-xl p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-green-700 p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-300">Total Deposit</p>
                        <p class="text-xl font-bold text-white">{{ number_format($totalApprovedDeposit, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#1a2636] rounded-xl p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-purple-700 p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-300">Current Level</p>
                        <p class="text-xl font-bold text-white">{{ $currentLevel ? 'Level ' . $currentLevel->level : 'None' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Commission Balance Card -->
            <div class="bg-[#1a2636] rounded-xl p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-yellow-700 p-3 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-blue-300">Available Commission</p>
                        <p class="text-xl font-bold text-white" id="commissionAmount">
                            @if($currentLevel)
                                {{ number_format(($totalApprovedDeposit * $currentLevel->commission) / 100, 2) }}
                            @else
                                0.00
                            @endif
                        </p>
                    </div>
                </div>
                
            </div>
            <button id="collectCommissionBtn" 
                        class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ !$currentLevel ? 'disabled' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Collect Commission
                </button>
        </div>

        <!-- Progress to Next Level -->
        @if($nextLevel)
        <div class="bg-[#1a2636] rounded-xl p-6 mb-8 shadow-lg border border-[#2a3b52]">
            <h3 class="text-xl font-semibold mb-4 text-blue-300">Progress to Level {{ $nextLevel->level }}</h3>
            
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-sm text-blue-300 mb-1">
                        <span>Users Needed</span>
                        <span>{{ $usersNeededForNextLevel }} more</span>
                    </div>
                    <div class="w-full bg-[#0b141d] rounded-full h-2.5">
                        @php
                            $userProgress = $totalReferredUsers > 0 ? min(100, ($totalReferredUsers / $nextLevel->register_user) * 100) : 0;
                        @endphp
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $userProgress }}%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between text-sm text-blue-300 mb-1">
                        <span>Deposit Needed</span>
                        <span>{{ number_format($depositNeededForNextLevel, 2) }} more</span>
                    </div>
                    <div class="w-full bg-[#0b141d] rounded-full h-2.5">
                        @php
                            $depositProgress = $totalApprovedDeposit > 0 ? min(100, ($totalApprovedDeposit / $nextLevel->total_deposit) * 100) : 0;
                        @endphp
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ $depositProgress }}%"></div>
                    </div>
                </div>
            </div>
            
            <p class="mt-4 text-sm text-blue-300">
                Reach Level {{ $nextLevel->level }} to earn {{ $nextLevel->commission }}% commission!
            </p>
        </div>
        @endif

        <!-- Referral Levels Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-white flex items-center cursor-pointer" id="toggleLevels">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Referral Levels
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 transition-transform" id="levelsChevron" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </h2>
            </div>
            
            <div id="levelsContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($referralSettings as $setting)
                <div class="bg-[#1a2636] border border-[#2a3b52] rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow duration-200 
                    {{ $currentLevel && $currentLevel->id == $setting->id ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-semibold text-white">Level {{ $setting->level }}</h3>
                        <span class="bg-blue-900 text-blue-200 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $setting->commission }}% Commission
                        </span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span class="text-gray-300">{{ $setting->register_user }} Registered Users</span>
                        </div>
                        
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-gray-300">${{ $setting->total_deposit }} Total Deposit</span>
                        </div>
                    </div>
                    
                    @if($currentLevel && $currentLevel->id == $setting->id)
                    <div class="mt-4 pt-3 border-t border-[#2a3b52]">
                        <span class="bg-green-900 text-green-200 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            Current Level
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-[#1a2636] rounded-xl p-6 max-w-md w-full mx-4 border border-[#2a3b52]">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="mt-3 text-lg font-medium text-white" id="modalTitle">Success!</h3>
            <div class="mt-2">
                <p class="text-sm text-blue-300" id="modalMessage"></p>
            </div>
            <div class="mt-4">
                <button type="button" id="modalCloseBtn" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-blue-900 bg-blue-200 border border-transparent rounded-md hover:bg-blue-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    Got it!
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            // Change button text temporarily
            const originalText = button.innerHTML;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Copied!
            `;
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
        });
    }

    // Toggle referral levels visibility
    document.getElementById('toggleLevels').addEventListener('click', function() {
        const content = document.getElementById('levelsContent');
        const chevron = document.getElementById('levelsChevron');
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            chevron.classList.remove('rotate-180');
        } else {
            content.classList.add('hidden');
            chevron.classList.add('rotate-180');
        }
    });

    // Collect commission functionality
    document.getElementById('collectCommissionBtn').addEventListener('click', function() {
        const button = this;
        const originalText = button.innerHTML;
        
        // Show loading state
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Processing...
        `;
        button.disabled = true;
        
        // Make AJAX request
        fetch('{{ route("user.referral.collectBalance") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                // Show success modal
                document.getElementById('modalTitle').textContent = 'Commission Collected!';
                document.getElementById('modalMessage').textContent = data.message + ' Your new balance is $' + data.current_balance;
                document.getElementById('successModal').classList.remove('hidden');
                
                // Update commission amount display
                document.getElementById('commissionAmount').textContent = '$0.00';
            } else if (data.error) {
                // Show error modal
                document.getElementById('modalTitle').textContent = 'Error';
                document.getElementById('modalMessage').textContent = data.error;
                document.getElementById('successModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalTitle').textContent = 'Error';
            document.getElementById('modalMessage').textContent = 'An error occurred while processing your request.';
            document.getElementById('successModal').classList.remove('hidden');
        })
        .finally(() => {
            // Reset button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });

    // Close modal
    document.getElementById('modalCloseBtn').addEventListener('click', function() {
        document.getElementById('successModal').classList.add('hidden');
    });
</script>
@endsection