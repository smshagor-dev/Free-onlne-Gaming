@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] text-gray-100 py-8 px-4">
    <div class="container mx-auto max-w-6xl">
        <!-- User Profile Header -->
        <div class="flex flex-col md:flex-row items-center mb-8 bg-[#1a2636] rounded-xl shadow p-6 border border-[#2a3b52]">
            <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gradient-to-r from-blue-600 to-purple-700 flex items-center justify-center text-white text-3xl md:text-4xl font-bold mr-0 md:mr-6 mb-4 md:mb-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="text-center md:text-left">
                <h1 class="text-2xl md:text-3xl font-bold text-white">{{ Auth::user()->name ?? Auth::user()->username }}</h1>
                <div class="flex flex-col md:flex-row items-center justify-center md:justify-start mt-2 space-y-2 md:space-y-0 md:space-x-4">
                    <span class="text-blue-300">Level: {{ $current_level->level ?? '0' }}</span>
                    <span class="text-blue-300">Points: {{ Auth::user()->points ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Current Level Card -->
        <div class="bg-[#1a2636] rounded-xl shadow-lg overflow-hidden mb-8 border border-[#2a3b52]">
            <div class="bg-gradient-to-r from-blue-800 to-indigo-900 p-6 text-white">
                <h2 class="text-2xl font-bold">Your Current Achievement</h2>
            </div>
            
            @if($level)
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-center">
                    @if($level->photo)
                    <div class="w-32 h-32 md:w-48 md:h-48 flex-shrink-0 mb-6 md:mb-0 md:mr-8">
                        <img src="{{ asset('storage/' . $level->photo) }}" alt="Level Badge" class="w-full h-full object-contain">
                    </div>
                    @endif
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold text-white mb-2">Level {{ $level->level }}</h3>
                        <p class="text-blue-300 mb-4">{{ $level->achievements ?? 'No achievements description' }}</p>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-blue-300 mb-1">
                                <span>{{ $level->points }} points</span>
                                @if($next_level)
                                    <span>{{ $next_level->points }} points (next level)</span>
                                @endif
                            </div>
                            <div class="w-full bg-[#0b141d] rounded-full h-2.5">
                                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center text-sm text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            {{ round($progress) }}% to next level
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="p-6 text-center text-blue-400">
                No level assigned yet. Start playing games to earn points and unlock achievements!
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('user.levels') }}" class="bg-[#1a2636] p-4 rounded-lg shadow hover:shadow-md transition-shadow flex items-center border border-[#2a3b52] hover:border-blue-600">
                <div class="bg-blue-900 p-3 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-white">View All Levels</h3>
                    <p class="text-sm text-blue-300">See all available achievement levels</p>
                </div>
            </a>
            
            <a href="{{ route('user.games.lastplay')}}" class="bg-[#1a2636] p-4 rounded-lg shadow hover:shadow-md transition-shadow flex items-center border border-[#2a3b52] hover:border-purple-600">
                <div class="bg-purple-900 p-3 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-white">Recent Activity</h3>
                    <p class="text-sm text-purple-300">View your point history</p>
                </div>
            </a>
            
            <a href="{{ route('games.viewIndex')}}" class="bg-[#1a2636] p-4 rounded-lg shadow hover:shadow-md transition-shadow flex items-center border border-[#2a3b52] hover:border-green-600">
                <div class="bg-green-900 p-3 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-white">Earn Points</h3>
                    <p class="text-sm text-green-300">Play games to level up</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection