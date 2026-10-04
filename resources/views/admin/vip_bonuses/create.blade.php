@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">Send VIP Bonus</h1>

    <div class="bg-white shadow-lg rounded-lg p-6 border border-gray-200">
        <h2 class="text-xl font-bold mb-4">User: {{ $user->username }} ({{ $user->email }})</h2>

        <form method="POST" action="{{ route('admin.vipbonuses.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Bonus Amount</label>
                <input type="number" name="bonus_amount" min="0" step="0.01"
                       class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Playing Time (hours)</label>
                <input type="number" name="playing_time" min="1"
                       class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1">Wager</label>
                <input type="number" name="wager" min="0" step="0.01"
                       class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold transition">
                Assign Bonus
            </button>
        </form>
    </div>
</div>
@endsection
