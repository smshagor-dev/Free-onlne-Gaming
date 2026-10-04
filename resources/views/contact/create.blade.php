@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-[#0b141d] rounded-lg shadow-md mt-10">
    <h1 class="text-2xl font-bold text-white-800 mb-6">Contact Us</h1>
    
    <form action="{{ route('contacts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="mb-4">
            <label for="name" class="block text-white-700 font-medium mb-2">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" 
                   class="w-full px-4 py-2 border bg-[#0b141d] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="email" class="block text-white-700 font-medium mb-2">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}"
                   class="w-full px-4 py-2 border bg-[#0b141d] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="subject" class="block text-white-700 font-medium mb-2">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                   class="w-full px-4 py-2 border bg-[#0b141d] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required>
            @error('subject')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="priority" class="block text-white-700 font-medium mb-2">Priority</label>
            <select name="priority" id="priority"
                    class="w-full px-4 py-2 border bg-[#0b141d] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                <option value="">Select Priority</option>
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            </select>
            @error('priority')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-4">
            <label for="message" class="block text-white-700 font-medium mb-2">Message</label>
            <textarea name="message" id="message" rows="5"
                      class="w-full px-4 py-2 border bg-[#0b141d] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required>{{ old('message') }}</textarea>
            @error('message')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-6">
            <label for="file" class="block text-white-700 font-medium mb-2">Attachment (optional, max 10MB)</label>
            <input type="file" name="file" id="file"
                   class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('file')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <button type="submit" 
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
            Send Message
        </button>
    </form>
</div>
@endsection