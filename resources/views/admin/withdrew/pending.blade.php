@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pending Withdrews</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.user.withdrews.index') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">All Withdrews</a>
            <a href="{{ route('admin.user.withdrews.approved') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Approved</a>
            <a href="{{ route('admin.user.withdrews.rejected') }}" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Rejected</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($withdrews as $withdrew)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $withdrew->user->name }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $withdrew->gateway->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($withdrew->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($withdrew->documents->count() > 0)
                                <div class="space-y-2">
                                    @foreach($withdrew->documents as $document)
                                        <div>
                                            <span class="font-medium">{{ $document->requiredDocument->name_withdraw }}:</span>
                                            @if(filter_var($document->value, FILTER_VALIDATE_URL))
                                                <a href="{{ $document->value }}" target="_blank" class="text-blue-500 hover:underline">View File</a>
                                            @elseif(str_starts_with($document->value, 'user_withdraw_docs/'))
                                                <a href="{{ route('files.private', ['path' => $document->value]) }}" target="_blank" class="text-blue-500 hover:underline">View File</a>
                                            @else
                                                {{ Str::limit($document->value, 30) }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                No documents
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $withdrew->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" data-withdrew-id="{{ $withdrew->id }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3 update-status">Update Status</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-50 sm:px-6">
            {{ $withdrews->links() }}
        </div>
    </div>
</div>

@include('admin.withdrew.model')
@endsection
