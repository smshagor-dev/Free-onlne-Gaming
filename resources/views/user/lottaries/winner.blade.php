@extends('layouts.app')

@section('title', 'Lottery Winners - ' . $lottary->title)

@section('content')
<div class="min-h-screen bg-[#0b141d] text-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center">
                    <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-2 rounded-lg mr-3">
                        <i class="fas fa-trophy"></i>
                    </span>
                    Winners - {{ $lottary->title }}
                </h1>
                <p class="text-gray-400 mt-2">Congratulations to all the winners of this lottery draw</p>
            </div>
            <div class="mt-4 md:mt-0 flex space-x-2">
                <a href="{{ route('user.lottaries.view') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-md">
                    <i class="fa fa-arrow-left mr-2"></i> Buy Lottary Tickets
                </a>
                <a href="{{ route('user.lottaries.my') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors duration-300">
                    <i class="fas fa-ticket-alt mr-2"></i> My Tickets
                </a>
            </div>
        </div>

        <!-- Lottery Info Card -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row md:items-center">
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                    @if($lottary->photo)
                        <img src="{{ asset('storage/' . $lottary->photo) }}" alt="{{ $lottary->title }}" class="w-20 h-20 object-cover rounded-lg">
                    @else
                        <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white text-2xl"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-grow">
                    <h2 class="text-xl font-semibold text-white">{{ $lottary->title }}</h2>
                    <p class="text-gray-400 mt-1">{{ Str::limit($lottary->description, 100) }}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-400">Draw Date</p>
                            <p class="font-semibold text-white">{{ $lottary->draw_date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Status</p>
                            <p class="font-semibold {{ $lottary->is_draw ? 'text-green-400' : 'text-blue-400' }}">
                                {{ $lottary->is_draw ? 'Draw Completed' : 'Active' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Prize Number</p>
                            <p class="font-semibold text-white">{{ $lottary->prize_number ?? 'TBD' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Winner Number</p>
                            <p class="font-semibold text-white">{{ $lottary->winner_number ?? 'Not drawn' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-900/50 to-blue-800/50 border border-blue-700 rounded-xl p-5 shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-200">Total Winners</h2>
                        <p class="text-2xl font-bold text-blue-400">{{ $winners->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-900/50 to-green-800/50 border border-green-700 rounded-xl p-5 shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-600 text-white rounded-full flex items-center justify-center">
                            <i class="fas fa-award"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-200">Total Prize Value</h2>
                        <p class="text-2xl font-bold text-green-400">
                            {{ $setting->currency_symble ?? '' }}{{ number_format($winners->sum(fn($winner) => $winner->prize->price), 2) }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-900/50 to-purple-800/50 border border-purple-700 rounded-xl p-5 shadow-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-600 text-white rounded-full flex items-center justify-center">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-200">Winning Tickets</h2>
                        <p class="text-2xl font-bold text-purple-400">{{ $winners->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($winners->count() > 0)
            <!-- Winners Table -->
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-700">
                <div class="px-6 py-4 border-b border-gray-700 bg-gray-900 flex flex-col md:flex-row md:items-center md:justify-between">
                    <h2 class="text-lg font-semibold text-white">Winners List</h2>
                    <div class="mt-2 md:mt-0">
                        <input type="text" placeholder="Search winners..." class="px-4 py-2 bg-gray-700 border border-gray-600 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64 placeholder-gray-400">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Winner</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Ticket Number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Prize Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @foreach($winners as $index => $winner)
                            <tr class="hover:bg-gray-750 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-white">{{ $index + 1 }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($winner->user && $winner->user->profile_photo_path)
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $winner->user->profile_photo_path) }}" alt="{{ $winner->user->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold">
                                                    {{ $winner->user ? substr($winner->user->name, 0, 1) : '?' }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-white">
                                                {{ $winner->user->name ?? 'Unknown User' }}
                                            </div>
                                            <div class="text-sm text-gray-400">{{ $winner->user->username ?? 'User' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-900/50 text-blue-300 font-mono border border-blue-700">
                                        {{ $winner->transaction->ticket_number ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-green-300">{{ $winner->prize->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-white mt-1">
                                        <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gradient-to-r from-green-900/50 to-green-800/50 text-green-300 border border-green-700">
                                            {{ $setting->currency_symble ?? '' }}{{ number_format($winner->prize->price ?? 0, 2) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button class="text-blue-400 hover:text-blue-300 p-1 rounded-full hover:bg-blue-900/30 transition-colors duration-200" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="text-purple-400 hover:text-purple-300 p-1 rounded-full hover:bg-purple-900/30 transition-colors duration-200" title="Prize Details">
                                            <i class="fas fa-gift"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Celebration Section -->
            <div class="mt-8 bg-gradient-to-r from-yellow-900/30 to-orange-900/30 border border-yellow-700 rounded-xl p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-800/50 text-yellow-400 mb-4">
                    <i class="fas fa-glass-cheers text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Congratulations to All Winners!</h3>
                <p class="text-gray-400">Prizes will be distributed according to the lottery terms and conditions.</p>
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-700 p-12 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-700 text-gray-400 mb-6">
                    <i class="fas fa-trophy text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">No Winners Yet</h3>
                <p class="text-gray-400 max-w-md mx-auto mb-6">
                    @if($lottary->is_draw)
                        The lottery draw has been completed but no winners were selected.
                    @else
                        The lottery draw hasn't been completed yet. Check back after the draw date to see the winners.
                    @endif
                </p>
                <div class="flex justify-center space-x-3">
                    <a href="{{ route('user.lottaries.view') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Lotteries
                    </a>
                    @if(!$lottary->is_draw)
                        <button class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300">
                            <i class="fas fa-calendar-alt mr-2"></i> View Draw Details
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    .bg-gray-750 {
        background-color: #2d3748;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Winners page loaded');
        
        // Add search functionality
        const searchInput = document.querySelector('input[type="text"]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Add celebration effect when page loads with winners
        @if($winners->count() > 0)
            setTimeout(function() {
                const confettiSettings = { 
                    target: 'confetti-canvas',
                    colors: [
                        [62, 127, 235], // Blue
                        [233, 63, 126], // Pink
                        [251, 191, 36], // Yellow
                        [16, 185, 129], // Green
                        [139, 92, 246]  // Purple
                    ],
                    props: ['circle', 'square', 'line'],
                    clock: 25
                };
                const confetti = new ConfettiGenerator(confettiSettings);
                confetti.render();
                
                // Remove confetti after 3 seconds
                setTimeout(() => {
                    const canvas = document.getElementById('confetti-canvas');
                    if (canvas) canvas.remove();
                }, 3000);
            }, 1000);
        @endif
    });
</script>

@if($winners->count() > 0)
<!-- Confetti effect for winners celebration -->
<canvas id="confetti-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></canvas>
<script src="https://cdn.jsdelivr.net/npm/confetti-js@0.0.18/dist/index.min.js"></script>
@endif
@endsection