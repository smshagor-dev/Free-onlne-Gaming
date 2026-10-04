@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Section -->
    <div class="bg-transparent rounded-lg shadow-md overflow-hidden mb-8 border border-blue-400">
        <div class="bg-[#0b141d] px-6 py-4 text-white">
            <h2 class="text-2xl font-bold">Profile Information</h2>
        </div>
        <div class="p-6 bg-slate-800 bg-opacity-50 text-white">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Profile Photo -->
                <div class="w-full md:w-1/4 flex flex-col items-center bg-white/10 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20">
                    @if($user->photo)
                        <img src="{{ asset($user->photo ? 'storage/'.$user->photo : 'default.png') }}" 
                            alt="Profile Photo" 
                            class="w-40 h-40 rounded-full object-cover border-4 border-blue-500 shadow-md mb-4">
                    @else
                        <div class="w-40 h-40 rounded-full bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center mb-4 border-4 border-blue-500 shadow-md">
                            <span class="text-white text-6xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    <a href="{{ route('user.profile.edit') }}" 
                    class="w-full text-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg 
                            shadow-md transition duration-300 ease-in-out mb-4">
                        Edit Profile
                    </a>

                    <div class="w-full text-center text-sm text-gray-300 bg-white/5 py-2 rounded-md border border-gray-600">
                        <span class="font-semibold text-gray-100">Registration Type:</span> 
                        {{ $user->registration_type ?? 'N/A' }}
                    </div>
                </div>

                

                <!-- Profile Details -->
                <div class="w-full md:w-3/4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-blue-200 font-medium">Full Name</p>
                            <p class="text-lg font-semibold text-white">{{ $user->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Username</p>
                            <p class="text-lg font-semibold text-white">{{ $user->username ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Email Address</p>
                            <p class="text-lg font-semibold text-white">{{ $user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Mobile Number</p>
                            <p class="text-lg font-semibold text-white">{{ $user->mobile_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Location</p>
                            <p class="text-lg font-semibold text-white">
                                @if($user->user_location)
                                    {{ $user->user_location }}
                                @elseif($user->user_city && $user->user_country)
                                    {{ $user->user_city }}, {{ $user->user_country }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Points & Level</p>
                            <p class="text-lg font-semibold text-white">{{ $user->points ?? 0 }} - {{ $user->level ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Member Since</p>
                            <p class="text-lg font-semibold text-white">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-blue-200 font-medium">Date of Birth</p>
                            <p class="text-lg font-semibold text-white">{{ $user->date_of_birth?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Game Activity Section -->
    <div class="bg-transparent rounded-lg shadow-md overflow-hidden border border-blue-400 mb-8">
        <div class="bg-[#0b141d] px-6 py-4 text-white">
            <h2 class="text-2xl font-bold">Game Activity</h2>
        </div>
        <div class="p-6 bg-slate-800 bg-opacity-50">
            @if($gameOpens->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-400">
                        <thead class="bg-slate-800 bg-opacity-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Points</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Liked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Bookmarked</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Last Activity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#0b141d] bg-opacity-30 divide-y divide-blue-400">
                            @foreach($gameOpens as $game)
                                <tr class="hover:bg-blue-800 hover:bg-opacity-30 transition duration-150">
                                    <td>
                                    <img src="{{ asset($game->game->image) }}" 
                                        alt="{{ $game->game->name }}" 
                                        class="w-10 h-10 rounded mr-2">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $game->game->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $game->points ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($game->like)
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($game->bookmark)
                                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $game->updated_at->format('M d, Y H:i') }}</td>
                                    <td><a href="{{ route('games.open', ['id' => $game->id]) }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1 rounded-lg shadow-md transition">
                                            Play Again </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Game Activity Pagination -->
                <div class="mt-4">
                    {{ $gameOpens->appends(['logins_page' => $userLogins->currentPage()])->links() }}
                </div>
            @else
                <p class="text-blue-200 text-center py-4">No game activity recorded.</p>
            @endif
        </div>
    </div>

    <!-- Login History Section -->
    <div class="bg-transparent rounded-lg shadow-md overflow-hidden mb-8 border border-blue-400">
        <div class="bg-[#0b141d] px-6 py-4 text-white">
            <h2 class="text-2xl font-bold">Login History</h2>
        </div>
        <div class="p-6 bg-slate-800 bg-opacity-50">
            @if($userLogins->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-400">
                        <thead class="bg-slate-800 bg-opacity-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-blue-200 uppercase tracking-wider">Browser</th>
                            </tr>
                        </thead>
                        <tbody class="bg-slate-800 bg-opacity-30 divide-y divide-blue-400">
                            @foreach($userLogins as $login)
                                <tr class="hover:bg-blue-800 hover:bg-opacity-30 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $login->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $login->ip_address }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">
                                        @if($login->city && $login->country)
                                            {{ $login->city }}, {{ $login->country }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">
                                        @if($login->device_type && $login->device_name)
                                            {{ $login->device_type }} ({{ $login->device_name }})
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ $login->browser ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Login History Pagination -->
                <div class="mt-4">
                    {{ $userLogins->appends(['games_page' => $gameOpens->currentPage()])->links() }}
                </div>
            @else
                <p class="text-blue-200 text-center py-4">No login history available.</p>
            @endif
        </div>
    </div>
</div>
@endsection