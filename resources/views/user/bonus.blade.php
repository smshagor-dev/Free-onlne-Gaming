@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] py-8 px-4 sm:px-6 lg:px-8 text-white">
    <div class="max-w-6xl mx-auto">

        <!-- User greeting section -->
        <div class="bg-gradient-to-r from-blue-900 to-indigo-800 rounded-lg p-6 mb-6 shadow-lg">
            <h2 class="text-2xl font-bold mb-2">Hello, {{ $user->name }}</h2>
            <p class="text-lg">Your Bonus Balance:
                <strong class="text-yellow-400">{{ number_format($user->bonus_balance, 2) }}</strong>
            </p>
        </div>

        @if($bonus)
        <!-- Bonus card -->
        <div class="bg-gray-900 rounded-xl shadow-lg overflow-hidden transition-transform duration-300 hover:shadow-2xl border border-gray-700">

            <!-- Bonus photo -->
            @if($bonus['photo'])
            <div class="w-full h-48 overflow-hidden">
                <img src="{{ asset('storage/'.$bonus['photo']) }}" alt="Bonus" class="w-full h-full object-cover">
            </div>
            @endif

            <!-- Bonus details -->
            <div class="p-6">
                <h3 class="text-2xl font-bold text-white mb-4">{{ $bonus['title'] }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Bonus type -->
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div>
                            <p class="text-sm text-gray-400">Type</p>
                            <p class="font-semibold text-white">{{ $bonus['bonus_type'] }}</p>
                        </div>
                    </div>

                    <!-- Wager requirement -->
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <div>
                            <p class="text-sm text-gray-400">Wager Requirement</p>
                            <p class="font-semibold text-white">{{ $bonus['wager'] }}x</p>
                        </div>
                    </div>

                    <!-- Bonus percentage -->
                    @if($bonus['bonus_type'] !== 'Welcome Bonus')
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm text-gray-400">Bonus Percentage</p>
                                <p class="font-semibold text-white">{{ $bonus['bonus_percentage'] }}%</p>
                            </div>
                        </div>
                        @endif

                    </div>

                    <!-- Expiry -->
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm text-gray-400">Expires At</p>
                            <p class="font-semibold text-white">{{ $bonus['expires_at'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Conditions -->
                <div class="bg-gray-800 p-4 rounded-lg mb-6">
                    <h4 class="font-semibold text-white mb-2">Bonus Conditions:</h4>
                    <ul class="list-disc list-inside text-sm text-gray-300 space-y-1">
                        @if($bonus['bonus_type'] !== 'Welcome Bonus')
                        <li>Minimum deposit: {{ $bonus['minimum_bonus'] }}</li>
                        <li>Maximum bonus: {{ number_format($bonus['minimum_bonus'] * 10, 2) }}</li>
                        @endif
                        <li>Wager must be completed within {{ $bonus['bonus_time'] }}.</li>
                        <li>Some games contribute differently to wagering requirements</li>
                        <li>This bonus cannot be used in conjunction with any other offer</li>
                    </ul>
                </div>

                <!-- Action buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Balance box -->
                    <div class="flex-1 bg-gradient-to-r from-green-400 to-green-600 rounded-lg p-0.5">
                        <div class="flex items-center justify-between bg-gray-900 rounded-md py-3 px-4">
                            <span class="text-gray-300 font-medium">Your Balance:</span>
                            <span class="text-green-400 font-bold text-lg">{{ number_format($user->bonus_balance, 2) }}</span>
                        </div>
                    </div>

                    <!-- Play button -->
                    <a href="{{ route('casino.bonus.index') }}"
                        class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold py-3 px-6 rounded-lg transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Play Now
                    </a>
                </div>
            </div>
        </div>

        @else
        <!-- No bonus available -->
        <div class="bg-gray-900 rounded-xl shadow-lg p-8 text-center border border-gray-700">
            <svg class="h-16 w-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-xl font-bold text-white mb-2">No Active Bonus</h3>
            <p class="text-gray-400">There are no bonus offers available at the moment. Please check back later!</p>
            <a href="{{ route('promotion.index') }}"
                class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                Get Bonus Now
            </a>
        </div>
        @endif
    </div>
</div>
@endsection