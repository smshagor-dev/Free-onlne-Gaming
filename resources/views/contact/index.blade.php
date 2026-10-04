@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md mt-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Your Messages</h1>
    
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif
    
    @if($messages->isEmpty())
        <p class="text-gray-600">You haven't sent any messages yet.</p>
    @else
        <div class="space-y-4">
            @foreach($messages as $message)
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">{{ $message->subject }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $message->created_at->format('M d, Y h:i A') }} | 
                                Priority: 
                                <span class="font-medium 
                                    @if($message->priority == 'high') text-red-600
                                    @elseif($message->priority == 'medium') text-yellow-600
                                    @else text-green-600
                                    @endif">
                                    {{ ucfirst($message->priority) }}
                                </span>
                               | Status: 
                                <span class="font-medium {{ $message->status ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $message->status ? 'Solved' : 'Pending' }}
                                </span>
                            </p>
                        </div>
                        @if($message->file)
                            <a href="{{ route('files.private', ['path' => $message->file]) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Attachment
                            </a>
                        @endif
                    </div>
                    
                    <div class="mt-2">
                        <p class="text-gray-600">{{ $message->message }}</p>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t text-sm text-gray-500 flex justify-between items-center">
                        <p>From: {{ $message->name }} &lt;{{ $message->email }}&gt;</p>
                        <span class="{{ $message->read ? 'text-green-600' : 'text-red-600' }} font-semibold">
                            {{ $message->read ? 'Read' : 'Unread' }}
                        </span>
                    </div>

                    <div class="mt-3 pt-3 border-t text-sm text-gray-500 flex justify-between items-center">
                        <p>Reply: {{ $message->reply ?? 'No Reply Found'}}</p>
                        <span class="{{ $message->read ? 'text-green-600' : 'text-red-600' }} font-semibold">
                            Replied on {{ $message->replied_at->format('F j, Y \a\t g:i A') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    <div class="mt-6">
        <a href="{{ route('contacts.create') }}"
           class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
            New Message
        </a>
    </div>
    <div class="mt-4">
        {{ $messages->links() }}
    </div>
</div>
@endsection
