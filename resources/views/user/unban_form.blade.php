@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#0b141d] py-12 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-[#1e2a35] shadow-lg rounded-2xl p-8 border border-gray-700">

            {{-- If account is active and no ban documents --}}
            @if($accountActive)
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="mb-6 p-6 bg-green-900 bg-opacity-20 rounded-full">
                        <svg class="w-20 h-20 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M6.267 3.455a3.066 3.066 0 001.745-.723 
                                  3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 
                                  3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 
                                  3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 
                                  3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 
                                  3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 
                                  3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 
                                  3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 
                                  3.066 3.066 0 012.812-2.812zm7.44 
                                  5.252a1 1 0 00-1.414-1.414L9 10.586 
                                  7.707 9.293a1 1 0 00-1.414 1.414l2 
                                  2a1 1 0 001.414 0l4-4z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-green-500 mb-4">Account Active</h1>
                    <p class="text-xl text-gray-300 mb-8">Your account is fully active. No documents required.</p>
                </div>

            {{-- If user is verified (all docs approved) --}}
            @elseif(!$user->is_banned && $allApproved)
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="mb-6 p-6 bg-green-900 bg-opacity-20 rounded-full">
                        <svg class="w-20 h-20 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" 
                                  d="M6.267 3.455a3.066 3.066 0 001.745-.723 
                                  3.066 3.066 0 013.976 0 3.066 3.066 
                                  0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 
                                  1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 
                                  3.066 0 00-.723 1.745 3.066 3.066 
                                  0 01-2.812 2.812 3.066 3.066 
                                  0 00-1.745.723 3.066 3.066 
                                  0 01-3.976 0 3.066 3.066 
                                  0 00-1.745-.723 3.066 3.066 
                                  0 01-2.812-2.812 3.066 3.066 
                                  0 00-.723-1.745 3.066 3.066 
                                  0 010-3.976 3.066 3.066 
                                  0 00.723-1.745 3.066 3.066 
                                  0 012.812-2.812zm7.44 
                                  5.252a1 1 0 00-1.414-1.414L9 
                                  10.586 7.707 9.293a1 1 
                                  0 00-1.414 1.414l2 2a1 1 
                                  0 001.414 0l4-4z" 
                                  clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-bold text-green-500 mb-4">Documents Verified</h1>
                    <p class="text-xl text-gray-300 mb-8">Your Documents verification is complete and approved.</p>
                    <div class="px-6 py-3 rounded-full bg-green-900 bg-opacity-30 text-green-400">
                        <span class="font-semibold">Verified on {{ optional($verifiedDate)->format('M d, Y') }}</span>
                    </div>
                </div>

            {{-- If user is banned --}}
            @else
                <h2 class="text-2xl font-bold mb-6 text-red-400 text-center">Upload Your Required Documents</h2>

                <!-- Show ban reason -->
                <div class="mb-6 p-4 bg-red-900/30 border border-red-600 rounded-lg">
                    <p class="font-semibold text-red-300">
                        Account Status: {{ $user->is_banned ? 'Locked' : 'Active' }}
                    </p>
                    @if($user->ban_reason)
                        <p class="text-gray-200 mt-1"><strong>Reason:</strong> {{ $user->ban_reason }}</p>
                    @endif
                </div>

                @if($banDocuments->isEmpty())
                    <p class="text-gray-400">All your documents are under review. No resubmission needed.</p>
                @else
                    <form action="{{ route('user.unban.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        @foreach($banDocuments as $doc)
                            <div class="bg-[#24313f] p-4 rounded-lg border border-gray-600">
                                <label class="block font-semibold text-gray-200 mb-2">
                                    {{ $doc->name }}
                                    @if($doc->status === 'rejected')
                                        <span class="ml-2 text-red-400 text-sm">(Rejected)</span>
                                        @if($doc->comments)
                                            <p class="text-sm text-gray-400">Reason: {{ $doc->comments }}</p>
                                        @endif
                                    @endif
                                </label>
                                <input type="file" name="submit_documents[{{ $doc->id }}]" required
                                       class="w-full text-sm text-gray-200 
                                              file:mr-4 file:py-2 file:px-4 
                                              file:rounded-lg file:border-0 
                                              file:text-sm file:font-semibold
                                              file:bg-blue-600 file:text-white
                                              hover:file:bg-blue-700
                                              border border-gray-500 rounded-lg 
                                              shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endforeach

                        <div>
                            <label class="block font-semibold text-gray-200 mb-2">Why should we unlock you?</label>
                            <textarea name="why_unban" rows="4" required
                                      class="w-full bg-[#2b3947] border border-gray-600 rounded-lg shadow-sm p-3 
                                             focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                                             placeholder-gray-400 text-gray-200"
                                      placeholder="Explain your situation..."></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold shadow-md 
                                           hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200">
                                {{ $banDocuments->contains('status', 'rejected') ? 'Resubmit Request' : 'Submit Request' }}
                            </button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
