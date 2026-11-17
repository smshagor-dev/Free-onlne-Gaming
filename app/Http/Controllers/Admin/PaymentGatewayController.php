<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    /**
     * Configuration inventory only. This does NOT initiate charges,
     * accept deposits, process callbacks or settle casino wagers.
     */
    public function index(): View
    {
        $gateways = Gateway::query()->orderBy('name')->paginate(20);

        return view('admin.gateways.index', compact('gateways'));
    }

    public function create(): View
    {
        return view('admin.gateways.form', ['gateway' => new Gateway()]);
    }

    public function edit(Gateway $gateway): View
    {
        return view('admin.gateways.form', compact('gateway'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        // Existing gateways may already be enabled; new entries are held
        // inactive until a provider integration and merchant eligibility
        // have been independently confirmed.
        $data['status'] = false;
        Gateway::query()->create($data);

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway configuration saved as inactive. No payments are enabled.');
    }

    public function update(Request $request, Gateway $gateway): RedirectResponse
    {
        $data = $this->validated($request);
        // This screen is metadata only and must not change any gateway's
        // ability to process payments. Provider activation is out of scope.
        $gateway->update($data);

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway configuration updated. No live payment processing was changed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'symbol' => ['required', 'string', 'max:16'],
            'min_amount' => ['required', 'numeric', 'between:0.01,999999999.99'],
            'max_amount' => ['required', 'numeric', 'between:0.01,999999999.99', 'gte:min_amount'],
            'instruction' => ['nullable', 'string', 'max:4000'],
            'withdraw_instruction' => ['nullable', 'string', 'max:4000'],
        ]);

        $data['currency'] = strtoupper($data['currency']);

        return $data;
    }
}
