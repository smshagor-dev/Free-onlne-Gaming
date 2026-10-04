@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4 sm:mb-0">Casino Games ({{ $games->total() }})</h1>

        <form action="{{ route('admin.casino.cache') }}" method="POST">
            @csrf
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition font-medium shadow">Import Games</button>
        </form>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow">
            {{ session('error') }}
        </div>
    @endif

    <!-- Games Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase">Photo</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700 uppercase">Category</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($games as $index => $game)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                            {{ $index + 1 + ($games->currentPage() - 1) * $games->perPage() }}
                        </td>
                        <td class="px-6 py-4">
                            @if(!empty($game['img']))
                                <img src="{{ $game['img'] }}" alt="{{ $game['name'] }}" class="h-12 w-12 object-cover rounded-md shadow-sm">
                            @else
                                <span class="text-gray-400 text-sm">No Image</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-700 font-medium">{{ $game['name'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            @if(!empty($game['categories']))
                                {{ is_array($game['categories']) ? implode(', ', $game['categories']) : $game['categories'] }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No games found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-end">
        {{ $games->links() }}
    </div>
</div>
@endsection
