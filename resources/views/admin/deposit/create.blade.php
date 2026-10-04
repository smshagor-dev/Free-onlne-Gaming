@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Gateway</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.deposit-settings.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-medium text-gray-900">Basic Information</h2>
                    
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-500 mb-1">Gateway Name</label>
                        <input type="text" name="name" id="name" required 
                               class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="image" class="block text-xs font-medium text-gray-500 mb-1">Gateway Image</label>
                        <input type="file" name="image" id="image" 
                               class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="currency" class="block text-xs font-medium text-gray-500 mb-1">Currency</label>
                            <input type="text" name="currency" id="currency" required 
                                   class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="symbol" class="block text-xs font-medium text-gray-500 mb-1">Symbol</label>
                            <input type="text" name="symbol" id="symbol" required 
                                   class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="min_amount" class="block text-xs font-medium text-gray-500 mb-1">Minimum Amount</label>
                            <input type="number" step="0.01" name="min_amount" id="min_amount" required 
                                   class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="max_amount" class="block text-xs font-medium text-gray-500 mb-1">Maximum Amount</label>
                            <input type="number" step="0.01" name="max_amount" id="max_amount" required 
                                   class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="instruction" class="block text-xs font-medium text-gray-500 mb-1">Instructions (Optional)</label>
                        <textarea name="instruction" id="instruction" rows="2" 
                                  class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="withdraw_instruction" class="block text-xs font-medium text-gray-500 mb-1">Withdrawal Instructions (Optional)</label>
                        <textarea name="withdraw_instruction" id="withdraw_instruction" rows="2" 
                                  class="block w-full px-3 py-2 text-sm text-gray-700 bg-white border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <!-- Required Documents -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Required Documents</h2>
                    
                    <div id="documents-container" class="space-y-3">
                        <!-- Documents will be added here dynamically -->
                    </div>

                    <button type="button" id="add-document-btn" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">
                        + Add Document
                    </button>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.deposit-settings.index') }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-1 focus:ring-offset-1 focus:ring-blue-500">
                    Save Gateway
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('documents-container');
        const addBtn = document.getElementById('add-document-btn');
        let docCount = container.querySelectorAll('.document-entry').length;

        function addDocumentEntry() {
            const docDiv = document.createElement('div');
            docDiv.className = 'document-entry p-3 border border-gray-200 rounded-md bg-gray-50';
            docDiv.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-medium text-gray-500">Document #${docCount + 1}</span>
                    <button type="button" class="remove-document text-xs text-red-600 hover:text-red-800 focus:outline-none">
                        Remove
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Deposit Section -->
                    <div class="p-2 border border-blue-100 rounded bg-blue-50">
                        <h5 class="text-xs font-medium text-blue-600 mb-1">Deposit</h5>
                        <div class="mb-2">
                            <label class="block text-xs text-gray-500 mb-1">Document Name</label>
                            <input type="text" name="documents[${docCount}][name]" required
                                   class="block w-full px-2 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Document Type</label>
                            <select name="documents[${docCount}][type]" required
                                    class="block w-full px-2 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="text">Text</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                    </div>

                    <!-- Withdrawal Section -->
                    <div class="p-2 border border-green-100 rounded bg-green-50">
                        <h5 class="text-xs font-medium text-green-600 mb-1">Withdrawal</h5>
                        <div class="mb-2">
                            <label class="block text-xs text-gray-500 mb-1">Document Name</label>
                            <input type="text" name="documents[${docCount}][name_withdraw]"
                                   class="block w-full px-2 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Document Type</label>
                            <select name="documents[${docCount}][type_withdrew]"
                                    class="block w-full px-2 py-1.5 text-xs text-gray-700 bg-white border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500">
                                <option value="text">Text</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(docDiv);
            docCount++;
        }

        // Add initial event listener
        addBtn.addEventListener('click', addDocumentEntry);

        // Event delegation for remove buttons
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-document')) {
                e.target.closest('.document-entry').remove();
            }
        });
    });
</script>
@endsection