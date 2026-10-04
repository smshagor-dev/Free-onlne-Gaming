@extends('layouts.app')

@section('content')
<div class="bg-[#0b141d] min-h-screen py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6 text-white">Deposit Bonuses</h1>

        @if($depositSettings->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($depositSettings as $setting)
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-700 group"
                onclick="openModal('{{ $setting->id }}')">

                @if($setting->photo)
                <div class="relative overflow-hidden h-48">
                    <img src="{{ asset('storage/' . $setting->photo) }}" alt="{{ $setting->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <!-- Title overlay on top of image -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-4">
                        <h2 class="text-xl font-bold text-white line-clamp-2">{{ $setting->title }}</h2>
                    </div>
                    <!-- Bonus type badge on bottom right -->
                    <span class="absolute bottom-3 right-3 bg-blue-700/90 text-blue-100 text-xs font-semibold px-2.5 py-1 rounded-full backdrop-blur-sm">
                        {{ ucfirst($setting->bonus_type) }}
                    </span>
                </div>
                @else
                <!-- If no photo, show title and bonus type in header -->
                <div class="relative p-5 pb-3">
                    <h2 class="text-xl font-bold text-white line-clamp-2 mb-2">{{ $setting->title }}</h2>
                    <span class="absolute top-5 right-5 bg-blue-700 text-blue-100 text-xs font-semibold px-2.5 py-1 rounded-full">
                        {{ ucfirst($setting->bonus_type) }}
                    </span>
                </div>
                @endif

                <div class="p-5 pt-3">
                    <div class="text-gray-400 text-sm mb-4">
                        @if($setting->minimum_bonus)
                        <div class="flex items-center mb-2">
                            <i class="fas fa-wallet mr-2 text-green-400"></i>
                            <span>
                                @if($setting->bonus_type === 'Welcome Bonus')
                                Get Bonus: {{ number_format($setting->minimum_bonus * ($setting->bonus_percentage ?? 1), 2) }}
                                @else
                                Min Deposit: {{ number_format($setting->minimum_bonus, 2) }}
                                @endif
                            </span>
                        </div>
                        @endif


                        @if($setting->bonus_percentage)
                        <div class="flex items-center mb-2">
                            <i class="fas fa-percent mr-2 text-blue-400"></i>
                            <span>Get: {{ $setting->bonus_percentage }}% Bonus</span>
                        </div>
                        @endif

                        @if($setting->wager)
                        <div class="flex items-center mb-2">
                            <i class="fas fa-sync-alt mr-2 text-purple-400"></i>
                            <span>Wager: {{ $setting->wager }}x</span>
                        </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-700">
                        <div class="text-xs text-gray-500">
                            @if(count($setting->days) > 0)
                            <i class="far fa-calendar-alt mr-1"></i>
                            {{ implode(', ', $setting->days) }}
                            @else
                            <i class="far fa-calendar-alt mr-1"></i>
                            Available daily
                            @endif
                        </div>
                        <div class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                            Details <i class="fas fa-chevron-right text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for this deposit setting -->
            <div id="modal-{{ $setting->id }}" class="fixed inset-0 bg-gray-900 bg-opacity-90 overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative w-full max-w-4xl bg-gradient-to-b from-gray-800 to-gray-900 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                        <!-- Close button -->
                        <button onclick="closeModal('{{ $setting->id }}')" class="absolute top-4 right-4 z-10 text-gray-400 hover:text-white bg-gray-800 rounded-full p-2 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- Modal content with scrolling -->
                        <div class="max-h-[90vh] overflow-y-auto">
                            <!-- Hero image -->
                            @if($setting->photo)
                            <div class="relative h-56 md:h-72 w-full overflow-hidden">
                                <img src="{{ asset('storage/' . $setting->photo) }}" alt="{{ $setting->title }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
                                <div class="absolute bottom-4 left-4">
                                    <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $setting->title }}</h2>
                                </div>
                            </div>
                            @endif

                            <div class="p-6">
                                <!-- Header -->
                                @if(!$setting->photo)
                                <div class="mb-6">
                                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">{{ $setting->title }}</h2>
                                    <span class="bg-blue-700 text-blue-100 text-sm font-semibold px-3 py-1 rounded-full">
                                        {{ ucfirst($setting->bonus_type) }}
                                    </span>
                                </div>
                                @endif

                                <!-- Stats grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                    @if($setting->minimum_bonus)
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-400 mb-1">
                                            @if($setting->bonus_type === 'Welcome Bonus')
                                            Get Bonus
                                            @else
                                            Minimum Deposit
                                            @endif
                                        </p>
                                        <p class="text-xl font-bold text-green-400">
                                            @if($setting->bonus_type === 'Welcome Bonus' && !empty($setting->bonus_percentage))
                                            {{ number_format($setting->minimum_bonus * $setting->bonus_percentage, 2) }}
                                            @else
                                            {{ number_format($setting->minimum_bonus, 2) }}
                                            @endif
                                        </p>
                                    </div>
                                    @endif


                                    @if($setting->bonus_percentage)
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-400 mb-1">Get Bonus</p>
                                        <p class="text-xl font-bold text-green-400">{{ number_format($setting->bonus_percentage, 2) }} %</p>
                                    </div>
                                    @endif

                                    @if($setting->wager)
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-400 mb-1">Wager Requirement</p>
                                        <p class="text-xl font-semibold text-white">{{ $setting->wager }}x</p>
                                    </div>
                                    @endif

                                    @if($setting->bonus_time)
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-400 mb-1">Bonus Time</p>
                                        <p class="text-md text-white">{{ $setting->bonus_time }}</p>
                                    </div>
                                    @endif

                                    @if($setting->maximum_claim_in_a_day)
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <p class="text-sm text-gray-400 mb-1">
                                            @if($setting->bonus_type === 'Welcome Bonus')
                                            Claims In
                                            @else
                                            Max Claims Per Day
                                            @endif
                                        </p>
                                        <p class="text-md text-white">{{ $setting->maximum_claim_in_a_day }} Times</p>
                                    </div>
                                    @endif
                                </div>

                                <!-- Providers -->
                                @if(count($setting->providers) > 0)
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold text-white mb-3 border-b border-gray-700 pb-2">Available Providers</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($setting->providers as $provider)
                                        <span class="px-3 py-1 bg-gray-700 text-sm rounded-full text-gray-300">{{ $provider }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Days -->
                                @if(count($setting->days) > 0)
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold text-white mb-3 border-b border-gray-700 pb-2">Available Days</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($setting->days as $day)
                                        <span class="px-3 py-1 bg-gray-700 text-sm rounded-full text-gray-300">{{ $day }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                <!-- Action buttons -->
                                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-700">
                                    @if($setting->bonus_type === 'Welcome Bonus')
                                    <form action="{{ route('user.bonus.claim.welcome') }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold w-full text-center">
                                            Get Welcome Bonus
                                        </button>
                                    </form>
                                    @else
                                    <a href="{{ url('/user/deposit') }}"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold flex-1 text-center">
                                        Claim This Bonus
                                    </a>
                                    @endif

                                    <button onclick="closeModal('{{ $setting->id }}')"
                                        class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold flex-1">
                                        Close
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-8 max-w-md w-full border border-gray-700 shadow-lg">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-900/20 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">No Deposit Bonuses Available</h2>
                <p class="text-gray-400 mb-6">Check back later for new deposit bonuses!</p>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById('modal-' + id).classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(id) {
        document.getElementById('modal-' + id).classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id.split('-')[1]);
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('[id^="modal-"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal.id.split('-')[1]);
                }
            });
        }
    });
</script>

<style>
    .line-clamp-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }

    .line-clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }
</style>
@endsection