@extends('layouts.app')

@section('title', 'Withdrew Deposit')

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


<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-6">Withdrew Deposit</h2>
        
        <!-- Gateway Information Card -->
        <div class="bg-gray-800 rounded-xl shadow-lg p-6 mb-6 border border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <!-- Gateway Name and Logo -->
                <div class="flex items-center space-x-4">
                    @if($gateway->image)
                    <div class="w-12 h-12 flex-shrink-0 rounded-lg bg-gray-700 p-2 flex items-center justify-center">
                        <img src="{{ Storage::url($gateway->image) }}" alt="{{ $gateway->name }}" class="w-full h-full object-contain">
                    </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-100">{{ $gateway->name }}</h3>
                        <div class="flex items-center space-x-2 text-sm text-gray-400">
                            <span>Currency:</span>
                            <span class="font-medium text-indigo-300">{{ $gateway->currency }} ({{ $gateway->symbol }})</span>
                        </div>
                    </div>
                </div>
                
                <!-- Amount Limits -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-gray-700 p-3 rounded-lg border border-gray-600">
                        <div class="text-gray-400">Min Amount</div>
                        <div class="font-medium text-white">{{ $gateway->symbol }}{{ number_format($gateway->min_amount, 2) }}</div>
                    </div>
                    <div class="bg-gray-700 p-3 rounded-lg border border-gray-600">
                        <div class="text-gray-400">Max Amount</div>
                        <div class="font-medium text-white">{{ $gateway->symbol }}{{ number_format($gateway->max_amount, 2) }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Instructions (if available) -->
            @if($gateway->instruction)
            <div class="mt-6 pt-6 border-t border-gray-700">
                <h4 class="text-sm font-medium mb-3 text-gray-300">Instructions:</h4>
                <div class="text-sm text-gray-400 prose prose-invert max-w-none">
                    {!! nl2br(e($gateway->withdraw_instruction)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Deposit Form Card -->
    <div class="bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-700">
        <form action="{{ route('user.withdrew.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <input type="hidden" name="gateway_id" value="{{ $gateway->id }}">

            <!-- Amount Input -->
            <div class="mb-6">
                <label for="amount" class="block text-sm font-medium text-gray-300 mb-2">Amount</label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-400 sm:text-sm">{{ $gateway->symbol }}</span>
                    </div>
                    <input type="number" 
                        name="amount" 
                        id="amount" 
                        step="0.01" 
                        min="{{ $gateway->min_amount }}" 
                        max="{{ $gateway->max_amount }}"
                        class="block w-full pl-7 pr-12 py-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-500" 
                        placeholder="0.00" 
                        required>

                    <div class="absolute inset-y-0 right-0 flex items-center">
                        <span class="text-gray-400 sm:text-sm pr-3">{{ $gateway->currency }}</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Minimum: {{ $gateway->symbol }}{{ number_format($gateway->min_amount, 2) }}, Maximum: {{ $gateway->symbol }}{{ number_format($gateway->max_amount, 2) }}</p>
            </div>

            <!-- Required Documents Section -->
            @if($gateway->requiredDocuments->count() > 0)
            <div class="mb-6">
                <h3 class="text-lg font-medium text-white mb-4">Required Documents</h3>
                
                <div class="space-y-5">
                    @foreach($gateway->requiredDocuments as $document)
                    <div>
                        <label for="documents[{{ $document->id }}][value]" class="block text-sm font-medium text-gray-300 mb-1">
                            {{ $document->name_withdraw }}
                            @if($document->type === 'file')
                            <span class="text-xs text-gray-500 ml-1">(Max: 5MB)</span>
                            @endif
                        </label>
                        
                        @if($document->type === 'text')
                        <input type="text" 
                            name="documents[{{ $document->id }}][value]" 
                            id="documents[{{ $document->id }}][value]" 
                            class="block w-full px-3 py-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" 
                            required>
                        @elseif($document->type === 'file')
                        <div class="flex items-center space-x-4">
                            <input type="file" 
                                name="documents[{{ $document->id }}][file]" 
                                id="documents[{{ $document->id }}][file]" 
                                class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 focus:outline-none"
                                accept="image/*,.pdf,.doc,.docx">
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200 font-medium shadow-md hover:shadow-indigo-500/20">
                    Submit Deposit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection