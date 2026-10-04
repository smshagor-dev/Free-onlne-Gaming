@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="container mx-auto px-4 py-8 bg-[#0b141d] min-h-screen">
    <div class="max-w-6xl mx-auto">
        @if($allApproved)
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="mb-6 p-6 bg-green-900 bg-opacity-20 rounded-full">
                    <svg class="w-20 h-20 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold text-green-500 mb-4">KYC Verified</h1>
                <p class="text-xl text-gray-300 mb-8">Your identity verification is complete and approved.</p>
                <div class="px-6 py-3 rounded-full bg-green-900 bg-opacity-30 text-green-400">
                    <span class="font-semibold">Verified on {{ $verifiedDate->format('M d, Y') }}</span>
                </div>
            </div>
        @else
            <h1 class="text-2xl font-bold text-gray-200 mb-6">My KYC Submissions</h1>

            <div class="bg-gray-800 shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Field</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Value</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Submitted At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-800 divide-y divide-gray-700">
                            @forelse($submissions as $submission)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-200">{{ $submission->kycField->title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($submission->kycField->input_type === 'file')
                                            <a href="{{ route('files.private', ['path' => $submission->value]) }}" target="_blank" class="text-blue-400 hover:underline">View File</a>
                                        @else
                                            <div class="text-sm text-gray-300">{{ Str::limit($submission->value, 50) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($submission->status === 'approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900 text-green-200">Approved</span>
                                        @elseif($submission->status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-900 text-yellow-200">Pending</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900 text-red-200">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                        {{ $submission->created_at->format('M d, Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-400">
                                        No KYC submissions found. <a href="{{ route('user.kyc.create') }}" class="text-blue-400 hover:underline">Submit your KYC now</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                @if($hasRejected || $submissions->isEmpty())
                    <a href="{{ route('user.kyc.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        @if($submissions->count())
                            Update KYC
                        @else
                            Submit KYC
                        @endif
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
