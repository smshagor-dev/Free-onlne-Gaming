@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-8">

    <h1 class="text-3xl font-extrabold text-gray-900">User Section</h1>

    {{-- User Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mt-6">
        {{-- Total Users --}}
        <div class="bg-white shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-gray-700">
            <div class="text-gray-700 text-3xl">
                <i class="fas fa-users"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-gray-500 font-semibold text-sm">Total Users</span>
                <span class="text-2xl font-bold text-gray-900">{{ $users_count }}</span>
            </div>
        </div>

        {{-- Email Verified --}}
        <div class="bg-green-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-green-600">
            <div class="text-green-600 text-3xl">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-green-700 font-semibold text-sm">Email Verified</span>
                <span class="text-2xl font-bold text-green-900">{{ $users->whereNotNull('email_verified_at')->count() }}</span>
            </div>
        </div>

        {{-- KYC Verified --}}
        <div class="bg-blue-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-blue-600">
            <div class="text-blue-600 text-3xl">
                <i class="fas fa-id-card"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-blue-700 font-semibold text-sm">KYC Verified</span>
                <span class="text-2xl font-bold text-blue-900">
                    {{ $users->where('kyc_verified', 1)->count() }} / {{ $users->where('kyc_verified', 0)->count() }}
                </span>
                <span class="text-xs text-gray-500 mt-1">Complete / Not Complete</span>
            </div>
        </div>

        {{-- Banned Users --}}
        <div class="bg-red-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-red-600">
            <div class="text-red-600 text-3xl">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-red-700 font-semibold text-sm">Banned Users</span>
                <span class="text-2xl font-bold text-red-900">
                    {{ $users->where('is_banned', 1)->count() }} / {{ $users->where('is_banned', 0)->count() }}
                </span>
                <span class="text-xs text-gray-500 mt-1">Banned / Active</span>
            </div>
        </div>

        {{-- Verified Users --}}
        <div class="bg-purple-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-purple-600">
            <div class="text-purple-600 text-3xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-purple-700 font-semibold text-sm">Verified Users</span>
                <span class="text-2xl font-bold text-purple-900">
                    {{ $users->where('is_verified', 1)->count() }} / {{ $users->where('is_verified', 0)->count() }}
                </span>
                <span class="text-xs text-gray-500 mt-1">Verified / Not Verified</span>
            </div>
        </div>
    </div>

    <h1 class="text-3xl font-extrabold text-gray-900">Withdrew and Deposit Section</h1>
    {{-- Financial Amount Stats --}}
    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        {{-- Total Deposit Amount --}}
        <div class="bg-indigo-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-indigo-600">
            <span class="text-indigo-700 font-semibold text-sm">Total Deposit Amount</span>
            <span class="text-2xl font-bold text-indigo-900">{{ number_format($total_deposit_amount, 2) }}</span>
        </div>

        {{-- Approved Deposit Amount --}}
        <div class="bg-green-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-green-600">
            <span class="text-green-700 font-semibold text-sm">Approved Deposit Amount</span>
            <span class="text-2xl font-bold text-green-900">{{ number_format($approved_deposit_amount, 2) }}</span>
        </div>

        {{-- Total Withdrawal Amount --}}
        <div class="bg-pink-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-pink-600">
            <span class="text-pink-700 font-semibold text-sm">Total Withdrawal Amount</span>
            <span class="text-2xl font-bold text-pink-900">{{ number_format($total_withdrew_amount, 2) }}</span>
        </div>

        {{-- Approved Withdrawal Amount --}}
        <div class="bg-green-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-green-600">
            <span class="text-green-700 font-semibold text-sm">Approved Withdrawal Amount</span>
            <span class="text-2xl font-bold text-green-900">{{ number_format($approved_withdrew_amount, 2) }}</span>
        </div>

    </div>

    {{-- Deposits Stats --}}
    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-indigo-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-indigo-600">
            <div class="text-indigo-600 text-3xl">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-indigo-700 font-semibold text-sm">Total Deposits</span>
                <span class="text-2xl font-bold text-indigo-900">{{ $user_deposits_count }}</span>
            </div>
        </div>
        <div class="bg-green-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-green-600">
            <span class="text-green-700 font-semibold text-sm">Deposits Approved</span>
            <span class="text-2xl font-bold text-green-900">{{ $deposits_approved }}</span>
        </div>
        <div class="bg-yellow-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-yellow-600">
            <span class="text-yellow-700 font-semibold text-sm">Deposits Pending</span>
            <span class="text-2xl font-bold text-yellow-900">{{ $deposits_pending }}</span>
        </div>
        <div class="bg-red-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-red-600">
            <span class="text-red-700 font-semibold text-sm">Deposits Rejected</span>
            <span class="text-2xl font-bold text-red-900">{{ $deposits_rejected }}</span>
        </div>
    </div>

    {{-- Withdrawals Stats --}}
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-pink-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-pink-600">
            <div class="text-pink-600 text-3xl">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-pink-700 font-semibold text-sm">Total Withdrawals</span>
                <span class="text-2xl font-bold text-pink-900">{{ $user_withdrew_count }}</span>
            </div>
        </div>
        <div class="bg-green-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-green-600">
            <span class="text-green-700 font-semibold text-sm">Withdrawals Approved</span>
            <span class="text-2xl font-bold text-green-900">{{ $withdrew_approved }}</span>
        </div>
        <div class="bg-yellow-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-yellow-600">
            <span class="text-yellow-700 font-semibold text-sm">Withdrawals Pending</span>
            <span class="text-2xl font-bold text-yellow-900">{{ $withdrew_pending }}</span>
        </div>
        <div class="bg-red-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-red-600">
            <span class="text-red-700 font-semibold text-sm">Withdrawals Rejected</span>
            <span class="text-2xl font-bold text-red-900">{{ $withdrew_rejected }}</span>
        </div>
    </div>
    <h1 class="text-3xl font-extrabold text-gray-900">User Transction Section</h1>
    {{-- Other Stats --}}
    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-yellow-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 flex items-center space-x-4 border-l-4 border-yellow-600">
            <div class="text-yellow-600 text-3xl">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-yellow-700 font-semibold text-sm">Total Transactions</span>
                <span class="text-2xl font-bold text-yellow-900">{{ $transactions_count }}</span>
            </div>
        </div>
        {{-- Casino Transactions --}}
        <div class="bg-purple-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-purple-600">
            <span class="text-purple-700 font-semibold text-sm">Play Casino</span>
            <span class="text-2xl font-bold text-purple-900">{{ $casino_transactions_count }}</span>
        </div>

        {{-- Game Transactions --}}
        <div class="bg-teal-50 shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 border-teal-600">
            <span class="text-teal-700 font-semibold text-sm">Point Transction</span>
            <span class="text-2xl font-bold text-teal-900">{{ $points_transactions_count }}</span>
        </div>

        <div class="bg-white shadow-md hover:shadow-xl transition-shadow rounded-lg p-6 border-l-4 
            {{ $admin_status === 'Profit' ? 'border-green-600 bg-green-50' : 'border-red-600 bg-red-50' }}">
            
            <span class="text-sm font-semibold 
                {{ $admin_status === 'Profit' ? 'text-green-700' : 'text-red-700' }}">
                {{ ucfirst($admin_status) }}
            </span>
            
            <span class="text-2xl font-bold 
                {{ $admin_status === 'Profit' ? 'text-green-900' : 'text-red-900' }}">
                {{ $admin_status === 'Profit' ? number_format($admin_profit, 2) : '-' . number_format(abs($admin_profit), 2) }}
            </span>
        </div>


    </div>

    <h1 class="text-3xl font-extrabold text-gray-900">User Login Section</h1>
    {{-- Charts --}}
    <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Part 1: Geographical Distribution --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">User Logins by Country</h2>
            <canvas id="countryChart"></canvas>
        </div>

        {{-- Part 2: Devices & Browsers --}}
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">User Logins by Device / Browser</h2>
            <canvas id="deviceChart"></canvas>
        </div>

    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- Country Chart ---
    const countryCtx = document.getElementById('countryChart').getContext('2d');
    const countryChart = new Chart(countryCtx, {
        type: 'bar',
        data: {
            labels: @json($countries -> keys()),
            datasets: [{
                label: 'Logins per Country',
                data: @json($countries -> values()),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // --- Device Chart ---
    const deviceCtx = document.getElementById('deviceChart').getContext('2d');
    const deviceChart = new Chart(deviceCtx, {
        type: 'bar',
        data: {
            labels: @json($browsers -> keys()),
            datasets: [{
                label: 'Browser Usage',
                data: @json($browsers -> values()),
                backgroundColor: 'rgba(34,197,94,0.7)',
                borderColor: 'rgba(34,197,94,1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>



@endsection