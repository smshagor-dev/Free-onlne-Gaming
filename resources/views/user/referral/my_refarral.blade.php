@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] text-gray-100 py-4 px-3 sm:px-4 md:py-8">
    <div class="container mx-auto max-w-5xl">
        <!-- Header Section -->
        <div class="mb-6 md:mb-8 text-center">
            <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">Referral Program</h1>
            <p class="text-blue-300 text-sm sm:text-base">Invite friends and earn rewards</p>
        </div>

        <!-- Stats Overview - Stack on mobile -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 md:mb-8">
            <div class="bg-[#1a2636] rounded-xl p-4 sm:p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-blue-700 p-2 sm:p-3 mr-3 sm:mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm text-blue-300">Referred Users</p>
                        <p class="text-lg sm:text-xl font-bold text-white">{{ $totalReferredUsers }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#1a2636] rounded-xl p-4 sm:p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-green-700 p-2 sm:p-3 mr-3 sm:mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-green-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm text-blue-300">Total Deposit</p>
                        <p class="text-lg sm:text-xl font-bold text-white">{{ number_format($totalApprovedDeposit, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-[#1a2636] rounded-xl p-4 sm:p-5 shadow-lg border border-[#2a3b52]">
                <div class="flex items-center">
                    <div class="rounded-full bg-purple-700 p-2 sm:p-3 mr-3 sm:mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-purple-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs sm:text-sm text-blue-300">Current Level</p>
                        <p class="text-lg sm:text-xl font-bold text-white">{{ $currentLevel ? 'Level ' . $currentLevel->level : 'None' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Section -->
        <div class="bg-[#1a2636] rounded-xl p-4 sm:p-6 mb-6 md:mb-8 shadow-lg border border-[#2a3b52]">
            <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4 text-blue-300 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Invite Friends & Earn
            </h3>

            <div class="space-y-3 sm:space-y-4">
                <!-- Referral URL -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-blue-300 mb-1">Your Referral Link</label>
                    <div class="flex flex-col xs:flex-row">
                        <input type="text" 
                               value="{{ url('/register?ref=' . auth()->user()->referral_code) }}" 
                               class="flex-grow px-3 sm:px-4 py-2 sm:py-3 bg-[#0b141d] border border-[#2a3b52] rounded-t-lg xs:rounded-l-lg xs:rounded-tr-none text-xs sm:text-sm text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               readonly>
                        <button
                            onclick="copyToClipboard('{{ url('/register?ref=' . auth()->user()->referral_code) }}', this)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 sm:py-3 rounded-b-lg xs:rounded-r-lg xs:rounded-bl-none transition-colors duration-200 flex items-center justify-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy Link
                        </button>
                    </div>
                </div>

                <!-- Referral Code Only -->
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-blue-300 mb-1">Your Referral Code</label>
                    <div class="flex flex-col xs:flex-row">
                        <div class="flex-grow px-3 sm:px-4 py-2 sm:py-3 bg-[#0b141d] border border-[#2a3b52] rounded-t-lg xs:rounded-l-lg xs:rounded-tr-none font-mono text-xs sm:text-sm text-gray-200">
                            {{ auth()->user()->referral_code }}
                        </div>
                        <button
                            onclick="copyToClipboard('{{ auth()->user()->referral_code }}', this)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 sm:py-3 rounded-b-lg xs:rounded-r-lg xs:rounded-bl-none transition-colors duration-200 flex items-center justify-center text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Copy Code
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referred Users Section -->
        <div>
            <div class="flex justify-between items-center mb-3 sm:mb-4">
                <h2 class="text-xl sm:text-2xl font-bold text-white flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 mr-2 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Your Referred Users
                </h2>
                
                @if($referralUsersData->count())
                <span class="bg-blue-900 text-blue-200 text-xs sm:text-sm font-medium px-2 sm:px-3 py-1 rounded-full">
                    {{ $referralUsersData->count() }} {{ Str::plural('user', $referralUsersData->count()) }}
                </span>
                @endif
            </div>

            @if($referralUsersData->count())
            <div class="bg-[#1a2636] shadow-md rounded-lg overflow-hidden border border-[#2a3b52]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#2a3b52]">
                        <thead class="bg-[#0b141d]">
                            <tr>
                                <th scope="col" class="px-4 py-2 sm:px-6 sm:py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-4 py-2 sm:px-6 sm:py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Registered</th>
                                <th scope="col" class="px-4 py-2 sm:px-6 sm:py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Deposit</th>
                                <th scope="col" class="px-4 py-2 sm:px-6 sm:py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Code</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#1a2636] divide-y divide-[#2a3b52]">
                            @foreach($referralUsersData as $data)
                            <tr class="hover:bg-[#223044] transition-colors duration-150">
                                <td class="px-4 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-blue-800 flex items-center justify-center text-blue-200 font-medium text-sm sm:text-base">
                                                {{ substr($data['user']->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-2 sm:ml-4">
                                            <div class="text-xs sm:text-sm font-medium text-white">{{ $data['user']->name ?? $data['user']->username }}</div>
                                            <div class="text-xs text-blue-300 sm:hidden">{{ $data['user']->created_at->format('M j, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-300 hidden sm:table-cell">
                                    {{ $data['user']->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-300">
                                    {{ number_format($data['total_deposit'], 2) }}
                                </td>
                                <td class="px-4 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-900 text-blue-200">
                                        {{ $data['user']->referrer }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-[#1a2636] rounded-lg shadow-sm p-6 sm:p-8 text-center border border-[#2a3b52]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-blue-700 mb-3 sm:mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-base sm:text-lg font-medium text-white mb-1">No users referred yet</h3>
                <p class="text-blue-300 text-xs sm:text-sm">Start sharing your referral link to invite friends</p>
            </div>
            @endif
        </div>
        
        <!-- Pagination -->
        @if($referralUsersData->count())
        <div class="mt-4">
            {{ $referredUsers->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    function copyToClipboard(text, button) {
        navigator.clipboard.writeText(text).then(function() {
            // Change button text temporarily
            const originalText = button.innerHTML;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
</script>
@endsection