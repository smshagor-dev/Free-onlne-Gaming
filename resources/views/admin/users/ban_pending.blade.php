@extends('layouts.admin')

@section('content')
<div class="p-6 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Pending Ban Documents</h1>

    @foreach($users as $user)
        <div class="bg-white p-4 rounded-lg mb-6 border border-gray-300 shadow-sm">
            <p class="text-gray-800 font-semibold">User: {{ $user->name }} (ID: {{ $user->user_id }}) <br> Username: {{$user->username}}</p>
            <p class="text-gray-700 mb-2"><strong>Ban Reason:</strong> {{ $user->ban_reason }}</p>
            <p class="text-gray-700 mb-4"><strong>Why Unban:</strong> {{ $user->banDocuments->first()->why_unban }}</p>

            <form action="{{ route('admin.ban_documents.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <table class="w-full text-left border border-gray-300 rounded-lg">
                    <thead>
                        <tr class="bg-gray-200 text-gray-800">
                            <th class="p-2 border">Document</th>
                            <th class="p-2 border">File</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->banDocuments as $doc)
                            <tr class="border-t border-gray-300">
                                <td class="p-2">{{ $doc->name }}</td>
                                <td class="p-2">
                                    @if($doc->submit_documents)
                                        <a href="{{ route('files.private', ['path' => $doc->submit_documents]) }}" target="_blank"
                                        class="inline-block px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition">
                                            View
                                        </a>
                                    @else
                                        <span class="text-gray-400">Not submitted</span>
                                    @endif
                                </td>
                                <td class="p-2">
                                    <select name="status[{{ $doc->id }}]" 
                                            class="p-1 rounded border w-full 
                                                {{ $doc->status == 'pending' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                                {{ $doc->status == 'approved' ? 'bg-green-200 text-green-800' : '' }}
                                                {{ $doc->status == 'rejected' ? 'bg-red-200 text-red-800' : '' }}">
                                        <option value="pending" {{ $doc->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $doc->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ $doc->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="text" name="comments[{{ $doc->id }}]" value="{{ $doc->comments }}" class="bg-gray-100 text-gray-800 p-1 rounded border border-gray-300 w-full">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4 flex space-x-2">
                <a href="{{ route('admin.users.banned') }}" 
                class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400 transition">
                    Back to Banned Users List
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Update User Documents
                </button>
            </div>

            </form>
        </div>
    @endforeach
</div>
@endsection
