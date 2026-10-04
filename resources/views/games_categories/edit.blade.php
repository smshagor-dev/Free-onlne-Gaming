@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Category</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.games_categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Category Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $category->title) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Category Sub Title</label>
                <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $category->subtitle) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('subtitle')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Category Image</label>
                
                @if($category->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="h-20 w-20 object-cover rounded-md">
                    <p class="mt-1 text-sm text-gray-500">Current image</p>
                </div>
                @endif
                
                <input type="file" name="image" id="image"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Upload a new image to replace the current one (JPG, JPEG, WEBP or PNG max 2MB).</p>
            </div>
            
            <div class="mb-4">
                <label for="home_image" class="block text-sm font-medium text-gray-700 mb-2">Home Image</label>
                
                @if($category->home_image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $category->home_image) }}" alt="{{ $category->name }}" class="h-20 w-20 object-cover rounded-md">
                    <p class="mt-1 text-sm text-gray-500">Current Image</p>
                </div>
                @endif
                
                <input type="file" name="home_image" id="home_image"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('home_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Upload a new image to replace the current one (JPG, JPEG, WEBP or PNG max 2MB).</p>
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('admin.games_categories.index') }}" class="mr-4 text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection