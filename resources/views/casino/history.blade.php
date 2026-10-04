@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 text-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">{{ $pageTitle }}</h1>

        <!-- History Table -->
        <div class="overflow-x-auto bg-gray-800 shadow-lg rounded-2xl">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-700 text-gray-300 uppercase text-xs tracking-wider">
                        <th class="px-6 py-3 text-left">User ID</th>
                        <th class="px-6 py-3 text-left">User Name</th>
                        <th class="px-6 py-3 text-left">Game Name</th>
                        <th class="px-6 py-3 text-left">Session ID</th>
                        <th class="px-6 py-3 text-left">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse ($data as $item)
                        <tr class="hover:bg-gray-700 transition">
                            <td class="px-6 py-3">{{ $item->user?->user_id ?? 'N/A' }}</td>
                            <td class="px-6 py-3">
                                {{ $item->user ? $item->user->username : 'N/A' }}
                            </td>
                            <td class="px-6 py-3 font-semibold">{{ $item->game_name }}</td>
                            <td class="px-6 py-3">{{ $item->session_id }}</td>
                            <td class="px-6 py-3">{{ $item->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-400">
                                No bet history found in the last 9 days.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $data->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
