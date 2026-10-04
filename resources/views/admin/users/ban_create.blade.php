@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto p-4 bg-white shadow rounded">
    <h2 class="text-2xl font-semibold mb-4">Ban User: {{ $user->name }}</h2>

    <form action="{{ route('admin.users.ban.store', $user->id) }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Ban Reason</label>
            <textarea 
                name="ban_reason" 
                required 
                class="w-full border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows="4"
            >{{ old('ban_reason') }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Ban Documents Titles (optional)</label>

            <div id="documents-wrapper" class="space-y-2">
                <div class="flex space-x-2">
                    <input type="text" name="documents[]" placeholder="Document title" 
                        class="flex-1 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" class="remove-btn bg-red-500 text-white px-3 rounded">Remove</button>
                </div>
            </div>

            <button type="button" id="add-document" 
                class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                + Add Document
            </button>
        </div>

        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">
            Ban User
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-document');
    const wrapper = document.getElementById('documents-wrapper');

    addBtn.addEventListener('click', function() {
        const div = document.createElement('div');
        div.classList.add('flex', 'space-x-2');

        div.innerHTML = `
            <input type="text" name="documents[]" placeholder="Document title" 
                class="flex-1 border border-gray-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="button" class="remove-btn bg-red-500 text-white px-3 rounded">Remove</button>
        `;

        wrapper.appendChild(div);

        // Add remove event
        div.querySelector('.remove-btn').addEventListener('click', function() {
            div.remove();
        });
    });

    // Remove initial remove button if needed
    wrapper.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.parentElement.remove();
        });
    });
});
</script>
@endsection
