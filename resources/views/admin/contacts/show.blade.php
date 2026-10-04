@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.contacts.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to messages
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Message Details</h3>
            <p class="mt-1 text-sm text-gray-500">Submitted on {{ $message->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Name</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $message->name }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Email</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $message->email }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Priority</h4>
                    <p class="mt-1 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($message->priority == 'high') bg-red-100 text-red-800
                            @elseif($message->priority == 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($message->priority) }}
                        </span>
                    </p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Subject</h4>
                    <p class="mt-1 text-sm text-gray-900">{{ $message->subject }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-500">Message</h4>
                <div class="mt-1 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $message->message }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-500">Reply</h4>
                <div class="mt-1 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $message->reply ?? 'No Update' }}</p>
                </div>
            </div>

            @if($message->file)
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-500">Attachment</h4>
                    <div class="mt-1">
                        <a href="{{ route('files.private', ['path' => $message->file]) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Download Attachment
                        </a>
                    </div>
                </div>
            @endif
        </div>
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200 flex justify-end space-x-3">
            <button type="button" 
                onclick="updateStatus({{ $message->id }})"
                id="status-toggle-{{ $message->id }}"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white 
                {{ $message->status ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500' }} 
                focus:outline-none focus:ring-2 focus:ring-offset-2">
                {{ $message->status ? 'Solved' : 'Pending' }}
            </button>
            
            <form action="{{ route('admin.contacts.destroy', $message->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this message?')">
                    Delete Message
                </button>
            </form>
        </div>
    </div>
    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Reply to Message</h3>
            @if($message->replied_at)
                <p class="mt-1 text-sm text-gray-500">Replied on {{ $message->replied_at->format('F j, Y \a\t g:i A') }}</p>
            @endif
        </div>
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('admin.contacts.reply', $message->id) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Your Reply</label>
                        <textarea id="comment" name="comment" rows="6" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                            {{ $errors->has('comment') ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500' : '' }}"
                            placeholder="Type your reply here..." required>{{ old('comment', $message->reply) }}</textarea>
                        @error('comment')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            @if($message->replied_at)
                                Last replied {{ $message->replied_at->diffForHumans() }}
                            @else
                                No reply sent yet
                            @endif
                        </div>
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                            </svg>
                            {{ $message->replied_at ? 'Update Reply' : 'Send Reply' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
function updateStatus(id) {
    fetch(`//sm-shagor/free-games/admin-main/control-back-office/contacts/${id}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const button = document.getElementById(`status-toggle-${id}`);
            if (data.status) {
                button.classList.remove('bg-gray-600', 'hover:bg-gray-700', 'focus:ring-gray-500');
                button.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
                button.textContent = 'Solved';
            } else {
                button.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500');
                button.classList.add('bg-gray-600', 'hover:bg-gray-700', 'focus:ring-gray-500');
                button.textContent = 'Pending';
            }
            
            // Show a success notification (you can use your preferred notification system)
            alert('Status updated successfully');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}
</script>


@endsection
