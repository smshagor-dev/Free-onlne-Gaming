@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-white mb-3">VIP Bonus Program</h1>
            <p class="text-purple-200 text-lg max-w-2xl mx-auto">Exclusive rewards for our most valued players. Claim your VIP bonuses and elevate your gaming experience.</p>
        </div>

        <!-- Bonus Cards Section -->
        @if($bonusSettings->isEmpty())
            <div class="col-span-full bg-white/10 backdrop-blur-lg rounded-2xl p-8 text-center border border-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-purple-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-2xl font-bold text-white mb-2">No VIP Bonuses Available</h3>
                <p class="text-purple-200">You don't have any VIP bonuses at the moment. Keep playing to unlock exclusive rewards!</p>
                <a href="#"
                    class="mt-6 inline-block px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-xl transition-all duration-300 pointer-events-none opacity-50 cursor-not-allowed">
                    Start Playing
                </a>

            </div>
        @else
            <div class="space-y-6 mb-10">
                @foreach($bonusSettings as $bonus)
                @php
                    $expires = $bonus->created_at->addHours($bonus->playing_time);
                    $isExpiringSoon = now()->diffInHours($expires) < 24;
                    $statusClass = $isExpiringSoon ? 'bg-yellow-500/20 text-yellow-300' : 'bg-green-500/20 text-green-300';
                @endphp
                
                <div class="bg-gradient-to-br w-full from-purple-800 to-indigo-900 rounded-2xl overflow-hidden border border-purple-500/30 shadow-2xl transform transition-transform hover:scale-105">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-purple-700 to-purple-600 p-5 relative overflow-hidden">
                        <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-white/10"></div>
                        <div class="absolute -bottom-4 -left-4 w-16 h-16 rounded-full bg-white/5"></div>
                        
                        <div class="flex justify-between items-center relative z-10">
                            <h3 class="text-white text-xl font-bold">VIP Bonus</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $isExpiringSoon ? 'Expiring Soon' : 'Active' }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5">
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-purple-200">Bonus Amount</span>
                                <span class="text-white font-bold text-xl">{{$bonus->user->currency}} {{ number_format($bonus->user->vip_bonus, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <span class="text-purple-200">Wager Requirement</span>
                                <span class="text-white font-semibold">{{ $bonus->wager }}x</span>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <span class="text-purple-200">Playing Time</span>
                                <span class="text-white font-semibold">{{ $bonus->playing_time }} hours</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-purple-200">Expires</span>
                                <span class="text-white font-semibold text-sm">{{ $expires->format('M j, g:i A') }}</span>
                            </div>
                        </div>

                        <!-- Progress bar for time remaining -->
                        @php
                            $totalHours = $bonus->playing_time;
                            $hoursPassed = now()->diffInHours($bonus->created_at);
                            $percentage = min(100, ($hoursPassed / $totalHours) * 100);
                        @endphp
                        <div class="mb-5">
                            <div class="flex justify-between text-xs text-purple-300 mb-1">
                                <span>Time Remaining</span>
                                @php
                                    $diff = now()->diff($expires);
                                    $hoursLeft = $diff->h + ($diff->days * 24); // total hours
                                    $minutesLeft = $diff->i; // remaining minutes
                                @endphp

                                <span>
                                    {{ $hoursLeft > 0 ? $hoursLeft . 'h ' : '' }}
                                    {{ $minutesLeft > 0 ? $minutesLeft . 'm' : '' }}
                                    left
                                </span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2 rounded-full"></div>
                            </div>
                        </div>

                        <a href="{{ route('casino.vip.bonus.index') }}" class="block w-full py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white font-semibold rounded-xl text-center transition-all duration-300 shadow-lg hover:shadow-green-500/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Play Now
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        <!-- FAQ Section -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/20 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                <div class="bg-purple-900/30 rounded-xl p-5">
                    <h3 class="text-white font-semibold mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        How do I qualify for VIP bonuses?
                    </h3>
                    <p class="text-purple-200 text-sm">VIP status is based on your gameplay activity, loyalty, and deposit history. The more you play, the higher your VIP tier and the better your bonuses.</p>
                </div>
                
                <div class="bg-purple-900/30 rounded-xl p-5">
                    <h3 class="text-white font-semibold mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        What is the wager requirement?
                    </h3>
                    <p class="text-purple-200 text-sm">The wager requirement indicates how many times you need to play through the bonus amount before being able to withdraw any winnings associated with the bonus.</p>
                </div>
                
                <div class="bg-purple-900/30 rounded-xl p-5">
                    <h3 class="text-white font-semibold mb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Can I have multiple active bonuses?
                    </h3>
                    <p class="text-purple-200 text-sm">Yes, higher VIP tiers can have multiple active bonuses simultaneously. Check your VIP status to see how many bonuses you can have active at once.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 p-4 bg-gray-800 border-l-4 border-green-500 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
    <div class="flex items-center">
        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div>
            <p class="text-white font-medium" id="toast-message">Bonus activated successfully!</p>
        </div>
    </div>
</div>

<script>
// Function to show toast notifications
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

// Add animation to cards on page load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bg-gradient-to-br');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
});
</script>
@endsection