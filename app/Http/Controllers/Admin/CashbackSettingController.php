<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashbackSetting;
use App\Models\Level;
use App\Services\CashbackService;
use Illuminate\Support\Facades\Auth;

class CashbackSettingController extends Controller
{
    public function index()
    {
        $cashbacks = CashbackSetting::with('level')->get();
        return view('admin.cashback.index', compact('cashbacks'));
    }

    public function create()
    {
        $levels = Level::all();
        return view('admin.cashback.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cashback' => 'required|array',
            'cashback.*.cashback_percentage' => 'required|numeric|min:5|max:100',
            'cashback.*.lose_calculation'   => 'required|numeric',
            'cashback.*.wager'              => 'required|numeric|min:5|max:15',
            'cashback.*.playing_time'       => 'required|integer',
            'cashback.*.activation_days'    => 'required|string',
            'cashback.*.maximum_claim'      => 'required|numeric|min:1|max:5',
        ]);

        foreach ($validated['cashback'] as $levelId => $data) {
            CashbackSetting::updateOrCreate(
                ['level_id' => $levelId],
                [
                    'cashback_percentage' => $data['cashback_percentage'],
                    'lose_calculation'    => $data['lose_calculation'],
                    'wager'               => $data['wager'],
                    'playing_time'        => $data['playing_time'],
                    'activation_days'     => $data['activation_days'],
                    'maximum_claim'       => $data['maximum_claim'],
                    'level_id'            => $levelId,
                ]
            );
        }

        return redirect()->route('admin.cashback.index')
            ->with('success', 'Cashback Settings saved successfully!');
    }


    public function edit(CashbackSetting $cashback)
    {
        $levels = Level::all();
        return view('admin.cashback.edit', compact('cashback', 'levels'));
    }

    public function update(Request $request, CashbackSetting $cashback)
    {
        $validated = $request->validate([
            'cashback_percentage' => 'required|numeric|min:5|max:100',
            'lose_calculation'    => 'required|numeric',
            'wager'               => 'required|numeric|min:5|max:15',
            'playing_time'        => 'required|integer',
            'activation_days'     => 'required|string',
            'maximum_claim'       => 'required|numeric|min:1|max:5',
            'level_id'            => 'required|exists:levels,id',
        ]);

        $cashback->update($validated);

        return redirect()->route('admin.cashback.index')
            ->with('success', 'Cashback Setting Updated Successfully!');
    }



    public function destroy(CashbackSetting $cashback)
    {
        $cashback->delete();
        return redirect()->route('admin.cashback.index')->with('success', 'Cashback Setting Deleted Successfully!');
    }

    public function viewCashback()
    {
        $user = Auth::user();

        $cashbackSetting = CashbackSetting::where('level_id', $user->level_id)
                                ->first();

        $availableCashback = min(
            $user->cashback_balance, 
            $cashbackSetting->maximum_claim ?? $user->cashback_balance
        );

        return view('user.cashback.index', compact('user', 'cashbackSetting', 'availableCashback'));
    }


    public function claim(CashbackService $service)
    {
        $user = Auth::user();
        $result = $service->getCashback($user);

        return response()->json($result);
    }
}
