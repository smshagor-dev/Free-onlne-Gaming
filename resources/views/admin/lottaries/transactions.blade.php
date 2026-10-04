@extends('layouts.admin')

@section('title', 'Lottery Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Lottery Management</h1>
            <p class="text-gray-600">Manage all lotteries and view transactions</p>
        </div>
        <div class="mt-4 md:mt-0">
            <a href="{{ route('admin.lottaries.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-plus mr-2"></i> New Lottery
            </a>
        </div>
    </div>

    <!-- Lotteries Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 class="text-lg font-medium text-gray-800">All Lotteries</h3>
            <div class="mt-2 md:mt-0">
                <input type="text" placeholder="Search lotteries..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lottery</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prize/Winner</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Draw Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($lottaries as $lottery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($lottery->photo)
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $lottery->photo) }}" alt="{{ $lottery->title }}">
                                    @else
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-indigo-600 flex items-center justify-center">
                                        <i class="fas fa-ticket-alt text-white"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $lottery->title }}</div>
                                    <div class="text-sm text-gray-500 line-clamp-1">{{ Str::limit($lottery->description, 40) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $setting->currency_symble ?? '' }}{{ number_format($lottery->price, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ $setting->site_currency ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Prize:</span> {{ $lottery->prize_number ?? 'TBD' }}
                            </div>
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Winner:</span> {{ $lottery->winner_number ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $lottery->draw_date->format('M d, Y') }}
                            <div class="text-xs text-gray-400">
                                {{ $lottery->draw_date->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $lottery->is_draw ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $lottery->is_draw ? 'Completed' : 'Active' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($lottery->total_tickets) }} tickets</div>
                            <div class="text-sm text-green-600 font-medium">{{ $setting->currency_symble ?? '' }}{{ number_format($lottery->total_income, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.lottaries.transactions.show', $lottery->id) }}"
                                class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700 transition">
                                View Transactions
                            </a>
                            <a href="{{ route('admin.lottaries.winners', ['lottaryId' => $lottery->id]) }}"
                                class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700 transition">
                                View Winners
                            </a>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <div class="py-8">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-ticket-alt text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">No lotteries found.</p>
                                <a href="#" class="inline-flex items-center px-4 py-2 mt-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-plus mr-2"></i> Create New Lottery
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($lottaries->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $lottaries->firstItem() ?? 0 }} to {{ $lottaries->lastItem() ?? 0 }} of {{ $lottaries->total() }} entries
                </div>
                <div class="mt-4 md:mt-0">
                    {{ $lottaries->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Lottery management page loaded');

        // Add search functionality
        const searchInput = document.querySelector('input[type="text"]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection