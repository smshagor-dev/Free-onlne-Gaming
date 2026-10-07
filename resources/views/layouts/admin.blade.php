<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $setting->title ?? 'Dashboard' }}</title>
    
     {{-- Primary Meta Tags --}}
    <meta name="seo_title" content="{{ $setting->seo_seo_title ?? $setting->seo_title ?? '' }}">
    <meta name="description" content="{{ $setting->seo_description ?? $setting->description ?? '' }}">
    <meta name="keywords" content="{{ $setting->meta_tag ?? '' }}">
    <meta name="author" content="{{ $setting->name ?? config('app.name') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

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
    
    {{-- Favicon --}}
    <link rel="icon" href="{{ $setting->favicon ? Storage::url($setting->favicon) : asset('default-favicon.ico') }}" type="image/x-icon">
    
    {{-- Styles & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Color palette */
        .bg-owasame-primary {
            background-color: #2c3e50;
        }
        .bg-owasame-secondary {
            background-color: #34495e;
        }
        .bg-owasame-accent {
            background-color: #3498db;
        }
        .text-owasame-accent {
            color: #3498db;
        }

        /* Sidebar transitions */
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .sidebar-open .sidebar {
            transform: translateX(0);
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
        }

        /* Backdrop */
        .sidebar-backdrop {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .sidebar-open .sidebar-backdrop {
            opacity: 1;
            visibility: visible;
        }

        /* Dropdown menus */
        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .submenu-open {
            max-height: 500px;
        }
        
        .dropdown-toggle::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: 0.5rem;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .dropdown-toggle.open::after {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-800">
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
    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebarBackdrop" class="sidebar-backdrop fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto bg-owasame-primary text-white shadow-xl">
        <!-- Brand Logo with Close Button -->
        <div class="flex items-center justify-between h-20 px-6 bg-owasame-secondary border-b border-gray-700">
            <div class="flex items-center">
                <i class="fas fa-shield-alt text-owasame-accent text-2xl mr-3"></i>
                <span class="text-xl font-semibold">AdminPanel</span>
            </div>
            <!-- X Button for Mobile -->
            <button id="sidebarCloseBtn" class="lg:hidden text-gray-300 hover:text-white focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- User Profile -->
        <div class="px-6 py-4 border-b border-gray-700 flex items-center space-x-4">
            <div class="relative">
            <img 
                src="{{ Auth::guard('admin')->user()->photo 
                        ? asset('storage/' . Auth::guard('admin')->user()->photo) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::guard('admin')->user()->name) . '&background=3498db&color=fff' }}" 
                alt="User" 
                class="w-10 h-10 rounded-full border-2 border-owasame-accent"
            />

                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-owasame-primary"></span>
            </div>
            <div>
                <p class="font-medium">{{ Auth::guard('admin')->user()->name }}</p>
                <p class="text-xs text-gray-400">Administrator</p>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="px-4 py-6">
            <ul class="space-y-2">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('admin.dashboard') }}" 
                       class="flex items-center px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- User Management Section -->
                <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fa fa-users mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>User</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.users.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-eye mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                View Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.kyc.submissions.users') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-id-card-alt mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                KYC Pending
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.users.banned') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-user-slash mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Banned Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.user.transactions') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-exchange-alt mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                User Transactions
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Games Management Section -->
                <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-gamepad mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>Free Games</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.games.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-dice mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Games List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.games_categories.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-th-list mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Category
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-dice-d6 mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>Casino Games</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.casino.view') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-dice mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Casino Games
                            </a>
                        </li>
                        <li>
                            <form action="{{ route('admin.casino.cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex w-full items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-sync-alt mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Load All Casino
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('admin.casino.bonus.cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex w-full items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-gift mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Load Bonus
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('admin.casino.cashback.cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex w-full items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-hand-holding-usd mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Load Cashback
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('admin.casino.vip.cache') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex w-full items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-star mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Load VIP
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>


                 <!-- Payment Management Section -->
                 <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-gift mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>Bonus</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.bonuses.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-list mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Bonus List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vipbonuses.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-star mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                VIP Bonus
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.depositsettings.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-wallet mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Deposit Setting
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.user.deposits.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-hand-holding-usd mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Cashback Setting
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bonus.search') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-paper-plane mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Send Bonus to User
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bonus.active') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-user-check mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Active User List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.bonus.expired') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-user-times mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Expired User List
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Payment Management Section -->
                <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-credit-card mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>Payment</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.deposit-settings.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-plus-circle mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Create Method
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.user.deposits.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-arrow-down mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Deposit
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.user.withdrews.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-arrow-up mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Withdrew
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Lottary Management Section -->
                <li>
                    <button class="dropdown-toggle flex items-center justify-between w-full px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <div class="flex items-center">
                            <i class="fas fa-ticket-alt mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                            <span>Lottery</span>
                        </div>
                    </button>
                    
                    <ul class="submenu mt-2 ml-4 pl-4 border-l-2 border-gray-700 space-y-2">
                        <li>
                            <a href="{{ route('admin.lottaries.index') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-plus-circle mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                Create
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.lottaries.transactions') }}" 
                            class="flex items-center px-3 py-2 text-sm rounded-lg hover:bg-owasame-secondary hover:text-white group">
                                <i class="fas fa-exchange-alt mr-2 text-xs text-gray-400 group-hover:text-owasame-accent"></i>
                                View Transactions
                            </a>
                        </li>
                    </ul>
                </li>

              <!-- Contact Messages Section -->

                <li>
                    <a href="{{ route('admin.contacts.index') }}" 
                    class="flex items-center px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <i class="fas fa-envelope mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                        <span>Contact</span>
                    </a>
                </li>

                
                <!-- Settings Section -->

                <li>
                    <a href="{{ route('admin.settings.global') }}" 
                    class="flex items-center px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <i class="fas fa-gear mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                        <span>Global Setting</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('admin.logs.index') }}" 
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <i class="fas fa-clipboard-list mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                        <span>View Logs</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.scheduler.logs.index') }}" 
                        class="flex items-center px-4 py-3 rounded-lg hover:bg-owasame-secondary hover:text-white group">
                        <i class="fas fa-history mr-3 text-gray-400 group-hover:text-owasame-accent"></i>
                        <span>View Cron Logs</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="absolute bottom-0 left-0 right-0 px-6 py-4 border-t border-gray-700 bg-owasame-secondary">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">v1.0.0</span>
                <button class="text-gray-400 hover:text-white">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="mainContent" class="flex flex-col min-h-screen lg:ml-64">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="flex items-center justify-between px-6 py-4">
                <!-- Mobile menu button -->
                <button id="menuToggleBtn" class="lg:hidden focus:outline-none text-gray-600 hover:text-owasame-accent">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Breadcrumbs -->
                <nav class="hidden md:flex items-center text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="text-owasame-accent hover:underline">Dashboard</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-600">@yield('breadcrumb', 'Home')</span>
                </nav>
                
                <!-- Header Right -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="relative p-1 text-gray-500 hover:text-owasame-accent">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Messages -->
                    <button class="relative p-1 text-gray-500 hover:text-owasame-accent">
                        <i class="fas fa-envelope text-xl"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full"></span>
                    </button>
                    
                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="userDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
                        <img 
                            src="{{ Auth::guard('admin')->user()->photo 
                                    ? asset('storage/' . Auth::guard('admin')->user()->photo) 
                                    : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::guard('admin')->user()->name) . '&background=3498db&color=fff' }}" 
                            alt="User" 
                            class="w-10 h-10 rounded-full border-2 border-owasame-accent"
                        />
                            <span class="hidden md:inline font-medium">{{ Auth::guard('admin')->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-user mr-2 text-owasame-accent"></i> Profile
                            </a>
                            <a href="{{ route('admin.editProfile') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2 text-owasame-accent"></i> Edit Profile
                            </a>
                            <a href="{{ route('admin.editPassword') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2 text-owasame-accent"></i> Change Password
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <a href="{{ route('admin.logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2 text-owasame-accent"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <!-- Page Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                    <p class="text-gray-600 mt-1">@yield('subtitle', 'Overview and statistics')</p>
                </div>
                <div class="flex items-center space-x-2">
                    @yield('header-actions')
                </div>
            </div>
            
            <!-- Content Container -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 px-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-600 text-sm">© 2025 AdminPanel. All rights reserved.</p>
                <div class="flex items-center space-x-4 mt-2 md:mt-0">
                    <a href="#" class="text-gray-500 hover:text-owasame-accent text-sm">Privacy Policy</a>
                    <a href="#" class="text-gray-500 hover:text-owasame-accent text-sm">Terms of Service</a>
                    <a href="#" class="text-gray-500 hover:text-owasame-accent text-sm">Help Center</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const menuToggleBtn = document.getElementById('menuToggleBtn');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            
            // Toggle sidebar
            function toggleSidebar() {
                body.classList.toggle('sidebar-open');
            }
            
            // Event listeners
            menuToggleBtn.addEventListener('click', toggleSidebar);
            sidebarCloseBtn.addEventListener('click', toggleSidebar);
            sidebarBackdrop.addEventListener('click', toggleSidebar);
            
            // Close sidebar when clicking nav links (mobile only)
            document.querySelectorAll('#sidebar a').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) {
                        toggleSidebar();
                    }
                });
            });
            
            // Dropdown menus
            document.querySelectorAll('.dropdown-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    this.classList.toggle('open');
                    const submenu = this.nextElementSibling;
                    submenu.classList.toggle('submenu-open');
                });
            });
            
            // User dropdown
            const userDropdownBtn = document.getElementById('userDropdownBtn');
            const userDropdown = document.getElementById('userDropdown');
            
            userDropdownBtn.addEventListener('click', function() {
                userDropdown.classList.toggle('hidden');
            });
            
            // Close user dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userDropdownBtn.contains(event.target)) {
                    userDropdown.classList.add('hidden');
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    body.classList.remove('sidebar-open');
                }
            });
        });
    </script>
</body>
</html>
