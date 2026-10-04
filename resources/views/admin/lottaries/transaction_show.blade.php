@extends('layouts.admin')

@section('title', $lottary->title . ' Transactions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $lottary->title }} - Transactions</h1>
            <p class="text-gray-600">All transactions for this lottery</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <a href="{{ route('admin.lottaries.transactions') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Lotteries
            </a>
            <a href="javascript:void(0)" id="exportBtn"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-download mr-2"></i> Export Data
            </a>

        </div>
    </div>

    <!-- Lottery Info -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row">
            <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-6">
                @if($lottary->photo)
                <img src="{{ asset('storage/' . $lottary->photo) }}" alt="{{ $lottary->title }}" class="w-32 h-32 object-cover rounded-lg">
                @else
                <div class="w-32 h-32 bg-gradient-to-r from-blue-400 to-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-white text-4xl"></i>
                </div>
                @endif
            </div>
            <div class="flex-grow">
                <h2 class="text-xl font-semibold text-gray-800">{{ $lottary->title }}</h2>
                <p class="text-gray-600 mt-2">{{ $lottary->description }}</p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-500">Ticket Price</p>
                        <p class="font-semibold text-gray-800">${{ number_format($lottary->price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Prize Number</p>
                        <p class="font-semibold text-gray-800">{{ $lottary->prize_number ?? 'TBD' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Draw Date</p>
                        <p class="font-semibold text-gray-800">{{ $lottary->draw_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-semibold {{ $lottary->is_draw ? 'text-green-600' : 'text-blue-600' }}">
                            {{ $lottary->is_draw ? 'Draw Completed' : 'Active' }}
                        </p>
                    </div>
                </div>

                @if($lottary->winner_number)
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-green-800 font-medium">
                        <i class="fas fa-trophy mr-1"></i> Winner Number:
                        <span class="text-lg font-bold">{{ $lottary->winner_number }}</span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Tickets Sold</h2>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($summary->total_tickets) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Total Income</h2>
                    <p class="text-3xl font-bold text-gray-900">{{ $setting->currency_symble ?? '' }} {{ number_format($summary->total_income, 2) }} {{ $setting->site_currency ?? '' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-800">Avg. Ticket Price</h2>
                    <p class="text-3xl font-bold text-gray-900">{{ $setting->currency_symble ?? '' }} {{ number_format($summary->total_tickets > 0 ? $summary->total_income / $summary->total_tickets : 0, 2) }} {{ $setting->site_currency ?? '' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
            <h3 class="text-lg font-medium text-gray-800">Transaction History</h3>
            <div class="mt-2 md:mt-0">
                <input type="text" placeholder="Search transactions..." class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Win Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $transaction->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($transaction->user->name) }}&background=random" alt="{{ $transaction->user->name }}">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $transaction->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $setting->currency_symble ?? '' }} {{ number_format($transaction->amount, 2) }} {{ $setting->site_currency ?? '' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $transaction->transaction_type == 'purchase' ? 'green' : 'blue' }}-100 text-{{ $transaction->transaction_type == 'purchase' ? 'green' : 'blue' }}-800">
                                {{ ucfirst($transaction->ticket_number) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="#" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <div class="py-8">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-4">
                                    <i class="fas fa-exchange-alt text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">No transactions found for this lottery.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                </div>
                <div class="mt-4 md:mt-0">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Lottery transactions page loaded');
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Lottery transactions page loaded');

        document.getElementById('exportBtn').addEventListener('click', function () {
            exportTableToCSV("{{ $lottary->title }}_transactions.csv");
        });

        function downloadCSV(csv, filename) {
            let csvFile;
            let downloadLink;

            // CSV FILE
            csvFile = new Blob([csv], { type: "text/csv" });

            // Download link
            downloadLink = document.createElement("a");

            // File name
            downloadLink.download = filename;

            // Create a link to the file
            downloadLink.href = window.URL.createObjectURL(csvFile);

            // Hide download link
            downloadLink.style.display = "none";

            // Add link to DOM
            document.body.appendChild(downloadLink);

            // Click download link
            downloadLink.click();
        }

        function exportTableToCSV(filename) {
            let csv = [];
            let rows = document.querySelectorAll("table tr");

            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll("td, th");
                for (let j = 0; j < cols.length; j++) {
                    // Clean text (remove commas/newlines)
                    let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/,/g, "");
                    row.push('"' + data + '"');
                }
                csv.push(row.join(","));
            }

            // Download CSV
            downloadCSV(csv.join("\n"), filename);
        }
    });
</script>

@endsection