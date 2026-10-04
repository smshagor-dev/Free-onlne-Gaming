@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 sm:px-8">
    <div class="py-8">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Edit Lottery</h2>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <form action="{{ route('admin.lottaries.update', $lottary) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                        id="title" name="title" type="text" placeholder="Lottery Title" value="{{ old('title', $lottary->title) }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                        id="description" name="description" rows="4" placeholder="Lottery Description">{{ old('description', $lottary->description) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="photo">
                        Photo
                    </label>
                    
                    @if($lottary->photo)
                    <div class="mb-2">
                        <img src="{{ Storage::url($lottary->photo) }}" alt="Current lottery photo" class="h-32 w-32 object-cover rounded">
                    </div>
                    @endif
                    
                    <input type="file" name="photo" id="photo" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-gray-600 text-xs italic mt-1">Maximum file size: 2MB</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="price">
                            Price <span class="text-red-500">*</span>
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="price" name="price" type="number" step="0.01" min="0" placeholder="0.00" value="{{ old('price', $lottary->price) }}" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="prize_number">
                            Prize Number <span class="text-red-500">*</span>
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="prize_number" name="prize_number" type="number" min="1" placeholder="Prize Number" value="{{ old('prize_number', $lottary->prize_number) }}" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="winner_number">
                            Winner Number
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="winner_number" name="winner_number" type="number" min="1" placeholder="Winner Number" value="{{ old('winner_number', $lottary->winner_number) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="draw_date">
                            Draw Date <span class="text-red-500">*</span>
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                            id="draw_date" name="draw_date" type="date" value="{{ old('draw_date', $lottary->draw_date->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_draw" value="1" class="form-checkbox" {{ old('is_draw', $lottary->is_draw) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Mark as drawn</span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.lottaries.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Lottery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection