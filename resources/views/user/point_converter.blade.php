@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-full">
    <div class="max-w-lg mx-auto rounded-lg shadow-md overflow-hidden bg-[#0f1923]">
        <!-- Header -->
        <div class="bg-[#1a2a3a] px-6 py-4">
            <h1 class="text-white text-xl font-semibold text-center">Points Conversion</h1>
            <p class="text-gray-300 text-center">Convert Your Points to get VIP Bonus</p>
        </div>

        <div class="p-6">
            <!-- User Current Points -->
            <div class="mb-6 text-center">
                <p class="text-gray-300">Your Available Points:</p>
                <span class="text-[#4fd1c5] font-bold text-2xl">{{ auth()->user()->available_points ?? 0 }} pts</span>
            </div>

            <!-- Conversion Info -->
            <div class="mb-6 p-4 bg-[#1a2a3a] rounded-lg">
                <h3 class="text-[#4fd1c5] font-medium mb-2">Conversion Rate</h3>
                <p class="text-gray-300">
                    Every <span class="font-bold">{{ $minPoints }}</span> points = <span class="font-bold">{{ $getBalance }} {{ $currency }}</span>
                </p>
            </div>

            <!-- Points Input Form -->
            <form id="convertForm" action="{{ route('user.points.convert') }}" method="POST">
                @csrf
                <div class="mb-4 relative">
                    <label for="pointAmount" class="block text-gray-300 mb-2">Enter Points to Convert:</label>
                    <div class="flex">
                        <input 
                            type="number" 
                            id="pointAmount" 
                            name="point_amount"
                            min="{{ $minPoints }}"
                            max="{{ auth()->user()->available_points ?? 0 }}"
                            class="w-full px-4 py-2 rounded-l-lg bg-[#1a2a3a] text-white border border-gray-600 focus:outline-none focus:border-[#4fd1c5]"
                            placeholder="Enter points amount">
                        <button type="button" id="maxBtn" class="px-4 py-2 bg-[#4fd1c5] hover:bg-[#38b2ac] text-[#0f1923] rounded-r-lg font-bold transition">Max</button>
                    </div>
                    <p class="text-gray-400 text-sm mt-1">Minimum conversion: {{ $minPoints }} points</p>
                </div>

                <!-- Converted Amount Preview -->
                <div class="mb-4 p-3 bg-[#1a2a3a] rounded-lg">
                    <p class="text-gray-300 mb-1">Converted Amount:</p>
                    <p id="convertedPreview" class="text-[#4fd1c5] font-bold text-lg">0 {{ $currency }}</p>
                </div>

                <!-- Conversion Button -->
                <button
                    type="submit"
                    id="convertBtn"
                    class="w-full bg-[#4fd1c5] hover:bg-[#38b2ac] text-[#0f1923] font-bold py-3 px-4 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    Convert Points
                </button>
            </form>

            <!-- Requirements Note -->
            @if(auth()->user()->level_id < 1)
                <p class="mt-3 text-sm text-red-400 text-center">
                    * You must be at least level 1 to convert points.
                </p>
            @endif

            <!-- Success/Error Messages -->
            <div id="messageContainer" class="mt-4"></div>
        </div>
    </div>


    <!-- Transaction History -->
    <div class="mt-8 p-4 bg-[#1a2a3a] rounded-lg">
        <h1 class="text-[#4fd1c5] font-medium mb-4 text-center">Your Point Conversion History</h1>

        @if($transactions->isEmpty())
            <p class="text-gray-400 text-sm">No conversions yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-gray-300 border-collapse">
                    <thead>
                        <tr class="border-b border-gray-600">
                            <th class="px-3 py-2">Transaction #</th>
                            <th class="px-3 py-2">Points</th>
                            <th class="px-3 py-2">Converted Amount</th>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                            <tr class="border-b border-gray-700">
                                <td class="px-3 py-2">{{ $tx->transaction_number }}</td>
                                <td class="px-3 py-2">{{ $tx->point_amount }}</td>
                                <td class="px-3 py-2">{{ $tx->amount }} {{ $currency }}</td>
                                <td class="px-3 py-2">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                <td class="px-3 py-2">{{ $tx->status ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="my-4">
        {{ $transactions->links() }}
    </div>
</div>


<script>
    const input = document.getElementById('pointAmount');
    const preview = document.getElementById('convertedPreview');
    const button = document.getElementById('convertBtn');
    const maxBtn = document.getElementById('maxBtn');

    const minPoints = {{ $minPoints }};
    const getBalance = {{ $getBalance }};
    const maxPoints = {{ auth()->user()->available_points ?? 0 }};
    const currency = "{{ $currency }}";

    // Function to update converted amount and button state
    function updateConversion() {
        let value = parseInt(input.value) || 0;

        if (value >= minPoints && value <= maxPoints) {
            const convertedAmount = (value / minPoints) * getBalance;
            preview.textContent = `${convertedAmount.toFixed(2)} ${currency}`;
            button.disabled = false;
        } else {
            preview.textContent = `0 ${currency}`;
            button.disabled = true;
        }
    }

    input.addEventListener('input', updateConversion);

    // Max button functionality
    maxBtn.addEventListener('click', function() {
        input.value = maxPoints;
        updateConversion();
    });

    // Handle form submit via AJAX
    document.getElementById('convertForm').addEventListener('submit', function(e) {
        e.preventDefault();
        button.textContent = 'Processing...';
        button.disabled = true;

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                point_amount: parseInt(input.value)
            })
        })
        .then(response => response.json())
        .then(data => {
            const messageContainer = document.getElementById('messageContainer');
            messageContainer.innerHTML = '';

            if (data.success) {
                messageContainer.innerHTML = `
                    <div class="p-4 mb-4 text-sm text-green-300 bg-green-900 rounded-lg">
                        ${data.message} Converted amount: ${data.converted_amount} ${currency}
                    </div>
                `;
                setTimeout(() => window.location.reload(), 2000);
            } else {
                messageContainer.innerHTML = `
                    <div class="p-4 mb-4 text-sm text-red-300 bg-red-900 rounded-lg">
                        ${data.error}
                    </div>
                `;
                button.disabled = false;
                button.textContent = 'Convert Points';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.textContent = 'Convert Points';
        });
    });
</script>
@endsection
