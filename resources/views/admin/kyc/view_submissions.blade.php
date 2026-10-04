@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">KYC Submissions for {{ $user->name }}</h1>
        <a href="{{ route('admin.kyc.submissions.users') }}" class="text-blue-600 hover:text-blue-800">
            &larr; Back to KYC Users
        </a>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-medium text-gray-800">User Information</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">KYC Status</p>
                    <p class="font-medium">
                        @if($user->kyc_verified)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Verified
                        </span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Pending
                        </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.kyc.update_submissions', $user->id) }}" method="POST">
        @csrf
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-medium text-gray-800">KYC Documents</h2>
            </div>

            @if($submissions->isEmpty())
            <div class="px-6 py-8 text-center">
                <p class="text-gray-500">No KYC submissions found for this user.</p>
            </div>
            @else
            <div class="divide-y divide-gray-200">
                @foreach($submissions as $submission)
                <div class="px-6 py-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-800 mb-1">
                                {{ $submission->kycField->title ?? ucfirst(str_replace('_', ' ', $submission->field_name)) }}
                            </h3>
                            
                            @php
                            $fieldType = $submission->kycField->input_type ?? 'text';
                            @endphp

                            @if(in_array($fieldType, ['text', 'date']))
                            {{-- Normal display for text/date --}}
                            <p class="text-sm text-gray-700">{{ $submission->value }}</p>
                            @elseif($submission->value)
                            {{-- File or image display (unchanged design) --}}
                            @php
                            $fileExtension = pathinfo($submission->value, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                            @endphp

                            <div class="mt-2 flex items-center space-x-4">
                                @if($isImage)
                                <div class="relative">
                                    <a href="{{ route('files.private', ['path' => $submission->value]) }}" target="_blank" class="group">
                                        <img src="{{ route('files.private', ['path' => $submission->value]) }}" alt="KYC Document" class="h-24 w-auto rounded border border-gray-200 group-hover:border-blue-300 transition">
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                            <span class="bg-black bg-opacity-50 text-white px-2 py-1 rounded text-xs">View</span>
                                        </div>
                                    </a>
                                </div>
                                @else
                                <div class="flex items-center p-3 bg-gray-100 rounded-lg">
                                    <svg class="w-8 h-8 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $submission->kycField->title ?? 'Field Title' }}
                                        </p>
                                        <p class="text-sm font-medium text-gray-700">{{ basename($submission->value) }}</p>
                                        <p class="text-xs text-gray-500">{{ strtoupper($fileExtension) }} File</p>
                                    </div>
                                </div>
                                @endif

                                <a href="{{ route('files.private', ['path' => $submission->value]) }}" target="_blank" download class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download
                                </a>
                            </div>
                            @endif
                            
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-2">Submitted: {{ $submission->created_at->format('M d, Y H:i') }}</p>
                        </div>

                        <div class="w-full md:w-64">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select name="status[{{ $submission->id }}]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending" {{ $submission->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $submission->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $submission->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Comments</label>
                                <textarea name="comments[{{ $submission->id }}]" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('comments.'.$submission->id, $submission->comments) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                <a href="{{ route('admin.kyc.submissions.users') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update Submissions
                </button>
            </div>
            @endif
        </div>
    </form>
</div>
@php
// Check if there is at least one match in all submissions
$anyMatch = false;
foreach($submissions as $submission) {
$field = $kycFields->firstWhere('id', $submission->field_id);
if($field && in_array($field->input_type, ['text', 'date'])
&& isset($matches[$submission->id]) && $matches[$submission->id]->count()) {
$anyMatch = true;
break;
}
}
@endphp

@foreach($submissions as $submission)
@php
$field = $kycFields->firstWhere('id', $submission->field_id);
$isMatchable = $field && in_array($field->input_type, ['text', 'date']);
$hasMatches = $isMatchable && isset($matches[$submission->id]) && $matches[$submission->id]->count();
@endphp

<div class="px-6 py-4 {{ $anyMatch ? 'bg-red-50' : 'bg-green-50' }}">
    <!-- Existing submission display -->
    <p class="text-sm font-semibold text-gray-900">
        {{ $submission->kycField->title ?? 'Field Title' }}
    </p>
    <p class="text-sm font-medium text-gray-700">
        {{ $submission->value }}
    </p>

    {{-- Matching Status Section --}}
    <div class="mt-3 border-t border-gray-200 pt-3">
        @if($hasMatches)
        {{-- Only show red if matches exist --}}
        <div class="flex items-center mb-2 text-red-700">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Potential Duplicates Found: {{ $matches[$submission->id]->count() }} other user{{ $matches[$submission->id]->count() > 1 ? 's' : '' }}
        </div>
        @elseif($isMatchable)
        {{-- Only show green for matchable fields with no match --}}
        <div class="flex items-center text-sm text-green-600">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            This {{ $submission->kycField->title ?? 'Field Title' }} doesn't match any other users
        </div>
        @else
        {{-- Non-matchable fields --}}
        <div class="text-sm text-gray-500">
            Matching not performed for {{ $submission->kycField->input_type ?? 'Field Title' }} fields
        </div>
        @endif
    </div>
</div>
@endforeach

@endsection
