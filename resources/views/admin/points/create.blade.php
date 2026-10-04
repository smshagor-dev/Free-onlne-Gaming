@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-semibold mb-6">Create New Game Point Rule</h1>
        
        <form action="{{ route('admin.points.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="played_games" class="block text-gray-700 text-sm font-medium mb-2">
                    Played Games
                </label>
                <input type="number" name="played_games" id="played_games" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       min="0" required>
                @error('played_games')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="get_balance" class="block text-gray-700 text-sm font-medium mb-2">
                    Get Balance (Minimum Balance Required)
                </label>
                <input type="number" name="get_balance" id="get_balance" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       min="0" required>
                @error('get_balance')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="points" class="block text-gray-700 text-sm font-medium mb-2">
                    Converted Point to Bonus
                </label>
            </div>
            
            <div class="mb-4">
                <label for="points_amount" class="block text-gray-700 text-sm font-medium mb-2">
                    Points Amount
                </label>
                <input type="number" name="points_amount" id="points_amount" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       min="0" required>
                @error('points_amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label for="points" class="block text-gray-700 text-sm font-medium mb-2">
                    Points Awarded (Legacy Field - Consider using points_amount instead)
                </label>
                <input type="number" name="points" id="points" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       min="0">
                @error('points')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Rule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection