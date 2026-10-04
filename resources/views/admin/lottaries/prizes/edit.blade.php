@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Edit Prizes for Lottery</h1>
                    <p class="text-gray-600 mt-1">Update prize details for your lottery draw</p>
                </div>
                <div class="bg-indigo-100 text-indigo-800 px-4 py-2 rounded-lg">
                    <span class="font-semibold">Total Prizes:</span> {{ count($lottary->prizes) }}
                </div>
            </div>
            
            <!-- Lottery Info Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
                <h2 class="text-xl font-bold mb-2">{{ $lottary->title }}</h2>
                <p class="opacity-90">You're editing prizes for this lottery. Make sure all information is accurate before updating.</p>
            </div>
        </div>

        <form action="{{ route('admin.lottaries.prizes.update', $lottary->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 gap-6 mb-8">
                @foreach ($lottary->prizes as $i => $prize)
                <input type="hidden" name="prizes[{{ $i }}][id]" value="{{ $prize->id }}">
                
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 transition-all hover:shadow-lg">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-2">
                                {{ $i+1 }}
                            </span>
                            Prize Details
                        </h3>
                        <span class="text-sm font-medium px-3 py-1 rounded-full {{ $prize->user_id ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $prize->user_id ? 'Assigned' : 'Unassigned' }}
                        </span>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prize Name</label>
                                <input type="text" name="prizes[{{ $i }}][name]" value="{{ $prize->name }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    placeholder="e.g., First Prize" required>
                                <p class="mt-1 text-xs text-gray-500">Enter a descriptive name for this prize</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="prizes[{{ $i }}][description]" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    rows="3" placeholder="Describe the prize...">{{ $prize->description }}</textarea>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Value (৳)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">৳</span>
                                    <input type="number" name="prizes[{{ $i }}][price]" value="{{ $prize->price }}"
                                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                        placeholder="0.00" min="0" step="0.01" required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prize Number</label>
                                <input type="number" name="prizes[{{ $i }}][price_number]" value="{{ $prize->price_number }}"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    placeholder="e.g., 1" min="1" required>
                                <p class="mt-1 text-xs text-gray-500">The winning number for this prize</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-8 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <a href="{{ url()->previous() }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-md transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Update All Prizes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection