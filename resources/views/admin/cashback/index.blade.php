@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Cashback Settings</h1>
        <a href="{{ route('admin.cashback.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
            Add New
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-md shadow-sm">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">ID</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Cashback %</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Lose Calculation</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Wager</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Playing Time</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Activation Days</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Maximum Claim</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Level</th>
                    <th class="px-4 py-2 text-left text-gray-700 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($cashbacks as $cashback)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->id }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->cashback_percentage }} %</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->lose_calculation }} Days</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->wager }}x</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->playing_time }} Hours</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->activation_days }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->maximum_claim }} Times</td>
                    <td class="px-4 py-2 text-gray-700">{{ $cashback->level->level }} - {{ $cashback->level->achievements }}</td>
                    <td class="px-4 py-2 flex space-x-2">
                        <a href="{{ route('admin.cashback.edit', $cashback) }}" class="bg-yellow-400 text-white px-3 py-1 rounded-md hover:bg-yellow-500 transition-colors text-sm">Edit</a>
                        <form action="{{ route('admin.cashback.destroy', $cashback) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition-colors text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
