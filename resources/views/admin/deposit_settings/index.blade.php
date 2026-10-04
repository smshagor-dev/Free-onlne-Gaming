@extends('layouts.admin')

@section('title', 'Deposit Settings')

@section('content')
<div class="p-6 bg-white shadow-md rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Deposit Settings</h2>
        <a href="{{ route('admin.depositsettings.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create New
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
                <tr>
                    <th class="py-3 px-4 text-left">Photo</th>
                    <th class="py-3 px-4 text-left">Title</th>
                    <th class="py-3 px-4 text-left">Bonus Type</th>
                    <th class="py-3 px-4 text-left">Wager</th>
                    <th class="py-3 px-4 text-left">Bonus Persentage</th>
                    <th class="py-3 px-4 text-left">Minimum Deposit</th>
                    <th class="py-3 px-4 text-left">Bonus Time</th>
                    <th class="py-3 px-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                @forelse($settings as $setting)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        @if($setting->photo)
                            <img src="{{ asset('storage/' . $setting->photo) }}" alt="{{ $setting->title }}" class="w-16 h-16 object-cover rounded">
                        @else
                            <span class="text-gray-500">No Image</span>
                        @endif 
                    </td>
                    <td class="py-3 px-4">{{ $setting->title ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $setting->bonus_type ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $setting->wager ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $setting->bonus_percentage ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $setting->minimum_bonus ?? 'N/A' }}</td>
                    <td class="py-3 px-4">{{ $setting->bonus_time ?? 'N/A' }}</td>
                    <td class="py-3 px-4">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.depositsettings.edit', $setting) }}" class="text-blue-500 hover:text-blue-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('admin.depositsettings.destroy', $setting) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this setting?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                        No deposit settings found. <a href="{{ route('admin.depositsettings.create') }}" class="text-blue-500 hover:underline">Create one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection