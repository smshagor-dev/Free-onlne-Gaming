@extends('layouts.admin')

@section('title', 'Referral Settings')

@section('content')
<div class="p-6 bg-white shadow-md rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Referral Settings</h2>
        <a href="{{ route('admin.referral.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
            Add New Setting
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered Users</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deposit</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($settings as $setting)
                    <tr>
                        <td class="py-4 px-4 text-sm text-gray-800">Level# {{ $setting->level }}</td>
                        <td class="py-4 px-4 text-sm text-gray-800">{{ $setting->register_user }}</td>
                        <td class="py-4 px-4 text-sm text-gray-800">{{ number_format($setting->total_deposit, 2) }}</td>
                        <td class="py-4 px-4 text-sm text-gray-800">{{ number_format($setting->commission, 2) }} %</td>
                        <td class="py-4 px-4 text-sm text-gray-800">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.referral.edit', $setting->id) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                <form action="{{ route('admin.referral.destroy', $setting->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this setting?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 px-4 text-center text-sm text-gray-500">
                            No referral settings found. <a href="{{ route('admin.referral.create') }}" class="text-blue-600 hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection