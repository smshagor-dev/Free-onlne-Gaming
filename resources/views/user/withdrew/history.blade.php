@extends('layouts.app')

@section('title', 'Withdrew History')

@section('content')

<!-- Alerts -->
@if(session('success'))
    <div class="mb-4 p-4 text-green-800 bg-green-100 rounded-lg border border-green-200">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 text-red-800 bg-red-100 rounded-lg border border-red-200">
        {{ session('error') }}
    </div>
@endif

<div class="max-w-7xl mx-auto py-4 sm:py-8 px-2 sm:px-6 lg:px-8">
    <div class="bg-gray-800 rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6 border border-gray-700">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
            <h2 class="text-xl sm:text-2xl font-bold text-white">WithDrew History</h2>
            <a href="{{ route('user.withdrew.index') }}" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500 transition-colors duration-200 text-sm font-medium text-center">
                New Withdrew
            </a>
        </div>

        @if($withdrews->isEmpty())
        <div class="bg-gray-700 rounded-lg p-4 sm:p-6 text-center">
            <p class="text-gray-400">No withdrew history found.</p>
        </div>
        @else
        <!-- Mobile View (Cards) -->
        <div class="sm:hidden space-y-3">
            @foreach($withdrews as $withdrew)
            <div class="bg-gray-750 rounded-lg border border-gray-700 overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-sm font-medium text-white mb-1">
                                #{{ $withdrew->transaction_number }}
                            </div>
                            <div class="flex items-center text-sm text-gray-300 mb-2">
                                @if($withdrew->gateway->image)
                                <div class="flex-shrink-0 h-5 w-5 mr-2">
                                    <img class="h-5 w-5 object-contain" src="{{ Storage::url($withdrew->gateway->image) }}" alt="{{ $withdrew->gateway->name }}">
                                </div>
                                @endif
                                {{ $withdrew->gateway->name }}
                            </div>
                        </div>
                        <span @class([
                            'px-2 py-1 text-xs font-semibold rounded-full',
                            'bg-green-100 text-green-800' => $withdrew->status === 'completed',
                            'bg-yellow-100 text-yellow-800' => $withdrew->status === 'pending',
                            'bg-red-100 text-red-800' => $withdrew->status === 'rejected',
                            'bg-blue-100 text-blue-800' => $withdrew->status === 'processing',
                        ])>
                            {{ ucfirst($withdrew->status) }}
                        </span>
                    </div>

                    <div class="mt-2 flex justify-between items-center">
                        <div class="text-sm text-white">
                            {{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount, 2) }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $withdrew->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <!-- Mobile Details Toggle -->
                <input type="checkbox" id="mobile-details-{{ $withdrew->id }}" class="hidden peer">
                <label for="mobile-details-{{ $withdrew->id }}" class="block p-2 bg-gray-700 text-center text-sm text-indigo-400 hover:text-indigo-300 cursor-pointer border-t border-gray-600">
                    Show Details
                </label>

                <!-- Mobile Details Content -->
                <div class="hidden peer-checked:block p-4 bg-gray-750 border-t border-gray-700">
                    <div class="space-y-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-400 mb-2">Transaction Details</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Transaction ID:</span>
                                    <span class="text-sm text-white">{{ $withdrew->transaction_number }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Amount:</span>
                                    <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Charge:</span>
                                    <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->charge, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Total:</span>
                                    <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount + $withdrew->charge, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Status:</span>
                                    <span @class([
                                        'text-sm font-medium',
                                        'text-green-400' => $withdrew->status === 'completed',
                                        'text-yellow-400' => $withdrew->status === 'pending',
                                        'text-red-400' => $withdrew->status === 'rejected',
                                        'text-blue-400' => $withdrew->status === 'processing',
                                    ])>
                                        {{ ucfirst($withdrew->status) }}
                                    </span>
                                </div>
                                @if($withdrew->feedback)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-400">Feedback:</span>
                                    <span class="text-sm text-white">{{ $withdrew->feedback }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Desktop View (Table) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Transaction</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Gateway</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @foreach($withdrews as $withdrew)
                    <tr class="hover:bg-gray-700 transition-colors duration-150">
                        <!-- Main Row -->
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-white">
                                    #{{ $withdrew->transaction_number }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($withdrew->gateway->image)
                                <div class="flex-shrink-0 h-8 w-8 mr-2">
                                    <img class="h-8 w-8 object-contain" src="{{ Storage::url($withdrew->gateway->image) }}" alt="{{ $withdrew->gateway->name }}">
                                </div>
                                @endif
                                <div class="text-sm text-gray-300">{{ $withdrew->gateway->name }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm text-white">
                                {{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount, 2) }}
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span @class([
                                'px-2 py-1 text-xs font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $withdrew->status === 'completed',
                                'bg-yellow-100 text-yellow-800' => $withdrew->status === 'pending',
                                'bg-red-100 text-red-800' => $withdrew->status === 'rejected',
                                'bg-blue-100 text-blue-800' => $withdrew->status === 'processing',
                            ])>
                                {{ ucfirst($withdrew->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-400">
                            {{ $withdrew->created_at->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <label for="details-{{ $withdrew->id }}" class="text-indigo-400 hover:text-indigo-300 mr-4 cursor-pointer">
                                Details
                            </label>
                        </td>
                    </tr>
                    <!-- Expanded Row -->
                    <tr>
                        <td colspan="6" class="p-0">
                            <input type="checkbox" id="details-{{ $withdrew->id }}" class="hidden peer">
                            <div class="hidden peer-checked:block bg-gray-750 px-6 py-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-400 mb-2">Transaction Details</h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Transaction ID:</span>
                                                <span class="text-sm text-white">{{ $withdrew->transaction_number }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Gateway:</span>
                                                <span class="text-sm text-white">{{ $withdrew->gateway->name }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Amount:</span>
                                                <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Charge:</span>
                                                <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->charge, 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Total:</span>
                                                <span class="text-sm text-white">{{ $withdrew->gateway->symbol }}{{ number_format($withdrew->amount + $withdrew->charge, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-400 mb-2">Status Information</h4>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Status:</span>
                                                <span @class([
                                                    'text-sm font-medium',
                                                    'text-green-400' => $withdrew->status === 'completed',
                                                    'text-yellow-400' => $withdrew->status === 'pending',
                                                    'text-red-400' => $withdrew->status === 'rejected',
                                                    'text-blue-400' => $withdrew->status === 'processing',
                                                ])>
                                                    {{ ucfirst($withdrew->status) }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Initiated:</span>
                                                <span class="text-sm text-white">{{ $withdrew->created_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                            @if($withdrew->status !== 'pending')
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Updated:</span>
                                                <span class="text-sm text-white">{{ $withdrew->updated_at->format('M d, Y h:i A') }}</span>
                                            </div>
                                            @endif
                                            @if($withdrew->feedback)
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-400">Feedback:</span>
                                                <span class="text-sm text-white">{{ $withdrew->feedback }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 sm:mt-6">
            {{ $withdrews->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    /* Hide the checkbox */
    input[type="checkbox"].hidden {
        position: absolute;
        opacity: 0;
    }
    
    /* Style the details content */
    .peer-checked\:block {
        display: none;
    }
    
    input[type="checkbox"]:checked ~ .peer-checked\:block {
        display: block;
    }
    
    /* Add smooth transition */
    .peer-checked\:block {
        transition: all 0.3s ease;
    }
    
    /* Style for the details label */
    label[for^="details-"], label[for^="mobile-details-"] {
        user-select: none;
    }
    
    label[for^="details-"]:hover, label[for^="mobile-details-"]:hover {
        text-decoration: underline;
    }

    /* Pagination styling */
    .pagination {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .page-item {
        display: inline-block;
    }

    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #d1d5db;
        background-color: #374151;
        border: 1px solid #4b5563;
    }

    .page-link:hover {
        background-color: #4b5563;
    }

    .page-item.active .page-link {
        background-color: #6366f1;
        border-color: #6366f1;
        color: white;
    }

    .page-item.disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
    }
</style>
@endsection