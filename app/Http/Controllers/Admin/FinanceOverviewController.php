<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UserDeposit;
use App\Models\UserWithdrew;
use Illuminate\Http\Request;

class FinanceOverviewController extends Controller
{
    /**
     * Read-only financial oversight. Settlements, refunds and ledger changes
     * must use audited business workflows, never direct status toggles.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'kind' => ['sometimes', 'in:deposits,withdrawals,transactions'],
            'search' => ['sometimes', 'nullable', 'string', 'max:80'],
        ]);

        $kind = $validated['kind'] ?? 'deposits';
        $search = trim($validated['search'] ?? '');
        $model = match ($kind) {
            'withdrawals' => UserWithdrew::class,
            'transactions' => Transaction::class,
            default => UserDeposit::class,
        };

        $query = $model::query()->with('user:id,name,email');
        if ($search !== '') {
            $query->where('transaction_number', 'like', '%'.addcslashes($search, '%_\\').'%');
        }

        $records = $query->latest()->paginate(25)->withQueryString();
        $totals = [
            'deposits' => ['count' => UserDeposit::count(), 'amount' => (float) UserDeposit::sum('amount')],
            'withdrawals' => ['count' => UserWithdrew::count(), 'amount' => (float) UserWithdrew::sum('amount')],
            'transactions' => ['count' => Transaction::count(), 'amount' => (float) Transaction::sum('amount')],
        ];

        return view('admin.finance.index', compact('kind', 'search', 'records', 'totals'));
    }
}
