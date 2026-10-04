@extends('layouts.app')

@section('title', 'Withdrew Funds')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Withdrew Funds</h2>
        <p class="text-white-600 dark:text-gray-400">Choose a payment method to Withdrew funds into your account.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($gateways as $gateway)
        <a href="{{ route('user.withdrew.create', $gateway) }}" class="border border-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-300 flex flex-col items-center">

            <div class="w-16 h-16 mb-4">
                <img src="{{ Storage::url($gateway->image) }}" alt="{{ $gateway->name }}" class="w-full h-full object-contain">
            </div>
            <h3 class="text-lg font-semibold text-center">{{ $gateway->name }}</h3>
        </a>
        @endforeach
    </div>
</div>
@endsection