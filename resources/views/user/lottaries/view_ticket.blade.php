@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <!-- Ticket Verification Card -->
        <div class="relative bg-gradient-to-br from-yellow-400 via-yellow-300 to-yellow-200 rounded-xl shadow-2xl overflow-hidden border-4 border-yellow-500 p-1">
            <!-- Perforated edges -->
            <div class="absolute left-0 top-0 bottom-0 w-4 flex flex-col">
                @for($i = 0; $i < 20; $i++)
                    <div class="h-4 w-4 rounded-full bg-[#0b141d] mx-auto my-1"></div>
                @endfor
            </div>
            <div class="absolute right-0 top-0 bottom-0 w-4 flex flex-col">
                @for($i = 0; $i < 20; $i++)
                    <div class="h-4 w-4 rounded-full bg-[#0b141d] mx-auto my-1"></div>
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
                    <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wider">Ticket Verification</h2>
                    <p class="text-gray-600 text-sm">Official Lottery Ticket</p>
                </div>
                
                <!-- Verification Status -->
                @if($ticket)
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-green-700">Verified Ticket</h3>
                        <p class="text-sm text-gray-600">This ticket is valid and registered in our system</p>
                    </div>
                @else
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-red-700">Ticket Not Found</h3>
                        <p class="text-sm text-gray-600">This ticket number is not registered in our system</p>
                    </div>
                @endif
                
                <!-- Ticket Information -->
                @if($ticket)
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                            <span class="text-lg font-bold text-gray-800">{{ $ticket->ticket_number }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                            <span class="text-sm font-medium text-gray-600">Lottery:</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $ticket->lottary->title }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center border-b border-dotted border-gray-200 pb-2">
                            <span class="text-sm font-medium text-gray-600">Draw Date:</span>
                            <span class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($ticket->lottary->draw_date)->format('M d, Y H:i') }}</span>
                        </div>
                        
                        @php
                            $isDrawn = $ticket->lottary->draw_date < now();
                            $isWinner = $ticket->lottary->winner_number == $ticket->ticket_number;
                        @endphp
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Status:</span>
                            @if(!$isDrawn)
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold">Pending Draw</span>
                            @elseif($isWinner)
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Winner</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">Not Winner</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- QR Code Section -->
                    <div class="text-center mb-6">
                        @php
                            $ticketUrl = url()->current();
                            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($ticketUrl);
                        @endphp
                        
                        <div class="bg-gradient-to-br from-blue-400 to-purple-500 p-3 rounded-lg inline-block mb-2 shadow-md">
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" class="w-32 h-32 mx-auto">
                        </div>
                        <p class="text-xs text-gray-500">Scan to verify this ticket</p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3">
                        <button onclick="downloadSimpleTicket('{{ $ticket->ticket_number }}', '{{ \Carbon\Carbon::parse($ticket->lottary->draw_date)->format('M d, Y H:i') }}', '{{ $qrCodeUrl }}', '{{ $ticket->lottary->title }}')" class="px-4 py-2 bg-green-600 text-white rounded-md flex items-center justify-center hover:bg-green-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Ticket
                        </button>
                        
                        <button onclick="shareTicket('{{ $ticketUrl }}', '{{ $ticket->lottary->title }}', '{{ $ticket->ticket_number }}')" class="px-4 py-2 bg-blue-600 text-white rounded-md flex items-center justify-center hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            Share Ticket
                        </button>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-gray-600">Please check the ticket number and try again.</p>
                        <a href="{{ route('user.lottaries.view') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Browse Lotteries
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="text-center mt-6">
            <a href="{{ route('user.lottaries.my') }}" class="inline-flex items-center text-blue-400 hover:text-blue-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to My Tickets
            </a>
        </div>
    </div>
</div>

<!-- HTML2Canvas library for downloading tickets -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

<script>
function downloadSimpleTicket(ticketNumber, drawDate, qrCodeUrl, title) {
    // Create a simple ticket for download
    const ticketDiv = document.createElement('div');
    ticketDiv.style.width = '400px';
    ticketDiv.style.height = '200px';
    ticketDiv.style.padding = '20px';
    ticketDiv.style.backgroundColor = '#fffbeb';
    ticketDiv.style.border = '3px solid #f59e0b';
    ticketDiv.style.borderRadius = '10px';
    ticketDiv.style.fontFamily = 'Arial, sans-serif';
    ticketDiv.style.position = 'relative';
    ticketDiv.style.overflow = 'hidden';
    
    // Add decorative elements
    ticketDiv.innerHTML = `
        <div style="position:absolute; top:0; left:0; width:8px; height:8px; border-top:2px solid #f59e0b; border-left:2px solid #f59e0b;"></div>
        <div style="position:absolute; top:0; right:0; width:8px; height:8px; border-top:2px solid #f59e0b; border-right:2px solid #f59e0b;"></div>
        <div style="position:absolute; bottom:0; left:0; width:8px; height:8px; border-bottom:2px solid #f59e0b; border-left:2px solid #f59e0b;"></div>
        <div style="position:absolute; bottom:0; right:0; width:8px; height:8px; border-bottom:2px solid #f59e0b; border-right:2px solid #f59e0b;"></div>
        
        <div style="display: flex;">
            <div style="flex: 1;">
                <h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 18px; font-weight: bold; text-align: center;">${title}</h2>
                <div style="margin-bottom: 10px;">
                    <div style="font-size: 12px; color: #6b7280;">Ticket Number</div>
                    <div style="font-size: 16px; font-weight: bold; color: #1f2937;">${ticketNumber}</div>
                </div>
                <div style="margin-bottom: 10px;">
                    <div style="font-size: 12px; color: #6b7280;">Draw Date</div>
                    <div style="font-size: 14px; font-weight: bold; color: #1f2937;">${drawDate}</div>
                </div>
            </div>
            <div style="width: 100px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-left: 1px dashed #d1d5db; padding-left: 10px;">
                <img src="${qrCodeUrl}" alt="QR Code" style="width: 80px; height: 80px;">
                <div style="font-size: 9px; color: #6b7280; margin-top: 5px; text-align: center;">Scan to verify</div>
            </div>
        </div>
        <div style="position: absolute; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 10px; color: #9ca3af;">
            Official Lottery Ticket - ${new Date().toLocaleDateString()}
        </div>
    `;
    
    // Add to document temporarily
    document.body.appendChild(ticketDiv);
    ticketDiv.style.position = 'absolute';
    ticketDiv.style.left = '-9999px';
    
    // Use html2canvas to capture the ticket as an image
    html2canvas(ticketDiv, {
        backgroundColor: null,
        scale: 2 // Higher resolution
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
        document.body.removeChild(ticketDiv);
        
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
        document.body.removeChild(ticketDiv);
        
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
                <input type="text" value="${url}" class="flex-1 border border-gray-300 rounded-l-md px-3 py-2" readonly>
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
</script>
@endsection