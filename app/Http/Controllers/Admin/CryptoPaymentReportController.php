<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CryptoPaymentReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['sometimes', 'nullable', 'in:pending,processing,settled,expired,invalid,failed,review'],
        ]);

        $payments = CryptoPayment::query()
            ->with('user:id,name,email')
            ->when(filled($filters['status'] ?? null),
                fn ($query) => $query->where('status', $filters['status']))
            ->latest()->paginate(25)->withQueryString();

        $stats = CryptoPayment::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.crypto.index', compact('payments', 'stats'));
    }
}
