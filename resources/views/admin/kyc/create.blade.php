@extends('layouts.admin')

@section('title', 'Create KYC Field')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Create New KYC Field</h2>
        
        <form action="{{ route('admin.kyc.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="input_type" class="block text-sm font-medium text-gray-700 mb-1">Input Type</label>
                <select name="input_type" id="input_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                    <option value="text" {{ old('input_type', $kycField->input_type ?? '') == 'text' ? 'selected' : '' }}>Text</option>
                    <option value="file" {{ old('input_type', $kycField->input_type ?? '') == 'file' ? 'selected' : '' }}>File Upload</option>
                    <option value="date" {{ old('input_type', $kycField->input_type ?? '') == 'date' ? 'selected' : '' }}>Date</option>
                </select>
                @error('input_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <div class="flex items-center">
                    <input type="hidden" name="is_required" value="0"> 
                    <input type="checkbox" 
                        name="is_required" 
                        id="is_required" 
                        value="1"
                        {{ old('is_required', $kycField->is_required ?? false) ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_required" class="ml-2 block text-sm text-gray-700">Required Field</label>
                </div>
                @error('is_required')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.kyc.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection