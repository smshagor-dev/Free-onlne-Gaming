@extends('layouts.app')

@section('content')
<div class="bg-[#0b141d] min-h-screen py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6 text-white">Available Bonuses</h1>

        @if($bonuses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($bonuses as $bonus)
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-700 group"
                onclick="openModal('{{ $bonus->id }}')">
                @if($bonus->is_featured && $bonus->photo)
                <div class="relative overflow-hidden">
                    <img src="{{ asset('storage/' . $bonus->photo) }}" alt="{{ $bonus->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-3 right-3 bg-yellow-500 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full shadow-md">
                        Featured
                    </div>
                </div>
                @endif

                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-xl font-bold text-white line-clamp-1">{{ $bonus->title }}</h2>
                        <span class="bg-blue-700 text-blue-100 text-xs font-semibold px-2.5 py-1 rounded-full ml-2 whitespace-nowrap">
                            {{ ucfirst($bonus->bonus_type) }}
                        </span>
                    </div>

                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $bonus->subtitle }}</p>

                    <div class="flex items-center justify-between">
                        <span class="text-xl font-bold text-green-400">
                            {{ number_format($bonus->bonus_amount, 2) }}
                        </span>

                        <div class="text-xs text-gray-500">
                            @if($bonus->start_date && $bonus->end_date)
                            {{ $bonus->start_date->format('M d') }} - {{ $bonus->end_date->format('M d') }}
                            @else
                            Ongoing
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for this bonus -->
            <div id="modal-{{ $bonus->id }}" class="fixed inset-0 bg-gray-900 bg-opacity-90 overflow-y-auto h-full w-full hidden z-50 transition-opacity duration-300">
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative w-full max-w-4xl bg-gradient-to-b from-gray-800 to-gray-900 rounded-xl shadow-2xl overflow-hidden border border-gray-700">
                        <!-- Close button -->
                        <button onclick="closeModal('{{ $bonus->id }}')" class="absolute top-4 right-4 z-10 text-gray-400 hover:text-white bg-gray-800 rounded-full p-2 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                        <!-- Modal content with scrolling -->
                        <div class="max-h-[90vh] overflow-y-auto">
                            <!-- Hero image -->
                            @if($bonus->photo)
                            <div class="relative h-56 md:h-72 w-full overflow-hidden">
                                <img src="{{ asset('storage/' . $bonus->photo) }}" alt="{{ $bonus->title }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>

                                @if($bonus->is_featured)
                                <div class="absolute top-4 left-4 bg-yellow-500 text-yellow-900 text-sm font-bold px-3 py-1 rounded-full shadow-md">
                                    Featured Bonus
                                </div>
                                @endif

                                <div class="absolute bottom-4 left-4">
                                    <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $bonus->title }}</h2>
                                    <p class="text-gray-300">{{ $bonus->subtitle }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="p-6">
                                <!-- Stats grid -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                    <div class="bg-gray-700 rounded-lg p-4 text-center">
                                        <p class="text-sm text-gray-400 mb-1">Bonus Amount</p>
                                        <p class="text-2xl font-bold text-green-400">{{ number_format($bonus->bonus_amount, 2) }}</p>
                                    </div>

                                    <div class="bg-gray-700 rounded-lg p-4 text-center">
                                        <p class="text-sm text-gray-400 mb-1">Bonus Type</p>
                                        <p class="text-xl font-semibold text-white">{{ ucfirst($bonus->bonus_type) }}</p>
                                    </div>

                                    <div class="bg-gray-700 rounded-lg p-4 text-center">
                                        <p class="text-sm text-gray-400 mb-1">Start Date</p>
                                        <p class="text-md text-white">{{ $bonus->start_date ? $bonus->start_date->format('M d, Y') : 'No' }}</p>
                                    </div>

                                    <div class="bg-gray-700 rounded-lg p-4 text-center">
                                        <p class="text-sm text-gray-400 mb-1">End Date</p>
                                        <p class="text-md text-white">{{ $bonus->end_date ? $bonus->end_date->format('M d, Y') : 'No' }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <h3 class="text-xl font-semibold text-white mb-3 border-b border-gray-700 pb-2">Description</h3>
                                    <div class="prose prose-invert max-w-none text-gray-300">
                                        {!! \App\Support\SafeHtml::clean($bonus->description) !!}
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-700">
                                    <a href="{{ url('/') }}"
                                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold flex-1 text-center">
                                        Claim This Bonus
                                    </a>
                                    <button onclick="closeModal('{{ $bonus->id }}')"
                                        class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold flex-1">
                                        Close
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-8 max-w-md w-full border border-gray-700 shadow-lg">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-900/20 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">New Bonuses Coming Soon</h2>
                <p class="text-gray-400 mb-6">We're preparing exciting new bonuses for you. Check back later!</p>
                <div class="animate-pulse inline-flex items-center text-yellow-400 text-lg font-semibold">
                    <span class="mr-2">Coming Soon</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById('modal-' + id).classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        // Add animation class
        setTimeout(() => {
            document.getElementById('modal-' + id).classList.add('opacity-100');
        }, 10);
    }

    function closeModal(id) {
        document.getElementById('modal-' + id).classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
            if (event.target === modal) {
                closeModal(modal.id.split('-')[1]);
            }
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('[id^="modal-"]').forEach(modal => {
                if (!modal.classList.contains('hidden')) {
                    closeModal(modal.id.split('-')[1]);
                }
            });
        }
    });
