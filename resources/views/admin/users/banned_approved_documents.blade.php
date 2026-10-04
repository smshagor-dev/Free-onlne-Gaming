@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Approved Documents for {{ $user->name }}</h1>
    <p class="text-gray-800 font-semibold">User: {{ $user->name }} (ID: {{ $user->user_id }}) <br> Username: {{$user->username}}</p>
    <p class="text-gray-700 mb-2"><strong>Ban Reason:</strong> {{ $user->ban_reason }}</p>
    <p class="text-gray-700 mb-4"><strong>Why Unban:</strong> {{ $user->banDocuments->first()->why_unban }}</p>

    <table class="w-full border border-gray-300 rounded-lg">
        <thead>
            <tr class="bg-gray-200 text-gray-800">
                <th class="p-2 border">Document Name</th>
                <th class="p-2 border">File</th>
                <th class="p-2 border">Status</th>
                <th class="p-2 border">Comments</th>
                <th class="p-2 border">Why Unban</th>
            </tr>
        </thead>
        <tbody>
            @foreach($user->banDocuments as $doc)
                <tr class="border-t border-gray-300">
                    <td class="p-2">{{ $doc->name }}</td>
                    <td class="p-2">
                        @if($doc->submit_documents)
                            <a href="{{ route('files.private', ['path' => $doc->submit_documents]) }}" target="_blank" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700">View</a>
                        @else
                            <span class="text-gray-400">Not submitted</span>
                        @endif
                    </td>
                    <td class="p-2 text-green-600 font-semibold">{{ $doc->status }}</td>
                    <td class="p-2">{{ $doc->comments }}</td>
                    <td class="p-2">{{ $doc->why_unban }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('admin.users.banned.approved') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back</a>
    </div>
</div>
@endsection
