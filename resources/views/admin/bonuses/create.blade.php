@extends('layouts.admin')

@section('title', 'Create Bonus')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-card {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }
    .form-section {
        background: linear-gradient(90deg, #f7fafc 0%, #edf2f7 100%);
        border-left: 4px solid #4299e1;
    }
    .preview-image {
        transition: all 0.3s ease;
        border: 2px dashed #cbd5e0;
    }
    .preview-image:hover {
        transform: scale(1.02);
        border-color: #4299e1;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d2d6dc;
        border-radius: 6px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .date-fields {
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .hidden-fields {
        opacity: 0;
        height: 0;
        margin: 0;
        padding: 0;
        border: none;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Create New Bonus</h1>
        <a href="{{ route('admin.bonuses.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center bg-white py-2 px-4 rounded-lg shadow-sm border border-gray-200 transition duration-200">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Bonuses
        </a>
    </div>

    <div class="form-card bg-white p-6 mb-6">
        <div class="form-section px-4 py-3 mb-6 rounded">
            <h2 class="text-lg font-semibold text-gray-800">Bonus Information</h2>
            <p class="text-sm text-gray-600">Provide basic details about the bonus</p>
        </div>

        <form action="{{ route('admin.bonuses.store') }}" method="POST" enctype="multipart/form-data" id="bonusForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200 @error('title') border-red-500 @enderror" 
                               placeholder="Enter bonus title" required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                        <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200" 
                               placeholder="Enter bonus subtitle (optional)">
                    </div>

                    <div>
                        <label for="bonus_amount" class="block text-sm font-medium text-gray-700 mb-2">Bonus Amount <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" step="0.01" min="0" name="bonus_amount" id="bonus_amount" value="{{ old('bonus_amount') }}" 
                                   class="pl-7 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200 @error('bonus_amount') border-red-500 @enderror" 
                                   placeholder="0.00" required>
                        </div>
                        @error('bonus_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bonus_type" class="block text-sm font-medium text-gray-700 mb-2">Bonus Type <span class="text-red-500">*</span></label>
                        <select name="bonus_type" id="bonus_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200 @error('bonus_type') border-red-500 @enderror" required>
                            <option value="">Select Bonus Type</option>
                            <option value="permanent" {{ old('bonus_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                            <option value="offer" {{ old('bonus_type') == 'offer' ? 'selected' : '' }}>Limited Time Offer</option>
                            <option value="daily" {{ old('bonus_type') == 'daily' ? 'selected' : '' }}>Daily Bonus</option>
                        </select>
                        @error('bonus_type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Bonus Image</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="photo" class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-white hover:border-blue-500 transition duration-200">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-xs text-gray-500">JPG, PNG (Max. 2MB)</p>
                                </div>
                                <input id="photo" name="photo" type="file" class="hidden" accept="image/jpeg,image/jpg,image/png" />
                            </label>
                        </div>
                        <div id="image-preview" class="mt-3 hidden">
                            <p class="text-sm text-gray-700 mb-2">Image Preview:</p>
                            <img id="preview" class="preview-image h-40 rounded-lg mx-auto" />
                        </div>
                        @error('photo')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="date-fields-container" class="date-fields {{ old('bonus_type') == 'permanent' ? 'hidden-fields' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200">
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200" required>
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div>
                            <label for="is_featured" class="block text-sm font-medium text-gray-700 mb-2">Featured <span class="text-red-500">*</span></label>
                            <select name="is_featured" id="is_featured" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200" required>
                                <option value="1" {{ old('is_featured') == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_featured', 0) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <div class="form-section px-4 py-3 mb-4 rounded">
                    <h2 class="text-lg font-semibold text-gray-800">Bonus Description</h2>
                    <p class="text-sm text-gray-600">Provide detailed information about the bonus</p>
                </div>
                
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
            </div>

            <div class="pt-8 mt-8 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.bonuses.index') }}" class="bg-white py-2 px-6 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
                        Create Bonus
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Initialize CKEditor
    CKEDITOR.replace('description', {
        toolbar: [
            { name: 'document', items: [ 'Source', '-', 'NewPage', 'Preview', '-', 'Templates' ] },
            { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
            { name: 'styles', items: [ 'Styles', 'Format' ] },
            { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ] },
            { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl' ] },
            { name: 'links', items: [ 'Link', 'Unlink' ] },
            { name: 'insert', items: [ 'Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak' ] },
            { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
            { name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] }
        ],
        height: 300
    });

    // Initialize Select2
    $(document).ready(function() {
        $('#bonus_type').select2({
            placeholder: "Select Bonus Type",
            allowClear: false
        });
        
        $('#status').select2({
            minimumResultsForSearch: -1
        });
        
        $('#is_featured').select2({
            minimumResultsForSearch: -1
        });
        
        // Handle bonus type change to show/hide date fields
        $('#bonus_type').on('change', function() {
            toggleDateFields($(this).val());
        });
        
        // Initialize date fields visibility based on current selection
        toggleDateFields($('#bonus_type').val());
    });

    // Toggle date fields based on bonus type
    function toggleDateFields(bonusType) {
        const dateFields = $('#date-fields-container');
        
        if (bonusType === 'permanent') {
            dateFields.addClass('hidden-fields');
            // Clear date values when hidden
            $('#start_date').val('');
            $('#end_date').val('');
        } else {
            dateFields.removeClass('hidden-fields');
        }
    }

    // Image preview functionality
    document.getElementById('photo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview');
                preview.src = e.target.result;
                document.getElementById('image-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }
    });

    // Form validation
    document.getElementById('bonusForm').addEventListener('submit', function(e) {
        let isValid = true;
        const title = document.getElementById('title');
        const bonusAmount = document.getElementById('bonus_amount');
        const bonusType = document.getElementById('bonus_type');
        
        // Reset error states
        [title, bonusAmount, bonusType].forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        // Validate title
        if (!title.value.trim()) {
            title.classList.add('border-red-500');
            isValid = false;
        }
        
        // Validate bonus amount
        if (!bonusAmount.value || parseFloat(bonusAmount.value) <= 0) {
            bonusAmount.classList.add('border-red-500');
            isValid = false;
        }
        
        // Validate bonus type
        if (!bonusType.value) {
            bonusType.classList.add('border-red-500');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>
@endsection