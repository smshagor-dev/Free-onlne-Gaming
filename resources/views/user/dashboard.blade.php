@extends('layouts.app')

@section('content')
<div class="bg-gray-900 min-h-screen text-white p-2 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 md:mb-6 gap-2 md:gap-4">
            <h1 class="text-xl md:text-2xl font-bold text-orange-500 truncate">{{ $setting->name ?? config('app.name') }}</h1>
            <div class="flex items-center w-full md:w-auto justify-between md:justify-start">
                <div class="flex items-center space-x-2 bg-gray-800 p-2 rounded-lg w-full md:w-auto">
                    <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('default.png') }}" class="w-8 h-8 md:w-10 md:h-10 rounded-full" alt="avatar">
                    <div class="min-w-0">
                        <p class="font-bold text-sm md:text-base truncate">{{ auth()->user()->name ?? auth()->user()->username ?? 'User Name' }}</p>
                        <p class="text-orange-400 text-sm md:text-base">{{ $userInfo->currency ?? '' }} {{ number_format($userInfo->balance + $userInfo->available_balance, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-2 md:gap-4 mb-4 md:mb-6">

            <!-- Total Balance -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-orange-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-wallet text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Total Balance</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($userInfo->balance + $userInfo->available_balance, 2) }}
                    </p>
                </div>
            </div>

            <!-- Available Balance -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-yellow-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-piggy-bank text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Available Balance</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($userInfo->available_balance, 2) }}
                    </p>
                </div>
            </div>

            <!-- Total Deposits -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-blue-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-money-bill-wave text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Total Deposits</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($totalDeposit, 2) }}
                    </p>
                </div>
            </div>

            <!-- Total Withdrawals -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-red-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-arrow-circle-up text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Total Withdrawals</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($totalWithdrew, 2) }}
                    </p>
                </div>
            </div>

            <!-- Total Wins -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-green-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-trophy text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Total Wins</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($totalWin, 2) }}
                    </p>
                </div>
            </div>

            <!-- Bonus Balance -->
            <div class="bg-gray-800 rounded-xl p-3 md:p-4 flex items-center">
                <div class="bg-purple-500 p-2 md:p-3 rounded-lg mr-2 md:mr-4">
                    <i class="fas fa-coins text-white text-lg md:text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-gray-400 text-xs md:text-sm truncate">Bonus Balance</p>
                    <p class="text-sm md:text-lg font-bold truncate">
                        {{ $userInfo->currency ?? '' }} {{ number_format($userInfo->bonus_balance, 2) }}
                    </p>
                </div>
            </div>

        </div>

        <!-- Referral Section -->
        <div class="bg-gray-800 rounded-xl p-4 md:p-6 mb-4 md:mb-6">
            <h3 class="text-lg md:text-xl font-semibold mb-3 text-center text-orange-400">
                Invite Friends & Earn
            </h3>

            <div class="space-y-3">

                <!-- Referral URL -->
                <div class="flex items-center justify-between bg-gray-700 rounded-lg px-3 py-2">
                    <span class="text-xs md:text-sm text-gray-300 truncate">
                        {{ url('/register?ref=' . auth()->user()->referral_code) }}
                    </span>
                    <button
                        onclick="navigator.clipboard.writeText('{{ url('/register?ref=' . auth()->user()->referral_code) }}')"
                        class="ml-2 px-2 py-1 bg-orange-500 text-white text-xs rounded hover:bg-orange-600">
                        Copy
                    </button>
                </div>

                <!-- Referral Code Only -->
                <div class="flex items-center justify-between bg-gray-700 rounded-lg px-3 py-2">
                    <span class="text-xs md:text-sm text-gray-300">
                        Reffer Code: {{ auth()->user()->referral_code }}
                    </span>
                    <button
                        onclick="navigator.clipboard.writeText('{{ auth()->user()->referral_code }}')"
                        class="ml-2 px-2 py-1 bg-orange-500 text-white text-xs rounded hover:bg-orange-600">
                        Copy
                    </button>
                </div>

            </div>
        </div>



        <!-- Content Grid -->
        <div class="bg-gray-800 rounded-xl p-4 md:p-6 mb-4 md:mb-6">
            <!-- Left Sidebar - User Profile -->
            <div class="bg-gray-800 rounded-xl p-4 md:p-6 w-72">
                <!-- Profile -->
                <div class="flex flex-col items-center">
                    <img src="{{ auth()->user()->photo ? asset('storage/' . auth()->user()->photo) : asset('default.png') }}" class="w-16 h-16 md:w-20 md:h-20 rounded-full border-2 border-orange-500 mb-3 md:mb-4" alt="avatar">
                    <h2 class="text-lg md:text-xl font-semibold truncate max-w-full text-center">{{ auth()->user()->name ?? auth()->user()->username ?? 'User Name' }}</h2>
                    <p class="text-orange-400 text-base md:text-lg font-bold">{{ $userInfo->currency ?? '' }} {{ number_format($userInfo->balance + $userInfo->available_balance, 2) }}</p>
                    <div class="flex flex-wrap justify-center mt-2 gap-1 md:gap-2">
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">
                            <i class="fas fa-coins text-yellow-400 mr-1"></i> {{ $userInfo->points }} pts
                        </span>
                        <span class="bg-gray-700 px-2 py-1 rounded text-xs">
                            <i class="fas fa-medal text-blue-400 mr-1"></i> Level {{ $userInfo->level_id ?: '0' }}
                        </span>
                    </div>
                </div>

                <!-- Verification Status -->
                <div class="mt-4 md:mt-6 bg-gray-700 p-3 md:p-4 rounded-lg">
                    <h3 class="font-semibold mb-2 flex items-center text-sm md:text-base">
                        <i class="fas fa-shield-alt mr-2"></i> Account Verification
                    </h3>
                    <div class="flex justify-between items-center mt-2 md:mt-3">
                        <span class="flex items-center text-xs md:text-sm">
                            <i class="fas fa-user-shield mr-2"></i> Account Status
                        </span>
                        <span class="{{ $userInfo->is_verified ? 'text-green-400' : 'text-red-400' }} text-xs md:text-sm">
                            @if($userInfo->is_verified)
                            <i class="fas fa-check-circle mr-1"></i> Verified
                            @else
                            <i class="fas fa-times-circle mr-1"></i> Not Verified
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-2 md:mt-3">
                        <span class="flex items-center text-xs md:text-sm">
                            <i class="fas fa-envelope mr-2"></i> Email
                        </span>
                        <span class="{{ auth()->user()->email_verified_at ? 'text-green-400' : 'text-red-400' }} text-xs md:text-sm">
                            <i class="fas {{ auth()->user()->email_verified_at ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ auth()->user()->email_verified_at ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>


                    <div class="flex justify-between items-center mt-2 md:mt-3">
                        <span class="flex items-center text-xs md:text-sm">
                            <i class="fas fa-id-card mr-2"></i> KYC
                        </span>
                        <span class="{{ auth()->user()->kyc_verified ? 'text-green-400' : 'text-red-400' }} text-xs md:text-sm">
                            <i class="fas {{ auth()->user()->kyc_verified ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ auth()->user()->kyc_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>

                    @if(!$userInfo->kyc_verified)
                    <a href="{{ route('user.kyc.index') }}" class="block mt-3 md:mt-4 text-center bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg transition-colors text-sm md:text-base">
                        Complete KYC Verification
                    </a>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="mt-4 md:mt-6">
                    <h3 class="font-semibold mb-2 md:mb-3 flex items-center text-sm md:text-base">
                        <i class="fas fa-bolt mr-2"></i> Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 gap-2 md:gap-3">
                        <a href="{{ route('user.deposit.index') }}" class="bg-gray-700 hover:bg-gray-600 p-2 md:p-3 rounded-lg text-center transition-colors">
                            <i class="fas fa-plus-circle text-green-400 text-lg md:text-xl mb-1"></i>
                            <p class="text-xs md:text-sm">Deposit</p>
                        </a>
                        <a href="{{ route('user.withdrew.index') }}" class="bg-gray-700 hover:bg-gray-600 p-2 md:p-3 rounded-lg text-center transition-colors">
                            <i class="fas fa-minus-circle text-red-400 text-lg md:text-xl mb-1"></i>
                            <p class="text-xs md:text-sm">Withdraw</p>
                        </a>
                        <a href="{{ route('user.lottaries.view') }}" class="bg-gray-700 hover:bg-gray-600 p-2 md:p-3 rounded-lg text-center transition-colors">
                            <i class="fas fa-ticket-alt text-yellow-400 text-lg md:text-xl mb-1"></i>
                            <p class="text-xs md:text-sm">Buy Ticket</p>
                        </a>
                        <a href="{{ route('user.profile') }}" class="bg-gray-700 hover:bg-gray-600 p-2 md:p-3 rounded-lg text-center transition-colors">
                            <i class="fas fa-user-edit text-blue-400 text-lg md:text-xl mb-1"></i>
                            <p class="text-xs md:text-sm">Profile</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Content - Tabs and Data -->
            <div class="bg-gray-800 rounded-xl p-4 md:p-6 lg:col-span-2">
                <!-- Tabs - Mobile as dropdown, desktop as tabs -->
                <div class="block md:hidden mb-4">
                    <select id="mobile-tabs" class="w-full bg-gray-700 text-white p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="transactions-tab">Transactions</option>
                        <option value="deposits-tab">Deposits</option>
                        <option value="withdrawals-tab">Withdrawals</option>
                        <option value="lottery-tab">Lottery</option>
                        <option value="casino-tab">Games</option>
                    </select>
                </div>

                <div class="hidden md:flex overflow-x-auto space-x-4 lg:space-x-6 border-b border-gray-700 pb-2 mb-4 scrollbar-hide">
                    <button id="transactions-tab" class="tab-button text-orange-500 font-semibold border-b-2 border-orange-500 pb-2 px-1">
                        <i class="fas fa-exchange-alt mr-2"></i>Transactions
                    </button>
                    <button id="deposits-tab" class="tab-button text-gray-400 hover:text-white pb-2 px-1">
                        <i class="fas fa-money-bill-wave mr-2"></i>Deposits
                    </button>
                    <button id="withdrawals-tab" class="tab-button text-gray-400 hover:text-white pb-2 px-1">
                        <i class="fas fa-wallet mr-2"></i>Withdrawals
                    </button>
                    <button id="lottery-tab" class="tab-button text-gray-400 hover:text-white pb-2 px-1">
                        <i class="fas fa-trophy mr-2"></i>Lottery
                    </button>
                    <button id="casino-tab" class="tab-button text-gray-400 hover:text-white pb-2 px-1"> <!-- Added -->
                        <i class="fas fa-dice mr-2"></i>Games
                    </button>
                </div>

                <!-- Search Box -->
                <div class="mb-4">
                    <form id="search-form" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="search-input" name="search" value="{{ request('search') }}"
                                placeholder="Search by transaction number..."
                                class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg pl-10 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm md:text-base">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors text-sm md:text-base whitespace-nowrap">
                                Search
                            </button>
                            @if(request('search'))
                            <a href="{{ request()->url() }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors text-sm md:text-base whitespace-nowrap">
                                Clear
                            </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Transactions Table -->
                <div id="transactions-content" class="tab-content">
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full text-xs md:text-sm">
                            <thead>
                                <tr class="text-gray-400 bg-gray-700">
                                    <th class="py-2 px-2 md:px-4 text-left">Transaction #</th>
                                    <th class="py-2 px-2 md:px-4 text-left ">Type</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Amount</th>
                                    <th class="py-2 px-2 md:px-4 text-left ">Status</th>
                                    <th class="py-2 px-2 md:px-4 text-left ">Date</th>
                                    <th class="py-2 px-2 md:px-4 text-left ">Action on</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition-colors">
                                    <td class="py-3 px-2 md:px-4 truncate max-w-[80px] md:max-w-none">{{ $transaction->transaction_number }}</td>
                                    <td class="py-3 px-2 md:px-4 capitalize ">
                                        <span class="bg-gray-600 px-2 py-1 rounded text-xs">{{ $transaction->transaction_type }}</span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 font-medium {{ $transaction->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $userInfo->currency ?? '' }} {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4">
                                        <span class="status-badge {{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-2 md:px-4">
                                        <div class="text-xs text-gray-400">{{ $transaction->created_at->format('M d') }}</div>
                                        <div class="status-badge {{ $transaction->status }} text-xs">{{ ucfirst($transaction->status) }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-6 md:py-8 px-4 text-center text-gray-500 text-sm md:text-base">
                                        <i class="fas fa-exchange-alt text-2xl md:text-3xl mb-2 block"></i>
                                        No transactions found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $transactions->appends(['search' => request('search')])->links('pagination::tailwind') }}
                    </div>
                </div>

                <!-- Deposits Table -->
                <div id="deposits-content" class="tab-content hidden">
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full text-xs md:text-sm">
                            <thead>
                                <tr class="text-gray-400 bg-gray-700">
                                    <th class="py-2 px-2 md:px-4 text-left">Transaction #</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Amount</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden xs:table-cell">Status</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden md:table-cell">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition-colors">
                                    <td class="py-3 px-2 md:px-4 truncate max-w-[80px] md:max-w-none">{{ $deposit->transaction_number }}</td>
                                    <td class="py-3 px-2 md:px-4 font-medium text-green-400">
                                        {{ $userInfo->currency ?? '' }} {{ number_format($deposit->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden xs:table-cell">
                                        <span class="status-badge {{ $deposit->status }}">{{ ucfirst($deposit->status) }}</span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden md:table-cell">{{ $deposit->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-2 md:px-4 table-cell md:hidden">
                                        <div class="text-xs text-gray-400">{{ $deposit->created_at->format('M d') }}</div>
                                        <div class="status-badge {{ $deposit->status }} text-xs">{{ ucfirst($deposit->status) }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 md:py-8 px-4 text-center text-gray-500 text-sm md:text-base">
                                        <i class="fas fa-money-bill-wave text-2xl md:text-3xl mb-2 block"></i>
                                        No deposits found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $deposits->appends(['search' => request('search')])->links('pagination::tailwind') }}
                    </div>
                </div>

                <!-- Withdrawals Table -->
                <div id="withdrawals-content" class="tab-content hidden">
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full text-xs md:text-sm">
                            <thead>
                                <tr class="text-gray-400 bg-gray-700">
                                    <th class="py-2 px-2 md:px-4 text-left">Transaction #</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Amount</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden xs:table-cell">Status</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden md:table-cell">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdrawal)
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition-colors">
                                    <td class="py-3 px-2 md:px-4 truncate max-w-[80px] md:max-w-none">{{ $withdrawal->transaction_number }}</td>
                                    <td class="py-3 px-2 md:px-4 font-medium text-red-400">
                                        {{ $userInfo->currency ?? '' }} {{ number_format($withdrawal->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden xs:table-cell">
                                        <span class="status-badge {{ $withdrawal->status }}">{{ ucfirst($withdrawal->status) }}</span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden md:table-cell">{{ $withdrawal->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-2 md:px-4 table-cell md:hidden">
                                        <div class="text-xs text-gray-400">{{ $withdrawal->created_at->format('M d') }}</div>
                                        <div class="status-badge {{ $withdrawal->status }} text-xs">{{ ucfirst($withdrawal->status) }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 md:py-8 px-4 text-center text-gray-500 text-sm md:text-base">
                                        <i class="fas fa-wallet text-2xl md:text-3xl mb-2 block"></i>
                                        No withdrawals found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $withdrawals->appends(['search' => request('search')])->links('pagination::tailwind') }}
                    </div>
                </div>

                <!-- Lottery Wins Table -->
                <!-- Lottery Combined Table -->
                <div id="lottery-content" class="tab-content">
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full text-xs md:text-sm">
                            <thead>
                                <tr class="text-gray-400 bg-gray-700">
                                    <th class="py-2 px-2 md:px-4 text-left">Transaction #</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Ticket #</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Amount / Prize</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden sm:table-cell">Transction Type</th>
                                    <th class="py-2 px-2 md:px-4 text-left hidden md:table-cell">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                // Merge both collections and sort by date
                                $combined = $lotteryWins->concat($lotteryTransactions)->sortByDesc('created_at');
                                @endphp

                                @forelse($combined as $item)
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition-colors">
                                    <td class="py-3 px-2 md:px-4 font-mono truncate max-w-[80px] md:max-w-none">
                                        {{ $item->transaction_number ??  $item->transaction->transaction_number ?? '-' }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4">
                                        {{ $item->ticket_number ?? '-' }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 font-medium {{ isset($item->price) ? 'text-green-400' : 'text-orange-400' }}">
                                        @if(isset($item->price))
                                        {{ $userInfo->currency ?? '' }} {{ number_format($item->price, 2) }}
                                        @else
                                        {{ $userInfo->currency ?? '' }} {{ number_format($item->amount, 2) }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden sm:table-cell">
                                        @if(isset($item->position))
                                        <span class="px-2 py-1 rounded text-xs {{ $item->position == 1 ? 'bg-yellow-500 text-black' : ($item->position == 2 ? 'bg-gray-400 text-black' : ($item->position == 3 ? 'bg-yellow-800 text-white' : 'bg-gray-600')) }}">
                                            Win {{ $item->position }}
                                        </span>
                                        @else
                                        <span class="px-2 py-1 rounded text-xs">Lottary Buy</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden md:table-cell">
                                        {{ $item->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 table-cell md:hidden">
                                        <div class="text-xs text-gray-400">{{ $item->created_at->format('M d') }}</div>
                                        @if(isset($item->position))
                                        <div class="px-2 py-1 rounded text-xs {{ $item->position == 1 ? 'bg-yellow-500 text-black' : ($item->position == 2 ? 'bg-gray-400 text-black' : ($item->position == 3 ? 'bg-yellow-800 text-white' : 'bg-gray-600')) }}">
                                            {{ $item->position }}{{ $item->position == 1 ? 'st' : ($item->position == 2 ? 'nd' : ($item->position == 3 ? 'rd' : 'th')) }}
                                        </div>
                                        @else
                                        <div class="text-xs">Lottery Buy</div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-6 md:py-8 px-4 text-center text-gray-500 text-sm md:text-base">
                                        <i class="fas fa-ticket-alt text-2xl md:text-3xl mb-2 block"></i>
                                        No lottery records found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $lotteryWins->appends(['search' => request('search')])->links('pagination::tailwind') }}
                        {{ $lotteryTransactions->appends(['search' => request('search')])->links('pagination::tailwind') }}
                    </div>
                </div>
                <!-- Casino / Games Table -->
                <div id="casino-content" class="tab-content hidden">
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full text-xs md:text-sm">
                            <thead>
                                <tr class="text-gray-400 bg-gray-700">
                                    <th class="py-2 px-2 md:px-4 text-left">Transaction #</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Bet</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Session</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Type</th>
                                    <th class="py-2 px-2 md:px-4 text-left">Status</th>
                                    <th class="py-2 px-2 md:px-4 text-left">return</th>
                                    <th class="py-2 px-2 md:px-4 text-left ">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($casinotransactions as $game)
                                <tr class="border-b border-gray-700 hover:bg-gray-700 transition-colors">
                                    <td class="py-3 px-2 md:px-4 truncate max-w-[80px] md:max-w-none">
                                        {{ $game->transaction_number }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 font-medium {{ $game->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $userInfo->currency ?? '' }} {{ number_format($game->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 capitalize">
                                        @php
                                            $casinoDetails = json_decode($game->casino_details, true);
                                            $SessionId = $casinoDetails['SessionId'] ?? 0;
                                            $colorClass = $SessionId > 0 ? 'bg-green-500 text-white' : 'bg-red-500 text-white';
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs {{ $colorClass }}">
                                            {{ $userInfo->currency ?? '' }}  {{ $SessionId }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 capitalize">
                                        <span class="bg-gray-600 px-2 py-1 rounded text-xs">{{ $game->transaction_type }}</span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 capitalize">
                                        <span class="px-2 py-1 rounded text-xs 
                                            {{ $game->trx_type === '+' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                            {{ $game->trx_type === '+' ? 'Win' : 'Lose' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 capitalize">
                                        @php
                                            $casinoDetails = json_decode($game->casino_details, true);
                                            $win = $casinoDetails['win'] ?? 0;
                                            $colorClass = $win > 0 ? 'bg-green-500 text-white' : 'bg-red-500 text-white';
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs {{ $colorClass }}">
                                            {{ $userInfo->currency ?? '' }}  {{ $win }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-2 md:px-4 hidden md:table-cell">
                                        {{ $game->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="py-3 px-2 md:px-4 table-cell md:hidden">
                                        <div class="text-xs text-gray-400">{{ $game->created_at->format('M d') }}</div>
                                        <div class="px-2 py-1 rounded text-xs">{{ $game->transaction_type }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-6 md:py-8 px-4 text-center text-gray-500 text-sm md:text-base">
                                        <i class="fas fa-dice text-2xl md:text-3xl mb-2 block"></i>
                                        No game transactions found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $casinotransactions->appends(['search' => request('search')])->links('pagination::tailwind') }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .status-badge {
            @apply px-2 py-1 rounded text-xs font-medium;
        }

        .status-badge.pending {
            @apply bg-yellow-500 text-yellow-900;
        }

        .status-badge.approved {
            @apply bg-green-500 text-green-900;
        }

        .status-badge.rejected,
        .status-badge.failed {
            @apply bg-red-500 text-red-900;
        }

        .status-badge.processing {
            @apply bg-blue-500 text-blue-900;
        }

        .tab-button {
            @apply whitespace-nowrap transition-colors duration-200 flex items-center;
        }

        /* Responsive breakpoints */
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Mobile specific styles */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-info {
                margin-top: 1rem;
                width: 100%;
            }
        }

        /* Ensure tables are scrollable on mobile */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabs = {
                'transactions-tab': 'transactions-content',
                'deposits-tab': 'deposits-content',
                'withdrawals-tab': 'withdrawals-content',
                'lottery-tab': 'lottery-content',
                'casino-tab': 'casino-content',
            };

            function switchTab(tabId) {
                // Hide all content
                for (const content of Object.values(tabs)) {
                    document.getElementById(content).classList.add('hidden');
                }

                // Remove active styles from all tabs
                for (const tab of Object.keys(tabs)) {
                    const tabElement = document.getElementById(tab);
                    if (tabElement) {
                        tabElement.classList.remove('text-orange-500', 'border-orange-500');
                        tabElement.classList.add('text-gray-400');
                    }
                }

                // Show selected content and style active tab
                document.getElementById(tabs[tabId]).classList.remove('hidden');
                const activeTabElement = document.getElementById(tabId);
                if (activeTabElement) {
                    activeTabElement.classList.remove('text-gray-400');
                    activeTabElement.classList.add('text-orange-500', 'border-orange-500');
                }

                // Update search placeholder based on active tab
                const searchInput = document.getElementById('search-input');
                if (tabId === 'transactions-tab' || tabId === 'deposits-tab' || tabId === 'withdrawals-tab') {
                    searchInput.placeholder = 'Search by transaction number...';
                } else if (tabId === 'lottery-tab') {
                    searchInput.placeholder = 'Search by ticket number...';
                } else if (tabId === 'lottery-transactions-tab') {
                    searchInput.placeholder = 'Search by ticket or transaction number...';
                } else if(tabId === 'casino-tab'){
                    searchInput.placeholder = 'Search by transaction number...';
                }


                // Update mobile dropdown value
                const mobileDropdown = document.getElementById('mobile-tabs');
                if (mobileDropdown) {
                    mobileDropdown.value = tabId;
                }
            }

            // Set up desktop tab click handlers
            for (const tabId of Object.keys(tabs)) {
                const tabElement = document.getElementById(tabId);
                if (tabElement) {
                    tabElement.addEventListener('click', function() {
                        switchTab(tabId);
                    });
                }
            }

            // Set up mobile dropdown change handler
            const mobileDropdown = document.getElementById('mobile-tabs');
            if (mobileDropdown) {
                mobileDropdown.addEventListener('change', function() {
                    switchTab(this.value);
                });
            }

            // Initialize the first tab as active
            switchTab('transactions-tab');

            // Handle form submission to maintain tab state
            const searchForm = document.getElementById('search-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    // Get the active tab
                    let activeTab = 'transactions-tab';
                    for (const [tabId, contentId] of Object.entries(tabs)) {
                        if (!document.getElementById(contentId).classList.contains('hidden')) {
                            activeTab = tabId;
                            break;
                        }
                    }

                    // Add active tab as hidden input
                    const existingInput = document.querySelector('input[name="active_tab"]');
                    if (existingInput) {
                        existingInput.value = activeTab;
                    } else {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'active_tab';
                        input.value = activeTab;
                        this.appendChild(input);
                    }
                });
            }

            // Restore active tab from server if set
            @if(request('active_tab'))
            switchTab('{{ request('
                active_tab ') }}-tab');
            @endif
        });
    </script>
    @endsection