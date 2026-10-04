@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-full mx-auto">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $page->title }}</h1>
            <div class="text-sm text-gray-400">
                Last updated: {{ $page->updated_at->format('F j, Y') }}
            </div>
        </div>

        <!-- Page Content -->
        <div class="bg-[#0b141d] rounded-lg shadow-md overflow-hidden w-full">
            <div class="p-6 md:p-8 w-full">
                <div class="ck-content w-full max-w-full text-gray-300">
                    {!! \App\Support\SafeHtml::clean($page->content) !!}
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-8 text-center">
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-md transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back
            </a>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* CKEditor Content Styling - Dark Theme */
    .ck-content {
        font-size: 1rem;
        line-height: 1.8 !important; /* Equal line height for all elements */
        color: #e5e7eb !important;
    }
    
    .ck-content h2, 
    .ck-content h3, 
    .ck-content h4,
    .ck-content h5,
    .ck-content h6 {
        font-weight: 600;
        margin-top: 1.5em;
        margin-bottom: 0.75em;
        color: #ffffff !important;
        line-height: 1.8 !important;
    }
    
    .ck-content h2 { font-size: 1.5rem; }
    .ck-content h3 { font-size: 1.25rem; }
    .ck-content h4 { font-size: 1.125rem; }
    
    .ck-content p {
        margin-bottom: 1.25em;
        line-height: 1.8 !important;
    }
    
    .ck-content a {
        color: #60a5fa !important;
        text-decoration: underline;
    }
    
    .ck-content a:hover {
        color: #3b82f6 !important;
    }
    
    .ck-content ul, 
    .ck-content ol {
        margin-bottom: 1.25em;
        padding-left: 1.5em;
        line-height: 1.8 !important;
    }
    
    .ck-content ul {
        list-style-type: disc;
    }
    
    .ck-content ol {
        list-style-type: decimal;
    }
    
    .ck-content blockquote {
        border-left: 4px solid #4b5563;
        padding-left: 1em;
        font-style: italic;
        color: #9ca3af;
        margin: 1.5em 0;
        line-height: 1.8 !important;
    }
    
    .ck-content table {
        border-collapse: collapse;
        width: 100%;
        margin: 1.5em 0;
    }
    
    .ck-content table th,
    .ck-content table td {
        border: 1px solid #4b5563;
        padding: 0.75em;
        line-height: 1.8 !important;
    }
    
    .ck-content table th {
        background-color: #1f2937;
        font-weight: 600;
        text-align: left;
        color: #ffffff;
    }
    
    .ck-content table td {
        background-color: #111827;
        color: #e5e7eb;
    }
    
    .ck-content img {
        max-width: 100%;
        height: auto;
        margin: 1.5em 0;
        border-radius: 0.375rem;
    }
    
    .ck-content .image-style-side {
        float: right;
        margin-left: 1.5em;
        max-width: 50%;
    }
    
    /* Code blocks */
    .ck-content pre {
        background-color: #1f2937 !important;
        padding: 1em;
        border-radius: 0.375rem;
        overflow-x: auto;
        margin: 1.5em 0;
        line-height: 1.8 !important;
    }
    
    .ck-content code {
        font-family: monospace;
        background-color: #1f2937;
        padding: 0.2em 0.4em;
        border-radius: 0.25rem;
    }
</style>
@endsection