</script>

<style>
    .line-clamp-1 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }

    .line-clamp-2 {
        overflow: hidden;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    /* Enhanced CKEditor content styling */
    .prose-invert {
        --tw-prose-body: #d1d5db;
        --tw-prose-headings: #f9fafb;
        --tw-prose-lead: #9ca3af;
        --tw-prose-links: #f9fafb;
        --tw-prose-bold: #f9fafb;
        --tw-prose-counters: #9ca3af;
        --tw-prose-bullets: #4b5563;
        --tw-prose-hr: #374151;
        --tw-prose-quotes: #f9fafb;
        --tw-prose-quote-borders: #374151;
        --tw-prose-captions: #9ca3af;
        --tw-prose-code: #f9fafb;
        --tw-prose-pre-code: #d1d5db;
        --tw-prose-pre-bg: #1f2937;
        --tw-prose-th-borders: #4b5563;
        --tw-prose-td-borders: #374151;
    }

    .prose img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1.5rem 0;
    }

    .prose table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.875rem;
    }

    .prose table th,
    .prose table td {
        padding: 0.75rem;
        border: 1px solid #374151;
    }

    .prose table th {
        background-color: #1f2937;
        font-weight: 600;
        text-align: left;
    }

    .prose a {
        color: #60a5fa;
        text-decoration: underline;
        font-weight: 500;
    }

    .prose a:hover {
        color: #3b82f6;
    }

    .prose ul,
    .prose ol {
        padding-left: 1.5rem;
        margin: 1.25rem 0;
    }

    .prose li {
        margin: 0.5rem 0;
    }

    .prose h1,
    .prose h2,
    .prose h3,
    .prose h4 {
        font-weight: 700;
        margin: 2rem 0 1rem 0;
        line-height: 1.3;
    }

    .prose h1 {
        font-size: 1.875rem;
    }

    .prose h2 {
        font-size: 1.5rem;
    }

    .prose h3 {
        font-size: 1.25rem;
    }

    .prose blockquote {
        border-left: 4px solid #4b5563;
        padding-left: 1.5rem;
        margin: 1.5rem 0;
        font-style: italic;
    }

    .prose code {
        background-color: #1f2937;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .prose pre {
        background-color: #1f2937;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 1.5rem 0;
    }

    .prose pre code {
        background-color: transparent;
        padding: 0;
        border-radius: 0;
    }
</style>
@endsection
