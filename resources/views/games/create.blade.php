@extends('layouts.admin')

@section('title', 'Create New Game')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Add New Game</h1>
            <a href="{{ route('admin.games.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Back to Games
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form Container -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.games.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Game Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Game Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Enter game name" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" id="category_id" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror" required onchange="filterTags()">
                            <option value="">Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tag -->
                    <div>
                        <label for="tag_id" class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                        <select name="tag_id" id="tag_id" 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('tag_id') border-red-500 @enderror" required>
                            <option value="">Select a tag</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}"  data-category-id="{{ $tag->category_id }}" {{ old('tag_id') == $tag->id ? 'selected' : '' }}>
                                    {{ $tag->tag_name }} ({{ $tag->category->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('tag_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Game URL -->
                    <div class="md:col-span-2">
                        <label for="games_url" class="block text-sm font-medium text-gray-700 mb-1">Game URL</label>
                        <input type="url" name="games_url" id="games_url" value="{{ old('games_url') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('games_url') border-red-500 @enderror"
                               placeholder="https://example.com/game" required>
                        @error('games_url')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Image URL -->
                    <div class="md:col-span-2">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Image URL (Optional)</label>
                        <input type="url" name="image" id="image" value="{{ old('image') }}" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('image') border-red-500 @enderror"
                               placeholder="https://example.com/image.jpg">
                        @error('image')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Details -->
                    <div class="md:col-span-2">
                        <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Game Details (Optional)</label>
                        <textarea name="details" id="details" rows="4"
                                  class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('details') border-red-500 @enderror"
                                  placeholder="Enter game description and details">{{ old('details') }}</textarea>
                        @error('details')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-plus-circle mr-2"></i> Create Game
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('name').addEventListener('blur', function() {
        const name = this.value;
        if (name) {
            console.log('Slug would be generated from: ' + name);
        }
    });
</script>

<script>
// Store all tag options on page load (clone kore rakhlam)
const allTagOptions = Array.from(document.querySelectorAll('#tag_id option')).map(opt => ({
    value: opt.value,
    text: opt.text,
    categoryId: opt.dataset.categoryId || null
}));

function filterTags() {
    const categoryId = document.getElementById('category_id').value;
    const tagSelect = document.getElementById('tag_id');

    // Clear current options
    tagSelect.innerHTML = '<option value="">Select a tag</option>';

    // Filter tags by category
    const filteredTags = allTagOptions.filter(opt => {
        if (!opt.value) return false; // skip "Select a tag"
        if (!categoryId) return true; // jodi category select na kore, show all tags
        return opt.categoryId === categoryId; // sudhu oi category id er tag show
    });

    // Append filtered tags
    filteredTags.forEach(opt => {
        const option = document.createElement('option');
        option.value = opt.value;
        option.textContent = opt.text;
        option.dataset.categoryId = opt.categoryId;
        tagSelect.appendChild(option);
    });

    // Restore old selected tag if valid
    const oldTagId = "{{ old('tag_id') }}";
    if (oldTagId) {
        const selectedOption = Array.from(tagSelect.options).find(opt => opt.value === oldTagId);
        if (selectedOption) {
            selectedOption.selected = true;
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('category_id').value) {
        filterTags();
    }
});

</script>

@endsection