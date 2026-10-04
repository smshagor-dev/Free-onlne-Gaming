@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Welcome back, {{ auth()->user()->name ?? auth()->user()->username }} !</h1>
            <p class="text-gray-400 text-lg">Exclusive bonuses are waiting for you. Claim them now to boost your experience.</p>
        </div>

        <!-- Bonus Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($bonusSettings as $setting)
            <div class="bg-[#152232] rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl hover:translate-y-[-5px] border border-gray-700">
                <!-- Card Top - Photo Area -->
                <div class="h-48 bg-gradient-to-r from-blue-600/20 to-purple-600/20 relative">
                    @if($setting->photo)
                    <img src="{{ asset('storage/' . $setting->photo) }}" alt="{{ $setting->title }}" class="w-full h-full object-cover">
                    @else
                    <div class="flex items-center justify-center h-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-blue-400 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    @endif
                    <div class="absolute top-4 right-4 bg-green-500 px-3 py-1 rounded-full shadow-md">
                        <span class="text-sm font-bold text-white">
                            @if($setting->bonus_type === 'First Deposit Bonus')
                            UpTo +{{ $setting->bonus_percentage ?? '0' }}%
                            @elseif($setting->bonus_type === 'Welcome Bonus')
                            UpTo {{ $setting->minimum_bonus ?? '0' }} {{ auth()->user()->currency ?? '' }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Card Content -->
                <div class="p-6">
                    <div class="flex items-start mb-5">
                        <div class="flex-1">
                            <h3 class="font-bold text-white text-xl mb-1">{{ $setting->title }}</h3>
                            <p class="text-blue-300 text-sm">
                                @if($setting->bonus_type === 'First Deposit Bonus')
                                Minimum Deposit: {{ $setting->minimum_bonus ?? 'N/A' }}
                                @elseif($setting->bonus_type === 'Welcome Bonus')
                                Get Bonus: {{ $setting->minimum_bonus ?? 'N/A' }}
                                @else
                                {{ $setting->minimum_bonus ?? 'N/A' }}
                                @endif
                            </p>
                            <p class="text-blue-300 text-sm">{{ $setting->bonus_type }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-[#1e2e42] p-4 rounded-lg border border-gray-700">
                            <div class="flex items-center">
                                <div class="bg-blue-500/10 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">Wager</p>
                                    <p class="text-white font-semibold">{{ $setting->wager ?? 'N/A' }}x</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#1e2e42] p-4 rounded-lg border border-gray-700">
                            <div class="flex items-center">
                                <div class="bg-purple-500/10 p-2 rounded-lg mr-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-xs">Max Claims</p>
                                    <p class="text-white font-semibold">
                                        {{ $setting->maximum_claim_in_a_day ?? 'N/A' }}
                                        @if($setting->bonus_type === 'First Deposit Bonus')
                                        times only
                                        @elseif($setting->bonus_type === 'Welcome Bonus')
                                        times only
                                        @endif
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Footer with Button -->
                <div class="px-6 pb-6">
                    <form method="POST"
                        @if($setting->bonus_type === 'Welcome Bonus')
                        action="{{ route('user.bonus.claim.welcome') }}"
                        @elseif($setting->bonus_type === 'First Deposit Bonus')
                        action="{{ route('user.bonus.claim.first_deposit') }}"
                        @endif
                        >
                        @csrf
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 flex items-center justify-center shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
                            Claim {{ $setting->bonus_type }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Skip Button -->
        <div class="mt-10 text-center">
            <a href="{{ route('promotion.index') }}" class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-gray-300 font-medium rounded-lg hover:bg-[#25354a] transition-colors duration-300 border border-gray-700">
                <span>Explore More Bonus</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            <a href="/" class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-600 text-gray-300 font-medium rounded-lg hover:bg-[#25354a] transition-colors duration-300 border border-gray-700">
                <span>Skip for Now</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection