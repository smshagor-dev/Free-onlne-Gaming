@extends('layouts.admin')

@section('content')
<div class="container mx-auto">
    <h2 class="text-xl font-bold mb-4">Active Bonuses (All Users)</h2>

    <!-- Search -->
    <form method="GET" action="{{ route('admin.bonus.active') }}" class="mb-4">
        <input type="text" name="search" value="{{ $search ?? '' }}"
               placeholder="Search by username, user_id, email"
               class="border px-3 py-2 rounded w-1/3">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Search</button>
    </form>

    <!-- Table -->
    <table class="table-auto w-full border-collapse border border-gray-400">
        <thead>
            <tr class="bg-green-200">
                <th class="border px-4 py-2">User</th>
                <th class="border px-4 py-2">Bonus Type</th>
                <th class="border px-4 py-2">Bonus Amount</th>
                <th class="border px-4 py-2">Deposit Amount</th>
                <th class="border px-4 py-2">Provider</th>
                <th class="border px-4 py-2">Days</th>
                <th class="border px-4 py-2">Wager</th>
                <th class="border px-4 py-2">Bonus %</th>
                <th class="border px-4 py-2">Minimum Bonus</th>
                <th class="border px-4 py-2">Bonus Time (hr)</th>
                <th class="border px-4 py-2">Expair At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bonuses as $bonus)
                <tr>
                    <td class="border px-4 py-2">{{ $bonus->user->name ?? 'Unknown' }} <br> {{ $bonus->user->username ?? 'Unknown' }} <br> ID: {{ $bonus->user->user_id }}</td>
                    <td class="border px-4 py-2">{{ $bonus->bonus_type }}</td>
                    <td class="border px-4 py-2">{{ $bonus->bonus_amount }}</td>
                    <td class="border px-4 py-2">{{ $bonus->deposit_amount }}</td>
                    <td class="border px-4 py-2">
                        @php
                            $providers = $bonus->depositSetting->providers ?? '-';
                            echo is_array($providers) ? implode(', ', $providers) : $providers;
                        @endphp
                    </td>
                    <td class="border px-4 py-2">
                        @php
                            $days = $bonus->depositSetting->days ?? '-';
                            echo is_array($days) ? implode(', ', $days) : $days;
                        @endphp
                    </td>
                    <td class="border px-4 py-2">
                        @php
                            $wager = $bonus->depositSetting->wager ?? '-';
                            echo is_array($wager) ? implode(', ', $wager) : $wager;
                        @endphp
                    </td>
                    <td class="border px-4 py-2">{{ $bonus->depositSetting->bonus_percentage ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $bonus->depositSetting->minimum_bonus ?? '-' }}</td>
                    <td class="border px-4 py-2">{{ $bonus->depositSetting->bonus_time ?? '-' }}</td>
                    <td class="border px-4 py-2">
                        @php
                            $bonusTime = 0;
                            if (!empty($bonus->depositSetting->bonus_time)) {
                                if (is_numeric($bonus->depositSetting->bonus_time)) {
                                    $bonusTime = (int) $bonus->depositSetting->bonus_time;
                                } else {
                                    preg_match('/(\d+)/', $bonus->depositSetting->bonus_time, $matches);
                                    $bonusTime = isset($matches[1]) ? (int) $matches[1] : 0;
                                }
                            }
                            $expireAt = \Carbon\Carbon::parse($bonus->created_at)->addHours($bonusTime);
                        @endphp

                        {{ $expireAt->format('Y-m-d H:i:s') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-3">No Active Bonuses</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $bonuses->appends(['search' => $search])->links() }}
    </div>
</div>
@endsection
