@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-4 md:mb-0">Drew Lotteries</h1>
        <div class="flex space-x-4">
            <a href="{{ route('user.lottaries.view') }}" class="px-6 py-2 bg-[#1a2634] border border-gray-700 text-white rounded-md font-semibold shadow-md hover:bg-[#223141] transition-colors flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Current Lotteries
            </a>
            <a href="{{ route('user.lottaries.drew') }}" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-md font-semibold shadow-md flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                View Drawn Lotteries
            </a>
        </div>
    </div>

    @if($lottaries->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($lottaries as $lottary)
        <div class="bg-[#1a2634] rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:scale-[1.02] border border-gray-700">
            <div class="relative">
                <img src="{{ $lottary->photo ? asset('storage/' . $lottary->photo) : 'https://via.placeholder.com/400x200/1a2634/ffffff?text=Lottery' }}"
                    alt="{{ $lottary->title }}"
                    class="w-full h-48 object-cover">

                <div class="absolute top-4 right-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                    {{ $setting->currency_symble ?? '' }} {{ number_format($lottary->price, 2) }} {{ $setting->site_currency ?? '' }}
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-[#0b141d] to-transparent h-12"></div>
            </div>

            <div class="p-6">
                <h3 class="text-xl font-bold text-white mb-2">{{ $lottary->title }}</h3>
                <p class="text-gray-400 mb-4 line-clamp-2">{{ $lottary->description }}</p>

                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-sm text-gray-400">Draw Date</p>
                        <p class="text-blue-400 font-semibold">{{ \Carbon\Carbon::parse($lottary->draw_date)->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Prize Number</p>
                        <p class="text-blue-400 font-semibold">{{ $lottary->prize_number }}</p>
                    </div>
                </div>

                <div class="flex justify-between mt-6">
                    {{-- View Details --}}
                    <a href="{{ route('user.lottaries.show', $lottary->id) }}"
                        class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 15.5v-11a2 2 0 012-2h16a2 2 0 012 2v11a2 2 0 01-2 2h-16a2 2 0 01-2-2z" />
                        </svg>
                        View Details
                    </a>

                    <a href="{{ route('lottaries.winners', $lottary->id) }}"
                        class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 15.5v-11a2 2 0 012-2h16a2 2 0 012 2v11a2 2 0 01-2 2h-16a2 2 0 01-2-2z" />
                        </svg>
                        View Winner
                    </a>

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
        @endforeach
    </div>

    <div class="mt-8">
        {{ $lottaries->links('vendor.pagination.custom') }}
    </div>
    @else
    <div class="bg-[#1a2634] rounded-lg shadow-lg p-8 text-center border border-gray-700">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-900/30 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">No Lotteries Available</h3>
        <p class="text-gray-400">There are currently no Drew lotteries. Please check back later.</p>
    </div>
    @endif
</div>
@endsection