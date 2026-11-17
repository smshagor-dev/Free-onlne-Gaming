<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('withdrawals.index', [
            'kycApproved' => (bool) $user->kyc_verified,
            'enabled' => (bool) config('withdrawals.enabled'),
            'availableBalance' => $user->available_balance ?? '0.00',
            'currency' => config('withdrawals.currency'),
            'minimum' => config('withdrawals.minimum'),
            'maximum' => config('withdrawals.maximum'),
            'requests' => WithdrawalRequest::query()->where('user_id', $user->id)
                ->latest()->paginate(15),
        ]);
    }

    public function store(Request $request, WithdrawalService $service): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'],
            'asset' => ['required', 'in:BTC,USDT'],
            'network' => ['required', 'in:bitcoin,trc20,erc20'],
            'destination' => [
                'required', 'string', 'min:20', 'max:128',
                'regex:/^[A-Za-z0-9]{20,128}$/D',
            ],
        ]);

        $user = $request->user();
        // Sanity-check the crypto address network family, without claiming
        // to validate full chain checksums or prevent irreversible mistakes.
        $address = $data['destination'];
        $validFormat = match ($data['network']) {
            'bitcoin' => preg_match('/^(bc1[a-z0-9]{11,87}|[13][a-km-zA-HJ-NP-Z1-9]{25,34})$/D', $address),
            'trc20' => preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/D', $address),
            'erc20' => preg_match('/^0x[a-fA-F0-9]{40}$/D', $address),
        };
        if (! $validFormat) {
            return back()->withErrors(['destination' => 'Wallet address format does not match the selected network.'])->withInput($request->except('destination'));
        }

        $service->create($user, $data['amount'], $data['asset'], $data['network'], $address);

        return redirect()->route('user.withdrawals.index')
            ->with('success', 'Withdrawal request submitted. Funds have been reserved pending administrative review.');
    }

    public function cancel(Request $request, string $reference, WithdrawalService $service): RedirectResponse
    {
        $record = WithdrawalRequest::query()
            ->where('user_id', $request->user()->id)
            ->where('reference', $reference)->firstOrFail();

        $service->cancel($record, $request->user());

        return redirect()->route('user.withdrawals.index')
            ->with('success', 'Pending withdrawal cancelled and available balance restored.');
    }
}
