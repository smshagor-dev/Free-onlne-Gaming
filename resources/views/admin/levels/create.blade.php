@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Level</h1>
    
    <form action="{{ route('admin.levels.store') }}" method="POST" class="bg-white shadow rounded-lg p-6" enctype="multipart/form-data">
        @csrf
        
        <div id="levels-container">
            <div class="level-entry mb-6 p-4 border border-gray-200 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="levels[0][level]" class="block text-sm font-medium text-gray-700 mb-1">Level Number</label>
                        <input type="number" name="levels[0][level]" id="levels[0][level]" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="1">
                    </div>
                    
                    <div>
                        <label for="levels[0][points]" class="block text-sm font-medium text-gray-700 mb-1">Points Required</label>
                        <input type="number" name="levels[0][points]" id="levels[0][points]" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="0">
                    </div>
                    
                    <div>
                        <label for="levels[0][achievements]" class="block text-sm font-medium text-gray-700 mb-1">Achievements</label>
                        <input type="text" name="levels[0][achievements]" id="levels[0][achievements]" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="levels[0][photo]" class="block text-sm font-medium text-gray-700 mb-1">Level Image</label>
                        <input type="file" name="levels[0][photo]" id="levels[0][photo]" 
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-between mt-6">
            <button type="button" id="add-level" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Another Level
            </button>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                Save Levels
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('add-level').addEventListener('click', function() {
        const container = document.getElementById('levels-container');
        const index = container.children.length;
        
        const newLevel = document.createElement('div');
        newLevel.className = 'level-entry mb-6 p-4 border border-gray-200 rounded-lg';
        newLevel.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="levels[${index}][level]" class="block text-sm font-medium text-gray-700 mb-1">Level Number</label>
                    <input type="number" name="levels[${index}][level]" id="levels[${index}][level]" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="1">
                </div>
                
                <div>
                    <label for="levels[${index}][points]" class="block text-sm font-medium text-gray-700 mb-1">Points Required</label>
                    <input type="number" name="levels[${index}][points]" id="levels[${index}][points]" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="0">
                </div>
                
                <div>
                    <label for="levels[${index}][achievements]" class="block text-sm font-medium text-gray-700 mb-1">Achievements</label>
                    <input type="text" name="levels[${index}][achievements]" id="levels[${index}][achievements]" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="levels[${index}][photo]" class="block text-sm font-medium text-gray-700 mb-1">Level Image</label>
                    <input type="file" name="levels[${index}][photo]" id="levels[${index}][photo]" 
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                </div>
            </div>
            
            <button type="button" class="remove-level mt-2 text-red-600 hover:text-red-800 text-sm flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Remove
            </button>
        `;
        
        container.appendChild(newLevel);
        
        // Add event listener to the new remove button
        newLevel.querySelector('.remove-level').addEventListener('click', function() {
            container.removeChild(newLevel);
        });
    });
</script>
@endsection