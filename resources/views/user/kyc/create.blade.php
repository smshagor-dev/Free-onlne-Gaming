@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-white-800 mb-6">KYC Submission From</h1>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('user.kyc.store') }}" method="POST" enctype="multipart/form-data" class="bg-gray-800 shadow-md rounded-lg p-6">
            @csrf

            @foreach($fields as $field)
                <div class="mb-6">
                    <label for="kyc_{{ $field->id }}" class="block text-white-700 font-medium mb-2">
                        {{ $field->title }}
                        @if($field->description)
                            <span class="text-white-500 text-sm">({{ $field->description }})</span>
                        @endif
                        @if($field->required)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @if(isset($submissions[$field->id]) && $submissions[$field->id] === 'approved')
                        <div class="bg-gray-100 p-3 rounded">
                            <p class="text-gray-700">
                                @if($field->input_type === 'file')
                                    <a href="{{ route('files.private', ['path' => $submissions[$field->id]]) }}" target="_blank" class="text-blue-600 hover:underline">View File</a>
                                @else
                                    {{ $submissions[$field->id] }}
                                @endif
                            </p>
                            <span class="inline-block bg-green-200 text-green-800 text-xs px-2 py-1 rounded-full mt-1">Approved</span>
                        </div>
                    @elseif(isset($submissions[$field->id]) && $submissions[$field->id] === 'pending')
                        <div class="bg-gray-100 p-3 rounded">
                            <p class="text-gray-700">
                                @if($field->input_type === 'file')
                                    <a href="{{ route('files.private', ['path' => $submissions[$field->id]]) }}" target="_blank" class="text-blue-600 hover:underline">View File</a>
                                @else
                                    {{ $submissions[$field->id] }}
                                @endif
                            </p>
                            <span class="inline-block bg-yellow-200 text-yellow-800 text-xs px-2 py-1 rounded-full mt-1">Pending Review</span>
                        </div>
                    @elseif(isset($submissions[$field->id]) && $submissions[$field->id] === 'rejected')
                        <div class="bg-gray-100 p-3 rounded mb-2">
                            <p class="text-gray-700">
                                @if($field->input_type === 'file')
                                    <a href="{{ route('files.private', ['path' => $submissions[$field->id]]) }}" target="_blank" class="text-blue-600 hover:underline">View File</a>
                                @else
                                    {{ $submissions[$field->id] }}
                                @endif
                            </p>

                            @if(!empty($submission->comments))
                                <p class="text-sm text-gray-800 mt-1">
                                    <strong>Comments:</strong> {{ $submission->comments }}
                                </p>
                            @endif

                            <span class="inline-block bg-red-200 text-red-800 text-xs px-2 py-1 rounded-full mt-1">Rejected</span>
                        </div>
                        <p class="text-sm text-red-600 mb-2">Please update your submission</p>
                    @endif

                    @if(!isset($submissions[$field->id]) || $submissions[$field->id] === 'rejected')
                        @if($field->input_type === 'text')
                            <input type="text" name="kyc_{{ $field->id }}" id="kyc_{{ $field->id }}" 
                                class="w-full bg-gray-800 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kyc_'.$field->id) border-red-500 @enderror"
                                value="{{ old('kyc_'.$field->id) }}"
                                @if($field->required) required @endif>
                            
                        @elseif($field->input_type === 'file')
                            <input type="file" name="kyc_{{ $field->id }}" id="kyc_{{ $field->id }}"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kyc_'.$field->id) border-red-500 @enderror"
                                @if($field->required) required @endif>
                            <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, JPG, PNG (Max: 5MB)</p>
                            
                        @elseif($field->input_type === 'date')
                            <input type="date" name="kyc_{{ $field->id }}" id="kyc_{{ $field->id }}"
                                class="w-full bg-gray-800 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kyc_'.$field->id) border-red-500 @enderror"
                                value="{{ old('kyc_'.$field->id) }}"
                                @if($field->required) required @endif>
                                
                        @elseif($field->input_type === 'textarea')
                            <textarea name="kyc_{{ $field->id }}" id="kyc_{{ $field->id }}" rows="3"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kyc_'.$field->id) border-red-500 @enderror"
                                @if($field->required) required @endif>{{ old('kyc_'.$field->id) }}</textarea>
                        @endif

                        @error('kyc_'.$field->id)
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                    Submit KYC
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
