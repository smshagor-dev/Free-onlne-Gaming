@extends('layouts.admin')

@section('title', 'Lottery Winners - ' . $lottary->title)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-2 rounded-lg mr-3">
                    <i class="fas fa-trophy"></i>
                </span>
                Winners - {{ $lottary->title }}
            </h1>
            <p class="text-gray-600 mt-2">Congratulations to all the winners of this lottery draw</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <a href="{{ route('admin.lottaries.transactions') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-md">
                <i class="fa fa-arrow-left"> </i> Transactions
            </a>
            <a href="{{ route('admin.lottaries.transactions.show', $lottary->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-300">
                <i class="fas fa-list mr-2"></i> View Transactions
            </a>
        </div>
    </div>

    <!-- Lottery Info Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center">
            <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                @if($lottary->photo)
                    <img src="{{ asset('storage/' . $lottary->photo) }}" alt="{{ $lottary->title }}" class="w-20 h-20 object-cover rounded-lg">
                @else
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-white text-2xl"></i>
                    </div>
                @endif
            </div>
            <div class="flex-grow">
                <h2 class="text-xl font-semibold text-gray-800">{{ $lottary->title }}</h2>
                <p class="text-gray-600 mt-1">{{ Str::limit($lottary->description, 100) }}</p>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-500">Draw Date</p>
                        <p class="font-semibold text-gray-800">{{ $lottary->draw_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-semibold {{ $lottary->is_draw ? 'text-green-600' : 'text-blue-600' }}">
                            {{ $lottary->is_draw ? 'Draw Completed' : 'Active' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Prize Number</p>
                        <p class="font-semibold text-gray-800">{{ $lottary->prize_number ?? 'TBD' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Winner Number</p>
                        <p class="font-semibold text-gray-800">{{ $lottary->winner_number ?? 'Not drawn' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Total Winners</h2>
                    <p class="text-2xl font-bold text-blue-700">{{ $winners->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-600 text-white rounded-full flex items-center justify-center">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Total Prize Value</h2>
                    <p class="text-2xl font-bold text-green-700">
                    {{ $setting->currency_symble ?? '' }}{{ number_format($winners->sum(fn($winner) => $winner->prize->price), 2) }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-600 text-white rounded-full flex items-center justify-center">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Winning Tickets</h2>
                    <p class="text-2xl font-bold text-purple-700">{{ $winners->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($winners->count() > 0)
        <!-- Winners Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between">
                <h2 class="text-lg font-semibold text-gray-800">Winners List</h2>
                <div class="mt-2 md:mt-0">
                    <input type="text" placeholder="Search winners..." class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64">
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winner</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Info</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Number</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prize Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($winners as $index => $winner)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $index + 1 }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($winner->user && $winner->user->profile_photo_path)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $winner->user->profile_photo_path) }}" alt="{{ $winner->user->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-indigo-600 flex items-center justify-center text-white font-bold">
                                                {{ $winner->user ? substr($winner->user->name, 0, 1) : '?' }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $winner->user->name ?? 'Unknown User' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $winner->user->username ?? 'Unknown User' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $winner->user->email ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">
                                    @if($winner->user && $winner->user->mobile_number)
                                        {{ $winner->user->mobile_number }}
                                    @else
                                        Phone: N/A
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 font-mono">
                                    {{ $winner->transaction->ticket_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-green-700">{{ $winner->prize->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-900 mt-1">
                                    <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gradient-to-r from-green-100 to-green-200 text-green-800 border border-green-200">
                                    {{ $setting->currency_symble ?? '' }}{{ number_format($winner->prize->price ?? 0, 2) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="#" class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="#" class="text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-50" title="Contact Winner">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                    <a href="#" class="text-purple-600 hover:text-purple-900 p-1 rounded-full hover:bg-purple-50" title="Prize Details">
                                        <i class="fas fa-gift"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Celebration Section -->
        <div class="mt-8 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                <i class="fas fa-glass-cheers text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Congratulations to All Winners!</h3>
            <p class="text-gray-600">Prizes will be distributed according to the lottery terms and conditions.</p>
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gray-100 text-gray-400 mb-6">
                <i class="fas fa-trophy text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Winners Yet</h3>
            <p class="text-gray-500 max-w-md mx-auto mb-6">
                @if($lottary->is_draw)
                    The lottery draw has been completed but no winners were selected.
                @else
                    The lottery draw hasn't been completed yet. Check back after the draw date to see the winners.
                @endif
            </p>
            <div class="flex justify-center space-x-3">
                <a href="{{ route('admin.lottaries.transactions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-300">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Lotteries
                </a>
                @if(!$lottary->is_draw)
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300">
                        <i class="fas fa-calendar-alt mr-2"></i> View Draw Details
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>



<style>
    @media (max-width: 640px) {
        .container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
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
                const confettiSettings = { target: 'confetti-canvas' };
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