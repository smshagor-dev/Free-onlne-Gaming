@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center">
                <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-2 rounded-lg mr-3">
                    <i class="fas fa-ticket-alt mr-2"></i>
                </span>
                My Ticket
            </h1>
            <p class="text-gray-400 mt-2">Congratulations to all the Tickets of this lottery draw</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-2">
            <a href="{{ route('user.lottaries.view') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-md">
                <i class="fa fa-arrow-left mr-2"></i> Buy Lottary Tickets
            </a>
            <a href="{{ route('user.lottaries.mywin') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors duration-300">
                <i class="fas fa-trophy mr-2"> </i> My Win Tickets
            </a>
        </div>
    </div>

    @if($transactions->count() > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @foreach($transactions as $transaction)
        @php
        $isWinner = false;
        $wonPrize = null;

        // Check if this ticket is a winner
        if($transaction->lottary->winner_number == $transaction->ticket_number) {
        $isWinner = true;
        $wonPrize = App\Models\LottaryPrice::where('lottary_id', $transaction->lottary_id)
        ->where('price_number', $transaction->lottary->winner_number)
        ->first();
        }

        // Check if lottery has been drawn
        $isDrawn = $transaction->lottary->draw_date < now();

            // Generate ticket verification URL
            $ticketUrl=route('user.lottaries.view_ticket', ['ticketNumber'=> $transaction->ticket_number]);

            // Generate QR code URL (using a QR code generation service)
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($ticketUrl);
            @endphp

            <!-- Lottery Ticket Card -->
            <div class="relative bg-gradient-to-br from-yellow-400 via-yellow-300 to-yellow-200 rounded-xl shadow-2xl overflow-hidden border-4 border-yellow-500 p-1 transform hover:scale-105 transition-transform duration-300">
                <!-- Perforated edges -->
                <div class="absolute left-0 top-0 bottom-0 w-4 flex flex-col">
                    @for($i = 0; $i < 20; $i++)
                        <div class="h-4 w-4 rounded-full bg-[#0b141d] mx-auto my-1">
                </div>
                @endfor
            </div>
            <div class="absolute right-0 top-0 bottom-0 w-4 flex flex-col">
                @for($i = 0; $i < 20; $i++)
                    <div class="h-4 w-4 rounded-full bg-[#0b141d] mx-auto my-1">
            </div>
            @endfor
    </div>

    <!-- Ticket Content -->
    <div class="bg-white p-5 ml-4 mr-4 relative">
        <!-- Decorative elements -->
        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-yellow-600"></div>
        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-yellow-600"></div>
        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-yellow-600"></div>
        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-yellow-600"></div>

        <!-- Header -->
        <div class="text-center mb-4 border-b-2 border-dotted border-gray-300 pb-3">
            <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wider font-playfair">{{ $transaction->lottary->title }}</h2>
            <p class="text-gray-600 text-sm">Official Lottery Ticket</p>
        </div>

        <div class="flex flex-col md:flex-row gap-4">
            <!-- Left side - Ticket info -->
            <div class="flex-1">
                <!-- Status at the top -->
                @foreach($transactions as $transaction)
                    @php
                    $isDrawn = $transaction->lottary->is_draw;
                    $winner = $transaction->winner;
                    $isWinner = $winner && $winner->user_id == auth()->id();
                    $winningNumber = $transaction->lottary->winner_number;
                    @endphp

                
                @endforeach


                <!-- Ticket information in a clean list -->
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                        <span class="text-sm font-medium text-gray-600 hidden sm:inline">Ticket Number:</span>
                        <div class="flex items-center">
                            <span class="hidden sm:inline text-lg font-bold text-gray-800">{{ $transaction->ticket_number }}</span>
                            <span class="sm:hidden text-sm font-bold text-gray-800">{{ $transaction->ticket_number }}</span>
                            <button onclick="copyToClipboard('{{ $transaction->ticket_number }}')" class="ml-2 text-blue-500 hover:text-blue-700" title="Copy Ticket Number">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                        <span class="text-sm font-medium text-gray-600">Draw Date:</span>
                        <span class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($transaction->lottary->draw_date)->format('M d, Y H:i') }}</span>
                    </div>

                    <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                        <span class="text-sm font-medium text-gray-600">Transaction ID:</span>
                        <span class="transaction-id text-xs font-mono text-gray-700 truncate max-w-[120px]">{{ $transaction->transaction_number }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Amount:</span>
                        <span class="text-lg font-bold text-green-600">{{ $setting->currency_symble ?? '' }} {{ number_format($transaction->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Right side - QR Code -->
            <div class="border-l-2 border-dotted border-gray-300 pl-4 flex flex-col items-center justify-center">
                <!-- QR Code with colorful background -->
                <div class="bg-gradient-to-br from-blue-400 to-purple-500 p-3 rounded-lg mb-2 shadow-md" id="qr-container-{{ $transaction->id }}">
                    <a href="{{ $ticketUrl }}" target="_blank" class="block">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-28 h-28" id="qr-code-{{ $transaction->id }}">
                    </a>
                </div>
                <p class="text-xs text-gray-500 text-center mb-2">Scan or click to verify</p>

                <!-- Action buttons -->
                <div class="flex flex-col gap-2 w-full">
                    <button onclick="downloadTicket({{ $transaction->id }}, '{{ $transaction->ticket_number }}', '{{ \Carbon\Carbon::parse($transaction->lottary->draw_date)->format('M d, Y H:i') }}', '{{ $transaction->lottary->title }}')" class="px-3 py-2 bg-green-600 text-white rounded text-sm flex items-center justify-center hover:bg-green-700 transition-colors download-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Ticket
                    </button>

                    <button onclick="shareTicket('{{ $ticketUrl }}', '{{ $transaction->lottary->title }}', '{{ $transaction->ticket_number }}')" class="px-3 py-2 bg-blue-600 text-white rounded text-sm flex items-center justify-center hover:bg-blue-700 transition-colors share-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Share Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>

<div class="mt-8">
    {{ $transactions->links('vendor.pagination.custom') }}
</div>
@else
<div class="bg-[#1a2634] rounded-lg shadow-lg p-8 text-center border border-gray-700">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-900/30 rounded-full mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
        </svg>
    </div>
    <h3 class="text-xl font-semibold text-white mb-2">No Lottery Tickets Yet</h3>
    <p class="text-gray-400 mb-4">You haven't purchased any lottery tickets yet.</p>
    <a href="{{ route('user.lottaries.view') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Browse Lotteries
    </a>
</div>
@endif
</div>

<!-- HTML2Canvas library for downloading tickets -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            // Show a small notification
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
            notification.textContent = 'Copied to clipboard!';
            document.body.appendChild(notification);

            setTimeout(() => {
                document.body.removeChild(notification);
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
            alert('Failed to copy to clipboard');
        });
    }

    function downloadTicket(ticketId, ticketNumber, drawDate, title) {
        // Get the ticket element
        const ticketElement = document.querySelector(`#qr-container-${ticketId}`).closest('.relative');

        // Create a clone of the element to avoid affecting the original
        const clone = ticketElement.cloneNode(true);

        const buttons = clone.querySelectorAll('.download-btn, .share-btn');
        buttons.forEach(btn => btn.style.display = 'none');

        // Add to document temporarily but off-screen
        clone.style.position = 'fixed';
        clone.style.left = '-9999px';
        clone.style.top = '0';
        clone.style.transform = 'scale(1.5)'; // Increase size for better quality
        document.body.appendChild(clone);

        const txn = clone.querySelector('.transaction-id');
        if (txn) {
            txn.style.whiteSpace = 'normal';
            txn.style.overflow = 'visible';
            txn.style.textOverflow = 'clip';
            txn.style.wordBreak = 'break-all';
        }

        // Use html2canvas to capture the ticket as an image
        html2canvas(clone, {
            backgroundColor: null,
            scale: 3, // Higher resolution
            logging: false,
            useCORS: true, // Enable CORS for external images (QR code)
            allowTaint: true // Allow tainting for external images
        }).then(canvas => {
            // Convert canvas to image data URL
            const imageData = canvas.toDataURL('image/png');

            // Create a temporary link for downloading
            const link = document.createElement('a');
            link.href = imageData;
            link.download = `lottery-ticket-${ticketNumber}.png`;

            // Trigger the download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Remove the temporary element
            document.body.removeChild(clone);

            // Show success notification
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
            notification.textContent = 'Ticket downloaded successfully!';
            document.body.appendChild(notification);

            setTimeout(() => {
                document.body.removeChild(notification);
            }, 3000);
        }).catch(err => {
            console.error('Error generating ticket image:', err);

            // Remove the temporary element
            document.body.removeChild(clone);

            // Show error notification
            const notification = document.createElement('div');
            notification.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-md shadow-lg z-50';
            notification.textContent = 'Error downloading ticket. Please try again.';
            document.body.appendChild(notification);

            setTimeout(() => {
                document.body.removeChild(notification);
            }, 3000);
        });
    }

    function shareTicket(url, title, ticketNumber) {
        const shareText = `Check out my lottery ticket for ${title}! Ticket Number: ${ticketNumber}`;

        if (navigator.share) {
            // Web Share API is supported
            navigator.share({
                title: `My Lottery Ticket - ${title}`,
                text: shareText,
                url: url
            }).catch(err => {
                console.log('Error sharing:', err);
                fallbackShare(url, shareText);
            });
        } else {
            // Fallback for browsers that don't support Web Share API
            fallbackShare(url, shareText);
        }
    }

    function fallbackShare(url, text) {
        // Create a temporary element for the share options
        const shareModal = document.createElement('div');
        shareModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        shareModal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-sm w-full">
            <h3 class="text-lg font-bold mb-4">Share Ticket</h3>
            <p class="text-gray-600 mb-4">Copy the link to share your ticket:</p>
            <div class="flex mb-4">
                <input type="text" value="${url}" class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 text-black" readonly>
                <button onclick="copyToClipboard('${url}')" class="bg-blue-600 text-white px-3 py-2 rounded-r-md">Copy</button>
            </div>
            <div class="flex justify-between">
                <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" class="w-10 h-10 bg-blue-800 rounded-full flex items-center justify-center">
                    <span class="text-white text-xs font-bold">f</span>
                </a>
                <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(text + ' ' + url)}" target="_blank" class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center">
                    <span class="text-white text-xs font-bold">X</span>
                </a>
                <a href="https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}" target="_blank" class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-xs font-bold">WA</span>
                </a>
                <a href="https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}" target="_blank" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-xs font-bold">TG</span>
                </a>
            </div>
            <button onclick="this.closest('.fixed').remove()" class="mt-4 w-full bg-gray-200 text-gray-800 py-2 rounded-md hover:bg-gray-300">Close</button>
        </div>
    `;

        document.body.appendChild(shareModal);
    }
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap');

    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
    }

    .font-playfair {
        font-family: 'Playfair Display', serif;
    }

    /* Custom pagination styling */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }

    .pagination li {
        margin: 0 0.25rem;
    }

    .pagination li a,
    .pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.375rem;
        background-color: #1e293b;
        color: #e2e8f0;
        font-weight: 500;
        transition: all 0.2s;
    }

    .pagination li a:hover {
        background-color: #3b82f6;
        color: white;
    }

    .pagination li.active span {
        background-color: #3b82f6;
        color: white;
    }

    .pagination li.disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endsection