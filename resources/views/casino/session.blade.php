@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-900 p-6 text-gray-100">
    <div class="mx-auto max-w-6xl">
        <h1 class="mb-6 text-2xl font-bold">{{ $pageTitle }}</h1>

        <div class="overflow-x-auto rounded-lg bg-gray-800 p-4 shadow-lg">
            @php($entries = $data['content']['sessionsLog'] ?? $data['content']['log'] ?? [])

            @if (is_array($entries) && count($entries))
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-700 text-left text-gray-300">
                            <th class="px-4 py-3">Session</th>
                            <th class="px-4 py-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-3 align-top">{{ $session }}</td>
                                <td class="px-4 py-3"><pre class="whitespace-pre-wrap text-xs text-gray-300">{{ json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-400">No session activity was returned.</p>
            @endif
        </div>
    </div>
</div>
@endsection
