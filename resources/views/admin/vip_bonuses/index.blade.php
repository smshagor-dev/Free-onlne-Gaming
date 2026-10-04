@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-8">VIP Bonuses</h1>

    {{-- Search User --}}
    <div class="mb-10">
        <form method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
            <input type="text" name="search" placeholder="Search by ID, username, or email"
                value="{{ request('search') }}"
                class="flex-1 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold">Search</button>
        </form>

        @if($searchedUser)
        @php
        // Check for active VIP bonus
        $activeBonus = $searchedUser->vipBonuses()
        ->whereRaw('DATE_ADD(created_at, INTERVAL playing_time HOUR) > NOW()')
        ->first();

        // Check if user has any balances (VIP, bonus_balance, cashback)
        $hasAnyBalance = ($searchedUser->vip_bonus > 0) ||
        ($searchedUser->bonus_balance > 0) ||
        ($searchedUser->cashback > 0);

        // Determine if admin can send VIP bonus
        $canSendBonus = !$activeBonus && !$hasAnyBalance;
        @endphp

        <div class="bg-white shadow-lg rounded-lg p-4 border flex justify-between items-center">
            <div>
                <a href="{{ route('admin.users.view', $searchedUser->id) }}" class="text-blue-600 hover:underline">
                    {{ $searchedUser->user_id }} - {{ $searchedUser->username }} - ({{ $searchedUser->email }})
                </a>
            </div>

            <div>
                @if($activeBonus)
                <span class="px-4 py-2 bg-gray-400 text-white rounded-md">Already has active VIP bonus</span>
                @elseif($hasAnyBalance)
                <span class="px-4 py-2 bg-gray-400 text-white rounded-md">
                    Cannot send VIP bonus (Alreddy Have Bonus)
                </span>
                @elseif($canSendBonus)
                <a href="{{ route('admin.vipbonuses.create', ['user' => $searchedUser->id]) }}"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold">
                    Send VIP Bonus
                </a>
                @endif
            </div>
        </div>
        @elseif(request('search'))
        <p class="text-red-500">No user found for "{{ request('search') }}"</p>
        @endif

    </div>

    {{-- Active VIP Bonuses Table --}}
    <div>
        <table class="min-w-full bg-white shadow-lg rounded-lg overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bonus Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Playing Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Wager</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires In</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bonuses as $bonus)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $bonus->user->username }}</td>
                    <td class="px-6 py-4">{{ $bonus->user->email }}</td>
                    <td class="px-6 py-4">{{ number_format($bonus->bonus_amount, 2) }}</td>
                    <td class="px-6 py-4">{{ $bonus->playing_time }} Hours</td>
                    <td class="px-6 py-4">{{ $bonus->wager }}x</td>
                    <td class="px-6 py-4">{{ $bonus->created_at->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4">
                        @php
                        $expires = $bonus->created_at->copy()->addHours($bonus->playing_time);
                        $diff = now()->diff($expires);
                        @endphp
                        {{ $diff->format('%h hr %i min') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No active bonuses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-6 flex justify-end">
            {{ $bonuses->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection