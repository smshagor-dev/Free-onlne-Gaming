@extends('layouts.admin')


@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">All Deposits</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.user.deposits.pending') }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Pending</a>
            <a href="{{ route('admin.user.deposits.approved') }}" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Approved</a>
            <a href="{{ route('admin.user.deposits.rejected') }}" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Rejected</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documents</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($deposits as $deposit)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $deposit->user->name ?? $deposit->user->username }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deposit->gateway->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($deposit->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($deposit->documents->count() > 0)
                                <div class="space-y-2">
                                    @foreach($deposit->documents as $document)
                                        <div>
                                            <span class="font-medium">{{ $document->requiredDocument->name }}:</span>
                                            @if(filter_var($document->value, FILTER_VALIDATE_URL))
                                                <a href="{{ $document->value }}" target="_blank" class="text-blue-500 hover:underline">View File</a>
                                            @elseif(str_starts_with($document->value, 'user_deposit_docs/'))
                                                <a href="{{ route('files.private', ['path' => $document->value]) }}" target="_blank" class="text-blue-500 hover:underline">View File</a>
                                            @else
                                                {{ Str::limit($document->value, 30) }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                No documents
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $deposit->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($deposit->status === 'reject' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($deposit->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" data-deposit-id="{{ $deposit->id }}" 
                               class="text-indigo-600 hover:text-indigo-900 mr-3 update-status">Update Status</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-gray-50 sm:px-6">
            {{ $deposits->links() }}
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="statusForm" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Update Deposit Status</h3>
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="approved">Approved</option>
                            <option value="reject">Rejected</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="comments" class="block text-sm font-medium text-gray-700">Comments (Optional)</label>
                        <textarea id="comments" name="comments" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border border-gray-300 rounded-md"></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const depositId = this.getAttribute('data-deposit-id');
            const form = document.getElementById('statusForm');
            form.action = `/sm-shagor/free-games/admin-main/control-back-office/deposits/${depositId}/update-status`;
            document.getElementById('statusModal').classList.remove('hidden');
        });
    });

    function closeModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
</script>
@endsection
