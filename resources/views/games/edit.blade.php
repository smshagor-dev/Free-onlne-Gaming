@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Game</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.games.update', $game->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category*</label>
                    <select name="category_id" id="category_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $game->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="tag_id" class="block text-sm font-medium text-gray-700 mb-2">Tag*</label>
                    <select name="tag_id" id="tag_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ old('tag_id', $game->tag_id) == $tag->id ? 'selected' : '' }}>
                                {{ $tag->tag_name }} ({{ $tag->category->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                    @error('tag_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Game Name*</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $game->name) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="games_url" class="block text-sm font-medium text-gray-700 mb-2">Game URL*</label>
                    <input type="url" name="games_url" id="games_url" value="{{ old('games_url', $game->games_url) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('games_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image URL</label>
                <input type="url" name="image" id="image" value="{{ old('image', $game->image) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($game->image)
                <div class="mt-2">
                    <img src="{{ $game->image }}" alt="Current image" class="h-20 rounded-md">
                    <p class="text-sm text-gray-500 mt-1">Current image preview</p>
                </div>
                @endif
            </div>
            <div class="mb-4">
                <label for="details" class="block text-sm font-medium text-gray-700 mb-2">Details</label>
                <textarea name="details" id="details" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('details', $game->details) }}</textarea>
                @error('details')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end mt-6">
                <a href="{{ route('admin.games.index') }}" class="mr-4 text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-300">
                    Update Game
                </button>
            </div>
        </form>
    </div>
</div>
@endsection