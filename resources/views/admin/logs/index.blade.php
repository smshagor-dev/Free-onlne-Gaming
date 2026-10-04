@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Laravel Log Viewer</h2>
        <div class="flex space-x-2">
            <button id="refreshLogs" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg shadow flex items-center">
                <svg class="w-4 h-4 mr-1 animate-spin hidden" id="refreshIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M4 20l5.586-5.586M20 4l-5.586 5.586" />
                </svg>
                Refresh
            </button>
            <form method="POST" action="{{ route('admin.logs.clear') }}">
                @csrf
                <button type="submit" 
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow">
                    Clear Logs
                </button>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 border border-green-400 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <div id="logTableWrapper" class="overflow-x-auto bg-white rounded-xl shadow">
        <table class="w-full border-collapse">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-16">#</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Log Entry</th>
                </tr>
            </thead>
            <tbody id="logTableBody">
                @forelse($logs as $index => $line)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $logs->firstItem() + $index }}</td>
                        <td class="px-4 py-2 text-sm font-mono text-gray-800 whitespace-pre-wrap">{{ $line }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-4 text-center text-gray-500">No logs found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4" id="paginationLinks">
        {{ $logs->links() }}
    </div>
</div>

{{-- Refresh Script --}}
<script>
document.getElementById('refreshLogs').addEventListener('click', function() {
    let btn = this;
    let icon = document.getElementById('refreshIcon');
    icon.classList.remove('hidden');

    fetch("{{ route('admin.logs.index') }}?page={{ request()->get('page', 1) }}", {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        let parser = new DOMParser();
        let doc = parser.parseFromString(html, 'text/html');

        document.getElementById('logTableBody').innerHTML = doc.querySelector('#logTableBody').innerHTML;
        document.getElementById('paginationLinks').innerHTML = doc.querySelector('#paginationLinks').innerHTML;

        icon.classList.add('hidden');
    })
    .catch(() => {
        icon.classList.add('hidden');
        alert("Failed to refresh logs!");
    });
});
</script>
@endsection
