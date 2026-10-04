<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Primary Title --}}
    <title>{{ $setting->name ?? 'Dashboard' }}</title>

    {{-- Primary Meta Tags --}}
    <meta name="seo_title" content="{{ $setting->seo_seo_title ?? $setting->seo_title ?? '' }}">
    <meta name="description" content="{{ $setting->seo_description ?? $setting->description ?? '' }}">
    <meta name="keywords" content="{{ $setting->meta_tag ?? '' }}">
    <meta name="author" content="{{ $setting->name ?? config('app.name') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Open Graph / Facebook --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $setting->seo_title ?? $setting->title ?? '' }}">
    <meta property="og:description" content="{{ $setting->seo_description ?? $setting->description ?? '' }}">
    <meta property="og:image" content="{{ $setting->thumbnail_image ? Storage::url($setting->thumbnail_image) : asset('default-thumbnail.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $setting->name ?? config('app.name') }}">
    
    {{-- Open Graph / For Telegram & WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $setting->seo_title ?? $setting->title ?? '' }}">
    <meta property="og:description" content="{{ $setting->seo_description ?? $setting->description ?? '' }}">
    <meta property="og:image" content="{{ $setting->thumbnail_image ? Storage::url($setting->thumbnail_image) : asset('default-thumbnail.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $setting->title ?? config('app.name') }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $setting->seo_title ?? $setting->title ?? '' }}">
    <meta name="twitter:description" content="{{ $setting->seo_description ?? $setting->description ?? '' }}">
    <meta name="twitter:image" content="{{ $setting->thumbnail_image ? Storage::url($setting->thumbnail_image) : asset('default-thumbnail.jpg') }}">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:site" content="{{ $setting->name ?? config('app.name') }}">

    {{-- User ID Meta Tag for Authenticated Users --}}
    <meta name="user-id" content="{{ auth()->id() }}">

    {{-- Favicon --}}
    <link rel="icon" href="{{ $setting->favicon ? Storage::url($setting->favicon) : asset('default-favicon.ico') }}" type="image/x-icon">

    {{-- Styles & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    @vite('resources/js/app.js')

    {{-- Google Adsense --}}
    @if(!empty($setting->adsense_code))
    {!! $setting->adsense_code !!}
    @endif

    {{-- Google Analytics --}}
    @if(!empty($setting->google_analytics_code))
    {!! $setting->google_analytics_code !!}
    @endif
    <style>
        html,
        body {
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        .site-logo {
            max-height: 80px;
            max-width: 170px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            /* Subtle shadow */
        }

        @media (max-width: 767px) {
            #sidebar {
                width: 100vw;
                /* Full viewport width */
                left: 0;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                z-index: 50;
            }

            #sidebar {
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.5);
                transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
            }
        }
    </style>
@PwaHead
</head>

