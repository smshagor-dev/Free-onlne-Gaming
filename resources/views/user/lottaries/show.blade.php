@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li>
                    <a href="{{ route('user.lottaries.view') }}" class="text-blue-400 hover:text-blue-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Lotteries
                    </a>
                </li>
                <li>
                    <span class="text-gray-500">/</span>
                </li>
                <li class="text-gray-300 hidden md:block">{{ $lottary->title }}</li>
                <li class="text-gray-300 block md:hidden">{{ Str::limit($lottary->title, 10) }}</li>
            </ol>
        </nav>

        <!-- Lottery Details -->
        <div class="bg-[#1a2634] rounded-lg shadow-lg overflow-hidden mb-8 border border-gray-700">
            <div class="relative">
                <img src="{{ $lottary->photo ? asset('storage/' . $lottary->photo) : 'https://via.placeholder.com/400x200/1a2634/ffffff?text=Lottery' }}"
                    alt="{{ $lottary->title }}"
                    class="w-full h-48 object-cover">

                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-[#0b141d] to-transparent h-16"></div>
            </div>

            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-2xl font-bold text-white">{{ $lottary->title }}</h1>
                    <span class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-1 rounded-full text-sm font-semibold shadow-md">
                        {{ $setting->currency_symble ?? '' }} {{ number_format($lottary->price, 2) }} {{ $setting->site_currency ?? '' }}
                    </span>
                </div>

                <p class="text-gray-300 mb-6">{{ $lottary->description }}</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-[#223141] p-4 rounded-lg border border-gray-700">
                        <p class="text-sm text-gray-400 mb-1">Draw Date</p>
                        <p class="text-lg font-semibold text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ \Carbon\Carbon::parse($lottary->draw_date)->format('M d, Y H:i') }}
                        </p>
                    </div>

                    <div class="bg-[#223141] p-4 rounded-lg border border-gray-700">
                        <p class="text-sm text-gray-400 mb-1">Prize Number</p>
                        <p class="text-lg font-semibold text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $lottary->prize_number }}
                        </p>
                    </div>

                    <div class="bg-[#223141] p-4 rounded-lg border border-gray-700">
                        <p class="text-sm text-gray-400 mb-1">Winner Number</p>
                        <p class="text-lg font-semibold text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $lottary->winner_number ?? 'Not drawn yet' }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    {{-- Buy Now --}}
                    @auth
                    <form action="{{ route('user.lottaries.buy', $lottary->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-md 
               hover:from-blue-700 hover:to-purple-700 transition-all shadow-md flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Buy Now for {{ $setting->currency_symble ?? '' }} {{ number_format($lottary->price, 2) }} {{ $setting->site_currency ?? '' }}
                        </button>
                    </form>
                    @endauth

                    @guest
                    <a href="{{ route('login') }}"
                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-md 
                  hover:from-blue-700 hover:to-purple-700 transition-all shadow-md flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Login to Buy
                    </a>
                    @endguest
                </div>
            </div>
        </div>

        <!-- Prizes Section -->
        <h2 class="text-2xl font-bold text-white mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
            Available Prizes
        </h2>

        @if($lottary->prizes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($lottary->prizes as $prize)
            <div class="bg-[#1a2634] rounded-lg shadow-md p-6 border border-gray-700 transition-all hover:border-blue-500/30">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-semibold text-white">{{ $prize->name }}</h3>
                    <span class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-md">
                        {{ $setting->currency_symble ?? '' }} {{ number_format($prize->price, 2) }} {{ $setting->site_currency ?? '' }}
                    </span>
                </div>

                <p class="text-gray-300 mb-4">{{ $prize->description }}</p>

                <div class="bg-[#223141] p-3 rounded-lg border border-gray-600">
                    <p class="text-sm text-gray-400 mb-1">Winner Number</p>
                    <p class="text-lg font-semibold text-blue-400">{{ $prize->price_number }} Person</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-[#1a2634] rounded-lg shadow-md p-6 text-center border border-gray-700">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-900/30 rounded-full mb-3 border border-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-white mb-2">No Prize Details Available</h3>
            <p class="text-gray-400">There are no prize details available for this lottery yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection