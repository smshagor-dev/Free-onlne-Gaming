@extends('layouts.admin')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">
        ✏️ Edit Currency for {{ $country->name }}
    </h2>

    <form method="POST" action="{{ route('admin.countries.updateCurrency', $country->id) }}">
        @csrf
        @method('PUT')

        <!-- Currency Code -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Code</label>
            <input type="text" 
                   name="currency" 
                   value="{{ $country->currency }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-primary focus:border-primary"
                   required>
        </div>

        <!-- Currency Name -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Name</label>
            <input type="text" 
                   name="currency_name" 
                   value="{{ $country->currency_name }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-primary focus:border-primary"
                   required>
        </div>

        <!-- Currency Symbol -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
            <input type="text" 
                   name="currency_symbol" 
                   value="{{ $country->currency_symbol }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-primary focus:border-primary"
                   required>
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring focus:ring-primary focus:border-primary">
                <option value="active" {{ $country->status == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $country->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3">
            <a href="{{ url()->previous() }}" 
               class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                Cancel
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-400 text-white-600 rounded-lg hover:bg-blue-600">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
