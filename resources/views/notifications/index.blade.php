@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-slate-800 border-b border-gray-200">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-white-800">Notifications</h2>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($notifications->isEmpty())
                <div class="p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                    You have no notifications.
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($notifications as $notification)
                        <div class="p-4 border rounded-lg {{ $notification->is_read ? 'bg-slate-800' : 'bg-slate-800' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-white-800">{{ $notification->title }}</h3>
                                    <p class="mt-1 text-white-600">{{ $notification->message }}</p>
                                    <p class="mt-2 text-sm text-white-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div>
                                    @if (!$notification->is_read)
                                        <form action="{{ route('user.notifications.read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-5 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Mark as Read</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notification', () => ({
            showModal: false,
            
            init() {
                // Handle form submission
                document.getElementById('createNotificationForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    
                    fetch(e.target.action, {
                        method: 'POST',
                        body: new FormData(e.target),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.showModal = false;
                        window.location.reload(); // Refresh to show new notification
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while creating the notification.');
                    });
                });
            }
        }));
    });
</script>
@endsection
