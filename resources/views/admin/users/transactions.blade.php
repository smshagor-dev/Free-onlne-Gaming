@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">User Transactions</h1>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.user.transactions') }}" class="mb-6 flex space-x-2">
        <input type="text" name="transaction_number" value="{{ $search }}"
            placeholder="Search by Transaction Number"
            class="px-4 py-2 border rounded-lg w-1/3">
        <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Search
        </button>
    </form>

    {{-- Transactions Table --}}
    <div class="bg-white shadow-md rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Transaction #</th>
                    <th class="px-4 py-2">User ID</th>
                    <th class="px-4 py-2">Session ID</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2">Transaction Type</th>
                    <th class="px-4 py-2">Casino Deatils</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Remarks</th>
                    <th class="px-4 py-2">Comments</th>
                    <th class="px-4 py-2">Point Acount</th>
                    <th class="px-4 py-2">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $trx)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $trx->id }}</td>
                    <td class="px-4 py-2 font-mono">{{ $trx->transaction_number }}</td>
                    <td class="px-4 py-2"><span class="font-mono text-gray-600">#{{ $trx->user->user_id }}</span><br>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $trx->user->name ?? 'Unknown User' }}
                        </span></br>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $trx->user->username ?? 'Unknown User' }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $trx->session_id ?? '-' }}</td>
                    <td class="px-4 py-2">{{ number_format($trx->amount, 2) }}</td>
                    <td class="px-4 py-2">{{ $trx->trx_type }}</td>
                    <td class="px-4 py-2">{{ $trx->transaction_type }}</td>
                    <td class="px-4 py-2">
                        @php
                            $details = json_decode($trx->casino_details, true);
                            $allowedKeys = ['sessionId', 'login', 'bet', 'win', 'date'];
                        @endphp

                        @if(is_array($details))
                            <div class="text-xs text-gray-700 space-y-1">
                                @foreach($allowedKeys as $key)
                                    @if(isset($details[$key]))
                                        <div>
                                            <span class="font-semibold">{{ ucfirst($key) }}:</span>
                                            {{ $details[$key] }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            {{ $trx->casino_details ?? '-' }}
                        @endif
                    </td>

                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs 
                                {{ $trx->status === 'approved' ? 'bg-green-200 text-green-800' : 
                                   ($trx->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : 'bg-red-200 text-red-800') }}">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $trx->remark ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $trx->comments ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $trx->point_amount ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $trx->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">No transactions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $transactions->appends(['transaction_number' => $search])->links() }}
    </div>
</div>
@endsection