<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $params = $request->validate([
            'status' => ['sometimes', 'nullable', 'in:pending,approved,rejected,cancelled,paid_recorded'],
        ]);
        $selected = $params['status'] ?? '';

        return view('admin.withdrawals.index', [
            'requests' => WithdrawalRequest::query()
                ->with('user:id,name,email,kyc_verified')
                ->when($selected !== '', fn ($query) => $query->where('status', $selected))
                ->latest()->paginate(25)->withQueryString(),
            'selected' => $selected,
        ]);
    }

    public function show(WithdrawalRequest $withdrawal): View
    {
        return view('admin.withdrawals.show', [
            'withdrawal' => $withdrawal->load(['user:id,name,email,kyc_verified,balance,available_balance', 'reviewer:id,name']),
        ]);
    }

    public function approve(WithdrawalRequest $withdrawal, WithdrawalService $service): RedirectResponse
    {
        $service->approve($withdrawal, auth('admin')->user());

        return redirect()->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Withdrawal approved for separate manual payout. No funds have been transferred.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal, WithdrawalService $service): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $service->reject($withdrawal, auth('admin')->user(), $data['reason']);

        return redirect()->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'Withdrawal rejected. Reserved available funds were released.');
    }

    public function markPaid(Request $request, WithdrawalRequest $withdrawal, WithdrawalService $service): RedirectResponse
    {
        $data = $request->validate([
            'txid' => ['required', 'string', 'min:12', 'max:160', 'regex:/^[A-Za-z0-9_-]{12,160}$/D'],
            'confirm_external_transfer' => ['required', 'accepted'],
        ]);

        $service->recordExternalTransfer($withdrawal, auth('admin')->user(), $data['txid']);

        return redirect()->route('admin.withdrawals.show', $withdrawal)
            ->with('success', 'External transfer reference recorded. This status is operator-attested, not blockchain-verified.');
    }
}
