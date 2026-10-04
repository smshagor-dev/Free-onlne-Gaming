@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Add Prizes for Lottery</h1>
                    <p class="text-gray-600 mt-1">Create prizes for your lottery draw</p>
                </div>
                <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-lg">
                    <span class="font-semibold">Total Prizes:</span> {{ $prizeCount }}
                </div>
            </div>
            
            <!-- Lottery Info Card -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-5 text-white shadow-lg">
                <h2 class="text-xl font-bold mb-2">{{ $lottary->title }}</h2>
                <p class="opacity-90">You're adding prizes to this lottery. Make sure all information is accurate before saving.</p>
            </div>
        </div>

        <form action="{{ route('admin.lottaries.prizes.store', $lottary->id) }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-6 mb-8">
                @for ($i = 0; $i < $prizeCount; $i++)
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
                    <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-2">
                                {{ $i+1 }}
                            </span>
                            Prize Details
                        </h3>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prize Name</label>
                                <input type="text" name="prizes[{{ $i }}][name]" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    placeholder="e.g., First Prize" required>
                                <p class="mt-1 text-xs text-gray-500">Enter a descriptive name for this prize</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="prizes[{{ $i }}][description]" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    rows="3" placeholder="Describe the prize..."></textarea>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Value (৳)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-3 text-gray-500">৳</span>
                                    <input type="number" name="prizes[{{ $i }}][price]" 
                                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                        placeholder="0.00" min="0" step="0.01" required>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prize Number</label>
                                <input type="number" name="prizes[{{ $i }}][price_number]" 
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                    placeholder="e.g., 1" min="1" required>
                                <p class="mt-1 text-xs text-gray-500">The winning number for this prize</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
            
            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-8">
                <a href="{{ url()->previous() }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium shadow-md transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Save All Prizes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection