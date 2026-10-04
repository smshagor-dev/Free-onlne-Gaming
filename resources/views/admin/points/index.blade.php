@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Game Point Rules</h1>
        <a href="{{ route('admin.points.create') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md">
            Add New Rule
        </a>
    </div>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Played Games</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points Awarded</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Converted Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Get Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($records as $record)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $record->played_games }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $record->points }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $record->points_amount }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $record->get_balance }}</td>
                    <td class="px-6 py-4 whitespace-nowrap flex space-x-2">
                        <a href="{{ route('admin.points.edit', $record->id) }}" 
                           class="text-blue-500 hover:text-blue-700">
                            Edit
                        </a>
                        <form action="{{ route('admin.points.delete', $record->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="text-red-500 hover:text-red-700" 
                                    onclick="return confirm('Are you sure you want to delete this rule?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
@endsection