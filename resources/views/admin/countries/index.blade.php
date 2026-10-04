@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">🌍 Countries & Currency</h2>

    <!-- 🔍 Search bar -->
    <div class="mb-4 flex justify-end">
        <input type="text" id="searchInput" 
            placeholder="Search country, currency, symbol..." 
            class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
    </div>


    <div class="overflow-x-auto bg-white shadow-md rounded-xl">
        <table id="countriesTable" class="min-w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-100 text-xs uppercase text-gray-700">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Currency</th>
                    <th class="px-6 py-3">Currency Name</th>
                    <th class="px-6 py-3">Symbol</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Change Status</th>
                    <th class="px-6 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($countries as $country)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $country->name }}</td>
                        <td class="px-6 py-4">{{ $country->currency }}</td>
                        <td class="px-6 py-4">{{ $country->currency_name }}</td>
                        <td class="px-6 py-4">{{ $country->currency_symbol }}</td>
                        <td class="px-4 py-2">
                            <span id="status-badge-{{ $country->id }}"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $country->status == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $country->status == 1 ? 'Active' : 'Deactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button onclick="toggleStatus({{ $country->id }}, {{ $country->status }})"
                                id="status-btn-{{ $country->id }}"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                {{ $country->status == 1 ? 'Deactivate' : 'Activate' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.countries.editCurrency', $country->id) }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleStatus(id, currentStatus) {
    let newStatus = currentStatus === 1 ? 0 : 1;

    fetch("{{ route('admin.countries.updateStatus', ':id') }}".replace(':id', id), {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            let badge = document.getElementById(`status-badge-${id}`);
            badge.innerText = data.label;
            badge.className =
                "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium " +
                (data.status == 1 ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");

            let btn = document.getElementById(`status-btn-${id}`);
            btn.innerText = data.status == 1 ? "Deactivate" : "Activate";
            btn.setAttribute("onclick", `toggleStatus(${id}, ${data.status})`);
        }
    })
    .catch(err => console.error("AJAX Error:", err));
}

// 🔍 Search filter
document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#countriesTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});
</script>

@endsection
