@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] text-gray-100 py-10 px-4">
    <div class="container mx-auto max-w-6xl">

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-10">
            <h1 class="text-4xl font-extrabold text-white mb-4 sm:mb-0 tracking-wide">Achievement Levels</h1>
            <div class="bg-blue-900 text-blue-200 px-5 py-2 rounded-full text-sm font-semibold shadow-md">
                Current Level: {{ $current_level ? $current_level->level : '0' }}
            </div>
        </div>

        <!-- Circular Progress Card -->
        <div class="bg-[#1a2636] rounded-2xl shadow-xl overflow-hidden mb-10 border border-[#2a3b52] p-6 flex flex-col md:flex-row items-center">
            
            <!-- Circular Progress -->
            <div class="relative w-40 h-40 md:w-48 md:h-48 mx-auto md:mx-0">
                <svg viewBox="0 0 100 100" class="w-full h-full">
                    <!-- Outer Circle Stroke -->
                    <circle cx="50" cy="50" r="48" stroke="url(#gradient)" stroke-width="8" fill="none" />

                    <!-- Water Fill -->
                    <defs>
                        <clipPath id="wave-clip">
                            <path id="wave-path" fill="#3b82f6"></path>
                        </clipPath>
                    </defs>
                    <circle cx="50" cy="50" r="48" fill="#0b141d" />
                    <circle cx="50" cy="50" r="48" fill="#3b82f6" clip-path="url(#wave-clip)" />

                    <defs>
                        <linearGradient id="gradient" x1="1" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#8b5cf6"/>
                        </linearGradient>
                    </defs>

                    <!-- Percentage Text -->
                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="16">
                        {{ round($progress) }}%
                    </text>
                </svg>
            </div>

            <!-- Info Section -->
            <div class="flex-1 md:ml-10 text-center md:text-left">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Level {{ $current_level ? $current_level->level : '0' }}</h2>
                <p class="text-blue-300 mb-4">{{ $user_points }} points earned</p>

                @if($next_level)
                    <div class="flex justify-between items-center text-blue-300 text-sm md:text-base mb-2">
                        <p>
                            Current Level: 
                            <span class="font-semibold text-white">{{ $current_level ? $current_level->level : '0' }} </span>
                        </p>
                        @if($next_level)
                            <p>
                                Next Level: 
                                <span class="font-semibold text-white">{{ $next_level->level }}</span>
                            </p>
                        @else
                            <p class="text-green-400 font-semibold">Max level reached!</p>
                        @endif
                    </div>

                    <div class="w-full bg-[#0b141d] rounded-full h-3 md:h-4 mt-3">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 md:h-4 rounded-full transition-all duration-500 ease-out" 
                             style="width: {{ $progress }}%;">
                        </div>
                    </div>
                    <div class="text-center text-xs md:text-sm text-blue-300 mt-1">
                        Complete {{ round($progress) }}% </br> Need points {{  $next_level->points -  $user_points  }} to Level {{ $next_level->level }}
                    </div>
                @else
                    <p class="text-blue-300">Max level reached!</p>
                @endif
            </div>
        </div>

        <!-- Levels Step View -->
        <div class="relative w-full overflow-hidden">
            <div id="levels-track" class="flex transition-transform duration-500 ease-out">
                @foreach($levels as $index => $level)
                <div class="flex-shrink-0 w-80 mx-4 relative" data-index="{{ $index }}" data-is-user="{{ $level['is_user_level'] ? '1' : '0' }}">
                    <!-- Card -->
                    <div class="bg-[#1a2636] rounded-2xl shadow-md overflow-hidden border border-[#2a3b52] transform transition-transform hover:scale-105 hover:shadow-2xl
                                {{ $level['is_user_level'] ? 'ring-2 ring-blue-500' : '' }} {{ !$level['is_unlocked'] ? 'opacity-70' : '' }}">
                        
                        <div class="bg-gradient-to-r {{ $level['is_user_level'] ? 'from-blue-700 to-indigo-800' : ($level['is_unlocked'] ? 'from-green-700 to-emerald-800' : 'from-gray-700 to-gray-800') }} p-5 text-center">
                            <h3 class="text-2xl font-bold text-white tracking-wide">Level {{ $level['level'] }}</h3>
                        </div>

                        <div class="p-6 flex flex-col items-center">
                            <div class="flex justify-center mb-4">
                                @if($level['photo'] && $level['is_unlocked'])
                                    <img src="{{ $level['photo'] }}" alt="Level Badge" class="w-36 h-36 object-contain shadow-lg rounded-full">
                                @else
                                    <div class="w-36 h-36 bg-[#0b141d] rounded-full flex items-center justify-center text-gray-500 border border-[#2a3b52] shadow-inner">
                                        @if($level['is_unlocked'])
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <p class="text-center text-blue-300 mb-4">{{ $level['achievements'] ?? 'No achievements description' }}</p>

                            <div class="flex justify-between w-full text-sm md:text-base text-blue-300 mb-2">
                                <span>Points Required:</span>
                                <span class="font-semibold text-white">{{ $level['points'] }}</span>
                            </div>

                            @if($level['is_user_level'])
                                <div class="mt-4 bg-blue-900 text-blue-200 text-sm font-semibold px-4 py-2 rounded-full shadow">
                                    Your Current Level
                                </div>
                            @elseif($level['is_next_level'])
                                <div class="mt-4 bg-yellow-900 text-yellow-200 text-sm font-semibold px-4 py-2 rounded-full shadow">
                                    Next Goal
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Connector Line -->
                    @if(!$loop->last)
                    <div class="absolute top-1/2 left-full w-24 h-1 bg-gray-700">
                        <div class="h-1 bg-gradient-to-r from-blue-500 to-indigo-500" 
                            style="width: {{ $level['is_user_level'] ? $progress.'%' : ($level['is_unlocked'] ? '100%' : '0%') }}">
                        </div>
                        <!-- Step Node -->
                        <div class="absolute -top-2 left-0 w-5 h-5 rounded-full 
                                    {{ $level['is_unlocked'] ? 'bg-blue-500' : 'bg-gray-500' }}">
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    const track = document.getElementById('levels-track');
    const cardWidth = 320 + 32; // w-80 + mx-4 (px)

    function levelUp(newIndex) {
        track.style.transform = `translateX(-${newIndex * cardWidth}px)`;
    }

    // On load: find current level index
    window.addEventListener("DOMContentLoaded", () => {
        const cards = track.querySelectorAll("[data-index]");
        let currentIndex = 0;
        cards.forEach((card) => {
            if (card.dataset.isUser === "1") {
                currentIndex = parseInt(card.dataset.index);
            }
        });
        levelUp(currentIndex);
    });
</script>

<script>
    const progress = {{ $progress }}; // 0-100
    const wavePath = document.getElementById('wave-path');
    const waveAmplitude = 4; // wave height
    const waveFrequency = 2; // number of waves
    const steps = 100; // points along x-axis

    function generateWave(progressPercent) {
        const points = [];
        for (let i = 0; i <= steps; i++) {
            const x = i;
            const theta = (i / steps) * waveFrequency * 2 * Math.PI;
            const y = 100 - (progressPercent) + waveAmplitude * Math.sin(theta); // invert y-axis
            points.push(`${x},${y}`);
        }
        return 'M' + points.join(' ') + ' L100,100 L0,100 Z';
    }

    function animateWave() {
        let current = 0;
        const interval = setInterval(() => {
            if(current >= progress) {
                clearInterval(interval);
            }
            wavePath.setAttribute('d', generateWave(current));
            current += 0.5; // animation speed
        }, 20);
    }

    animateWave();
    </script>
@endsection
