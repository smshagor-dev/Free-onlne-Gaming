@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Level {{ $level->level }}</h1>
    
    <form action="{{ route('admin.levels.update', $level->id) }}" method="POST" class="bg-white shadow rounded-lg p-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700 mb-1">Level Number</label>
                <input type="number" name="level" id="level" value="{{ $level->level }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="1">
            </div>
            
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Points Required</label>
                <input type="number" name="points" id="points" value="{{ $level->points }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="0">
            </div>
            
            <div class="md:col-span-2">
                <label for="achievements" class="block text-sm font-medium text-gray-700 mb-1">Achievements</label>
                <input type="text" name="achievements" id="achievements" value="{{ $level->achievements }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Level Image</label>
                <input type="file" name="photo" id="photo" 
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*">
                
                @if($level->photo)
                    <div class="mt-4">
                        <p class="text-sm text-gray-500 mb-2">Current Image:</p>
                        <img src="{{ asset('storage/' . $level->photo) }}" alt="Level {{ $level->level }} image" class="h-32 w-32 object-cover rounded-md">
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remove_photo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Remove current image</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="flex justify-end mt-6 space-x-4">
            <a href="{{ route('admin.levels.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-md">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md">
                Update Level
            </button>
        </div>
    </form>
</div>
@endsection