<body class="bg-[#0f1923] text-white">
    <div class="fixed top-4 right-4 space-y-2 z-50">

        @if(session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            @click="show = false"
            class="bg-green-500 text-white px-4 py-3 rounded-lg shadow flex items-center cursor-pointer transition">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 5000)"
            @click="show = false"
            class="bg-red-500 text-white px-4 py-3 rounded-lg shadow flex items-center cursor-pointer transition">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

    </div>



    <!-- Mobile overlay (hidden by default) -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-70 z-40 hidden md:hidden"></div>

    <!-- HEADER -->
    <header class="w-full bg-[#0b141d] border-b border-gray-800 flex items-center justify-between px-4 py-3">
        <!-- Left: Logo & Navigation -->
        <div class="flex items-center space-x-6">
            <!-- Mobile menu button -->
            <button id="mobileMenuButton" class="md:hidden text-white focus:outline-none ">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <a href="/">
                <img src="/storage/settings/qs8K7pownxvUgrtH50Qrwgt3UaE37iSgojIygRK7.png" alt="Company Logo" class="site-logo">
            </a>

            <!-- Promotion and Bonus Section -->
            <div class="flex items-center space-x-4 ml-6 hidden sm:flex">
                <!-- Daily Bonus Link -->
                <a href="{{ route('bonuses.index') }}" class="relative group">
                    <div class="flex items-center space-x-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-400 hover:to-yellow-500 text-white px-4 py-2 rounded-full shadow-lg transition-all duration-300 transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-medium">Daily Bonus</span>
                    </div>
                    <div class="absolute -top-2 -right-2 bg-red-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        FREE
                    </div>
                </a>

                <!-- Promotions Link -->
                <a href="/promotions" class="relative group">
                    <div class="flex items-center space-x-1 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-400 hover:to-pink-500 text-white px-4 py-2 rounded-full shadow-lg transition-all duration-300 transform hover:scale-105">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="font-medium">Promotions</span>
                    </div>
                    <div class="absolute -top-2 -right-2 bg-green-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        HOT
                    </div>
                </a>

                <!-- Casino Games Link -->
                <a href="/free-games" class="relative group">
                    <div class="flex items-center space-x-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 text-white px-4 py-2 rounded-full shadow-lg transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-gamepad mr-3 w-5 text-center text-white"></i>
                        <span class="font-medium">Free Games</span>
                    </div>
                    <div class="absolute -top-2 -right-2 bg-yellow-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                        FREE
                    </div>
                </a>

            </div>
        </div>
        <div class="flex items-center space-x-3">
            @auth
            <!-- Wallet Balance -->
            <a href="{{ route('user.deposit.index') }}">
                <div class="flex items-center gap-2 bg-blue-600/90 py-1 px-3 rounded-full shadow-md hidden sm:flex">
                    <!-- Wallet Icon -->
                    <i class="fas fa-money-check-alt"></i>
                    <!-- Balance -->
                    <span class="text-sm font-semibold text-white">
                    {{ $user->currency ?? '' }} {{ number_format((auth()->user()->balance ?? 0) + (auth()->user()->available_balance ?? 0), 2) }}
                    </span>
                </div>
            </a>

            <!-- Bonus Balance -->
            <a href="{{ route('my.bonus') }}">
                <div class="flex items-center gap-2 bg-emerald-600/90 py-1 px-3 rounded-full shadow-md hidden sm:flex">
                    <!-- Bonus Icon -->
                    <i class="fa fa-gift"></i>
                    <!-- Bonus Balance -->
                    <span class="text-sm font-semibold text-white">
                        {{ $user->currency ?? '' }} {{ number_format(auth()->user()->bonus_balance ?? 0, 2) }}
                    </span>
                </div>
            </a>

            <!-- Cashback Balance -->
            <a href="{{ route('user.cashback.index') }}">
                <div class="flex items-center gap-2 bg-yellow-600/90 py-1 px-3 rounded-full shadow-md hidden sm:flex">
                    <!-- Cashback Icon -->
                    <i class="fa fa-history"></i>
                    <!-- Cashback Balance -->
                    <span class="text-sm font-semibold text-white">
                        {{ $user->currency ?? '' }} {{ number_format(auth()->user()->cashback ?? 0, 2) }}
                    </span>
                </div>
            </a>

             <!-- VIP Bonus Balance -->
             <a href="{{ route('vip.bonuses.index') }}">
                <div class="flex items-center gap-2 bg-green-600/90 py-1 px-3 rounded-full shadow-md hidden sm:flex">
                    <!-- VIP Icon -->
                    <i class="fas fa-star"></i>
                    <!-- VIP Bonus Balance -->
                    <span class="text-sm font-semibold text-white">
                        {{ $user->currency ?? '' }} {{ number_format(auth()->user()->vip_bonus ?? 0, 2) }}
                    </span>
                </div>
            </a>


            <!-- Notification Bell with Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <!-- Notification Button -->
                <button @click="open = !open" class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 py-1 px-3 rounded-full shadow-md text-white">
                    <i class='far fa-bell'></i>
                    @auth
                    @php
                    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                    ->where('is_read', 0)
                    ->count();
                    @endphp

                    @if($unreadCount > 0)
                    <span class="text-sm font-semibold text-white">
                        {{ $unreadCount }}
                    </span>
                    @endif
                    @endauth
                </button>

                <!-- Red dot indicator -->
                @auth
                @if($unreadCount > 0)
                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-yellow-500"></span>
                @endif

                @endauth

                <!-- Notification Dropdown -->
                <div
                    x-show="open"
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-72 bg-slate-800 border border-slate-700 rounded-lg shadow-lg z-50 overflow-hidden"
                    style="display: none">
                    <div class="px-4 py-2 border-b border-slate-700 bg-slate-900 flex justify-between items-center">
                        <h3 class="font-semibold text-white">Notifications</h3>
                        <a href="{{ route('user.notifications.index') }}" class="text-xs text-gray-400 hover:text-gray-300">View all</a>
                        @auth
                        @if(auth()->user()->unreadNotifications()->count() > 0)
                        <form action="{{ route('user.notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-blue-400 hover:text-blue-300">Mark all as read</button>
                        </form>
                        @endif
                        @endauth
                    </div>



                    @php
                    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                    ->where('is_read', 0)
                    ->latest()
                    ->take(5)
                    ->get();
                    @endphp

                    @forelse($unreadNotifications as $notification)
                    <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-3 text-left border-b border-slate-700 hover:bg-slate-700 transition-colors {{ $notification->is_read ? 'bg-slate-800' : 'bg-slate-900' }}">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 pt-0.5">
                                <i class="fas fa-{{ $notification->data['icon'] ?? 'bell' }} text-{{ $notification->data['color'] ?? 'yellow' }}-500"></i>
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-white">{{ $notification->title ?? 'Notification' }}</p>
                                <p class="text-sm text-gray-400 mt-1">{{ $notification->message ?? '' }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        </button>
                    </form>
                    @empty
                    <div class="px-4 py-3 text-center text-gray-400">
                        No unread notifications
                    </div>
                    @endforelse

                </div>
            </div>

            <!-- User dropdown -->
            <div class="relative ml-2">
                <!-- Avatar Button -->
                <button class="flex items-center space-x-2 focus:outline-none" id="userMenuButton">
                    <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('default.png') }}"
                        alt="User Photo" class="w-8 h-8 rounded-full object-cover border-2 border-white">
                </button>

                <!-- Dropdown Menu (Dark Blue Theme) -->
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-[22rem] bg-slate-800 border-[5px] border-slate-700 rounded-lg shadow-xl z-50 text-white
                max-h-screen overflow-y-auto scrollbar-thin scrollbar-thumb-slate-600 scrollbar-track-slate-800">
                    <!-- User Info -->
                    <div class="px-4 py-3 border-b border-slate-700">
                        <div class="p-4 bg-[#0b141d] rounded-lg shadow-sm">
                            <!-- User info + KYC badge -->
                            <div class="flex items-center justify-between mb-3">
                                <!-- Left side: User info -->
                                <div>
                                    <p class="font-medium text-lg text-gray-200">
                                        {{ auth()->user()->username ?? 'username' }}<br>
                                        User ID: {{ auth()->user()->user_id ?? 'N/A' }}
                                    </p>
                                </div>

                                <!-- Right side: KYC badge -->
                                <div class="flex-shrink-0">
                                    @if(auth()->user()->kyc_verified ?? 0)
                                        <!-- Verified SVG Badge -->
                                        <svg class="w-10 h-10 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" 
                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 
                                                    3.066 3.066 0 013.976 0 3.066 3.066 
                                                    0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 
                                                    1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 
                                                    3.066 0 00-.723 1.745 3.066 3.066 
                                                    0 01-2.812 2.812 3.066 3.066 
                                                    0 00-1.745.723 3.066 3.066 
                                                    0 01-3.976 0 3.066 3.066 
                                                    0 00-1.745-.723 3.066 3.066 
                                                    0 01-2.812-2.812 3.066 3.066 
                                                    0 00-.723-1.745 3.066 3.066 
                                                    0 010-3.976 3.066 3.066 
                                                    0 00.723-1.745 3.066 3.066 
                                                    0 012.812-2.812zm7.44 
                                                    5.252a1 1 0 00-1.414-1.414L9 
                                                    10.586 7.707 9.293a1 1 
                                                    0 00-1.414 1.414l2 2a1 1 
                                                    0 001.414 0l4-4z" 
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <!-- Unverified SVG Badge -->
                                        <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" fill="currentColor" class="text-red-500/20"/>
                                            <path fill="currentColor" class="text-red-500" d="M15.78 8.22a.75.75 0 0 0-1.06 0L12 10.94 9.28 8.22a.75.75 0 0 0-1.06 1.06L10.94 12l-2.72 2.72a.75.75 0 1 0 1.06 1.06L12 13.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L13.06 12l2.72-2.72a.75.75 0 0 0 0-1.06z"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Points & Level badges below -->
                            <div class="flex items-center justify-between mt-2">
                                <!-- Points Badge -->
                                <a href="{{ route('user.convert.points') }}">
                                    <div class="flex items-center gap-2 bg-purple-600/90 py-1 px-3 rounded-full shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                        <span class="text-sm font-semibold text-white">
                                            {{ auth()->user()->points ?? 0 }} pts
                                        </span>
                                    </div>
                                </a>

                                <!-- Level Badge -->
                                <a href="{{ route('user.levels') }}">
                                    <div class="flex items-center gap-2 bg-purple-600/90 py-1 px-3 rounded-full shadow-md">
                                        <i class='fas fa-crown text-yellow-400 text-lg'></i>
                                        <span class="text-sm font-semibold text-white">
                                            {{ auth()->user()->level->level ?? '0' }} Level
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <!-- Mobile Balances Section -->
                        <div class="sm:hidden px-4 py-3">
                            <div class="grid grid-cols-2 gap-2">

                                <!-- Wallet -->
                                <a href="{{ route('user.deposit.index') }}">
                                    <div class="flex items-center gap-2 bg-blue-600/90 py-1 px-3 rounded-full shadow-md">
                                        <i class="fas fa-money-check-alt"></i>
                                        <span class="text-sm font-semibold text-white">
                                        {{ $user->currency ?? '' }} {{ number_format((auth()->user()->balance ?? 0) + (auth()->user()->available_balance ?? 0), 2) }}
                                        </span>
                                    </div>
                                </a>

                                <!-- Bonus Balance -->
                                 <a href="{{ route('my.bonus') }}">
                                    <div class="flex items-center gap-2 bg-emerald-600/90 py-1 px-3 rounded-full shadow-md">
                                        <i class="fa fa-gift"></i>
                                        <span class="text-sm font-semibold text-white">
                                        {{ $user->currency ?? '' }}{{ number_format(auth()->user()->bonus_balance ?? 0, 2) }}
                                        </span>
                                    </div>
                                </a>
                                <!-- Cashback Balance -->
                                <a href="{{ route('user.cashback.index') }}">
                                    <div class="flex items-center gap-2 bg-yellow-600/90 py-1 px-3 rounded-full shadow-md">
                                        <i class="fa fa-history"></i>
                                        <span class="text-sm font-semibold text-white">
                                        {{ $user->currency ?? '' }}{{ number_format(auth()->user()->cashback ?? 0, 2) }}
                                        </span>
                                    </div>
                                </a>
                                <a href="{{ route('vip.bonuses.index') }}">
                                    <div class="flex items-center gap-2 bg-yellow-600/90 py-1 px-3 rounded-full shadow-md">
                                        <i class="fas fa-star"></i>
                                        <span class="text-sm font-semibold text-white">
                                        {{ $user->currency ?? '' }}{{ number_format(auth()->user()->vip_bonus ?? 0, 2) }}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 p-4 bg-slate-800 rounded-lg max-w-3xl mx-auto">
                        <!-- User Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-400 px-2 mb-2">ACCOUNT</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-tachometer-alt mr-3 w-5 text-center text-blue-400"></i>
                                        <a href="{{ route('user.dashboard') }}" class="font-medium">Dashboard</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-user mr-3 w-5 text-center text-green-400"></i>
                                        <a href="{{ route('user.profile') }}" class="font-medium">My Profile</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-edit mr-3 w-5 text-center text-purple-400"></i>
                                        <a href="{{ route('user.profile.edit') }}" class="font-medium">Edit Profile</a>
                                    </div>
                                </div>
                                @php
                                $kycStatus = auth()->user()->kyc_verified ?? 0;

                                // Set colors based on status
                                $bgColor = $kycStatus ? 'bg-green-700 hover:bg-green-600' : 'bg-red-700 hover:bg-red-600';
                                $iconColor = $kycStatus ? 'text-green-400' : 'text-red-400';
                                $statusText = $kycStatus ? 'Verified' : 'Unverified';
                                @endphp

                                <div class="{{ $bgColor }} p-4 rounded-lg transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-id-card mr-3 w-5 text-center {{ $iconColor }}"></i>
                                            <a href="{{ route('user.kyc.index') }}" class="font-medium">KYC Status</a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-400 px-2 mb-2">TRANSACTIONS</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave mr-3 w-5 text-center text-emerald-400"></i>
                                        <a href="{{ route('user.deposit.index')}}" class="font-medium">Deposit</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-history mr-3 w-5 text-center text-amber-400"></i>
                                        <a href="{{ route('user.deposit.history')}}" class="font-medium">History</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-wallet mr-3 w-5 text-center text-red-400"></i>
                                        <a href="{{ route('user.withdrew.index')}}" class="font-medium">Withdraw</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock-rotate-left mr-3 w-5 text-center text-cyan-400"></i>
                                        <a href="{{ route('user.withdrew.history')}}" class="font-medium">History</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Game Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-400 px-2 mb-2">GAME</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-crown mr-3 w-5 text-center text-fuchsia-400"></i>
                                        <a href="{{ route('user.levels')}}" class="font-medium">My Level</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-trophy mr-3 w-5 text-center text-yellow-400"></i>
                                        <a href="{{ route('user.achievement')}}" class="font-medium">Achieve</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Referral Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-slate-400 px-2 mb-2">Referral</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-coins mr-3 w-5 text-center text-fuchsia-400"></i>
                                        <a href="{{route('user.referral.index')}}" class="font-medium">Earn</a>
                                    </div>
                                </div>
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-users mr-3 w-5 text-center text-yellow-400"></i>
                                        <a href="{{route('user.referral.my-referral')}}" class="font-medium">Referral</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2FA Section -->
                        <div class="mt-6">
                            <h3 class="text-sm font-semibold text-slate-400 px-2 mb-2">2FA Security</h3>
                            <div class="grid grid-cols-2 gap-4">

                                @if(auth()->user()->google2fa_status)
                                <!-- Show Disable Button if enabled -->
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-unlock-alt mr-3 w-5 text-center text-red-400"></i>
                                        <form method="POST" action="{{ route('2fa.toggle') }}" class="w-full">
                                            @csrf
                                            <input type="hidden" name="action" value="disable">
                                            <button type="submit" class="font-medium text-left w-full">Disable 2FA</button>
                                        </form>
                                    </div>
                                </div>
                                @else
                                <!-- Show Enable Link if not enabled -->
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-shield-alt mr-3 w-5 text-center text-green-400"></i>
                                        <a href="{{ route('2fa.setup') }}" class="font-medium">Enable 2FA</a>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Transactions Link -->
                                <div class="bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors">
                                    <div class="flex items-center">
                                        <i class="fas fa-exchange-alt mr-3 w-5 text-center text-blue-400"></i>
                                        <a href="{{ route('user.transction') }}" class="font-medium">Transactions</a>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Logout -->
                        <div class="pb-12">
                            <div class="grid grid-cols-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-slate-700 p-4 rounded-lg hover:bg-slate-600 transition-colors text-left">
                                        <div class="flex items-center">
                                            <i class="fas fa-sign-out-alt mr-3 w-5 text-center text-red-400"></i>
                                            <span class="font-medium">Logout</span>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endauth

            @guest
            <!-- Login & Registration buttons -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('login.form') }}"
                    class="px-3 py-1.5 text-sm rounded-full bg-blue-500 text-white hover:bg-blue-600 transition-colors">
                    Login
                </a>
                <a href="{{ route('register.form') }}"
                    class="px-3 py-1.5 text-sm rounded-full border border-blue-500 text-white hover:bg-blue-500 hover:text-white transition-colors">
                    Register
                </a>
            </div>
            @endguest
        </div>
    </header>

    <!-- LAYOUT -->
    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR - Modified for mobile -->
        <aside id="sidebar" class="fixed md:relative z-50 w-[22rem] bg-[#0b141d] border-r border-gray-800 h-full overflow-y-auto p-4 pb-14 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
            <!-- Close button for mobile -->
            <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-white focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>

            <!-- Lottery Try -->
            <a href="{{ route('user.lottaries.view')}}" class="block mb-4">
                <div class="relative rounded-lg p-6 mb-6 text-center overflow-hidden group h-35">
                    <!-- Background Image -->
                    <div class="absolute inset-0 bg-cover bg-center transition duration-500 group-hover:blur-sm"
                        style="background-image: url('{{ asset('storage/lottary.png') }}');">
                    </div>

                    <!-- Overlay for readability -->
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/70 to-orange-500/70"></div>

                    <!-- Content -->
                    <div class="relative z-10 text-blue">
                        <p class="font-bold text-white drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)]">
                            Try your luck in lottery
                        </p>
                        <button class="mt-2 px-3 py-1 bg-white text-black rounded text-sm font-semibold">
                            Try now
                        </button>
                    </div>
                </div>
            </a>


            <!-- Menu -->
            <nav class="space-y-2 bg-slate-800 p-4 rounded-lg">
                <!-- Top links -->
                <a href="{{ route('games.viewIndex')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded font-medium transition-colors">
                    <i class="fas fa-home mr-3 w-5 text-center text-blue-400"></i>
                    Home
                </a>

                <!-- Mobile-only Promotion/Bonus Section -->
                <div class="block sm:hidden space-y-2 mb-2">
                    <!-- Daily Bonus Link - Mobile -->
                    <a href="/daily-bonus" class="relative flex items-center px-4 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded font-medium transition-colors">
                        <i class="fas fa-gift mr-3 w-5 text-center text-white"></i>
                        Daily Bonus
                        <span class="absolute -top-2 -right-2 bg-red-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">FREE</span>
                    </a>

                    <!-- Promotions Link - Mobile -->
                    <a href="/promotions" class="relative flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded font-medium transition-colors">
                        <i class="fas fa-star mr-3 w-5 text-center text-white"></i>
                        Promotions
                        <span class="absolute -top-2 -right-2 bg-green-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">HOT</span>
                    </a>
                    <a href="/free-games" class="relative flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded font-medium transition-colors">
                        <i class="fas fa-donate mr-3 w-5 text-center text-white"></i>
                        Free Games
                        <span class="absolute -top-2 -right-2 bg-yellow-500 text-xs text-white rounded-full h-5 w-5 flex items-center justify-center animate-pulse">FREE</span>
                    </a>
                </div>

                <a href="{{ route('games.viewNewGames')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded font-medium transition-colors">
                    <i class="fas fa-gamepad mr-3 w-5 text-center text-green-400"></i>
                    New Games
                </a>
                <a href="{{ route('games.trending')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded font-medium transition-colors">
                    <i class="fas fa-chart-line mr-3 w-5 text-center text-purple-400"></i>
                    Trending Games
                </a>
                <a href="{{ route('games.popular')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded font-medium transition-colors">
                    <i class="fas fa-fire mr-3 w-5 text-center text-red-400"></i>
                    Popular Games
                </a>

                <hr class="my-2 border-slate-700">
                <!-- User-specific links -->
                <a href="{{ route('user.games.lastplay')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                    <i class="fas fa-history mr-3 w-5 text-center text-amber-400"></i>
                    Last Played ({{ $lastPlayedCount + $casinolastPlayedCount }})
                </a>
                <a href="{{ route('user.games.viewFavorites')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                    <i class="fas fa-heart mr-3 w-5 text-center text-pink-400"></i>
                    Favourite ({{ $favoritesCount + $casinofavoritesCount }})
                </a>
                <a href="{{ route('user.games.bookmarks')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                    <i class="fas fa-bookmark mr-3 w-5 text-center text-yellow-400"></i>
                    Bookmarks ({{ $bookmarksCount + $casinobookmarksCount }})
                </a>
                <a href="{{ route('games.recommended')}}" class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                    <i class="fas fa-thumbs-up mr-3 w-5 text-center text-blue-400"></i>
                    Recommended
                </a>
                <hr class="my-2 border-slate-700">

                <a href="{{ route('casino.index') }}"
                    class="flex items-center justify-between px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                        <div class="flex items-center">
                            <i class="fab fa-xbox mr-3 w-5 text-center text-green-400"></i>
                            <span>Casino</span>
                        </div>
                    <i class="fas fa-angle-double-down text-green-400"></i>
                </a>

                <hr class="my-2 border-slate-700">

                @php
                        $casinoData = json_decode(Cache::get('casino_raw_response'), true);
                        $games = collect($casinoData['content']['gameList'] ?? []);

                        // Define custom category map
                        $categoryMap = [
                            'Slots'       => ['slots'],
                            'Live Casino' => ['live_dealers', 'roulette'],
                            'Crash Games' => ['crash_games'],
                            'Arcade'      => ['arcade'],
                            'Card'        => ['card', 'video_poker', 'table_games'],
                            'Fast Games'  => ['fast_games', 'lottery'],
                        ];

                        // Define icon map
                        $iconMap = [
                            'Slots'       => 'fa-coins',
                            'Live Casino' => 'fa-dice',
                            'Crash Games' => 'fa-rocket',
                            'Arcade'      => 'fa-gamepad',
                            'Card'        => 'fa-layer-group',
                            'Fast Games'  => 'fa-bolt',
                        ];
                    @endphp

                    @foreach($categoryMap as $displayName => $aliases)
                        @php
                            $aliasString = implode(',', $aliases);
                            $categoryGamesAll = $games->filter(fn($game) => in_array($game['categories'], $aliases));
                            $iconClass = $iconMap[$displayName] ?? 'fa-dice'; // fallback
                        @endphp

                        @if($categoryGamesAll->isNotEmpty())
                            <a href="{{ url('/casino?category='.$aliasString) }}"
                            class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                                <i class="fas {{ $iconClass }} mr-3 text-center text-green-400"></i>
                                {{ $displayName }}
                            </a>
                        @endif
                    @endforeach


                <hr class="my-2 border-slate-700">

                

                <a href="{{ route('free.games.index') }}"
                    class="flex items-center justify-between px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                        <div class="flex items-center">
                            <i class="fab fa-fantasy-flight-games w-5 text-center mr-3 text-blue-400"></i>
                            <span>Free Games</span>
                        </div>
                    <i class="fas fa-angle-double-down text-blue-400"></i>
                </a>

                <hr class="my-2 border-slate-700">

                <!-- Categories -->
                @foreach($gameCategories as $category)
                <a href="{{ route('category.view', ['id' => $category->id]) }}"
                    class="flex items-center px-4 py-2 text-white hover:bg-slate-700 rounded transition-colors">
                    <img src="{{ $category->image ? Storage::url($category->home_image) : 'https://via.placeholder.com/20?text=Cat' }}"
                        alt="{{ $category->name }}"
                        class="w-5 h-5 rounded mr-3">
                    {{ $category->name }}
                </a>
                @endforeach

                <hr class="my-2 border-slate-700">
                <!-- Legal links -->
                <div class="px-4 py-2 text-xs text-slate-400 space-y-1">
                    @foreach($pages as $page)
                    <a href="{{ route('page.show', $page->slug) }}"
                        class="block hover:text-white transition-colors {{ $currentSlug == $page->slug ? 'text-white font-bold' : '' }}">
                        {{ $page->title }}
                    </a>
                    @endforeach
                    <a href="{{ route('contacts.create') }}" class="block hover:text-white transition-colors">Contact</a>
                    <p class="mt-2">© {{ $setting->title ?? 'Laravel' }} 2025 All rights reserved.</p>
                </div>
                <div class="px-4 py-2">
                    <div class="flex space-x-4">
                        <a href="#" class="text-slate-400 hover:text-cyan-400 transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-slate-400 hover:text-blue-400 transition-colors"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-slate-400 hover:text-sky-400 transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-slate-400 hover:text-red-500 transition-colors"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="px-4 py-2">
                    <div class="flex space-x-4">
                        <p class="text-slate-400">Free Games v1.0.0</p>
                    </div>
                </div>
            </nav>
        </aside>


        <!-- MAIN CONTENT -->
        <main class="flex-grow h-screen w-full px-4 pt-4 pb-12 h-full overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const userMenuButton = document.getElementById('userMenuButton');
            const userDropdown = document.getElementById('userDropdown');

            // Ensure sidebar and overlay are hidden by default
            sidebar.classList.add('-translate-x-full');
            mobileOverlay.classList.add('hidden');

            // Toggle sidebar on mobile
            mobileMenuButton.addEventListener('click', function() {
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // prevent scrolling
            });

            // Close sidebar
            const closeSidebarFunc = () => {
                sidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }

            closeSidebar.addEventListener('click', closeSidebarFunc);
            mobileOverlay.addEventListener('click', closeSidebarFunc);

            // User dropdown toggle
            if (userMenuButton && userDropdown) {
                userDropdown.classList.add('hidden'); // start hidden
                userMenuButton.addEventListener('click', () => {
                    userDropdown.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Close sidebar when clicking a link (mobile only)
            const sidebarLinks = document.querySelectorAll('#sidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) closeSidebarFunc();
                });
            });
        });

        // Ensure sidebar stays visible on desktop and hidden overlay
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileOverlay = document.getElementById('mobileOverlay');

            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            } else {
                // For mobile, start hidden unless clicked
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>

<script src="https://js.pusher.com/8.2/pusher.min.js"></script>
<script>
    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    Echo.private('App.Models.User.{{ auth()->id() }}')
    .notification((notification) => {
        console.log(notification.message);

        if (Notification.permission === "granted") {
            new Notification("New Notification", {
                body: notification.message,
                icon: "/iogo.png", 
            });
        }

        let box = document.getElementById("notifications");
        if (box) {
            box.innerHTML = `<div class="p-2 bg-gray-100">${notification.message}</div>` + box.innerHTML;
        }
    });
</script>

@include('components.ads.display')
@include('components.ads.infeed')
@include('components.ads.inarticle')
@include('components.ads.multiplex')


@RegisterServiceWorkerScript

<amp-auto-ads type="adsense"
        data-ad-client="ca-pub-3291705762140708">
</amp-auto-ads>

<!-- Yandex.RTB -->
<script>window.yaContextCb=window.yaContextCb||[]</script>
<script src="https://yandex.ru/ads/system/context.js" async></script>

<!-- Yandex.RTB R-A-17125537-4 -->
<div id="yandex_rtb_R-A-17125537-4"></div>
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-4",
        "renderTo": "yandex_rtb_R-A-17125537-4"
    })
})
</script>
<!-- Yandex.RTB R-A-17125537-2 -->
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-2",
        "type": "fullscreen",
        "platform": "touch"
    })
})
</script>
<!-- Yandex.RTB R-A-17125537-3 -->
<script>
window.yaContextCb.push(() => {
    Ya.Context.AdvManager.render({
        "blockId": "R-A-17125537-3",
        "type": "fullscreen",
        "platform": "desktop"
    })
})
</script>
</body>

</html>
