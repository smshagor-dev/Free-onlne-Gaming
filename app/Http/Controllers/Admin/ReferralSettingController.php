<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReferralSetting;

class ReferralSettingController extends Controller
{
    public function index()
    {
        $settings = ReferralSetting::all();
        return view('admin.referral.index', compact('settings'));
    }

    public function create()
    {
        return view('admin.referral.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'register_user' => 'required|integer',
            'total_deposit' => 'required|numeric',
            'commission' => 'required|numeric',
            'level' => 'required|string|max:255',
        ]);

        ReferralSetting::create($request->all());
        return redirect()->route('admin.referral.index')->with('success', 'Referral setting created.');
    }

    public function edit($id)
    {
        $referralSetting = ReferralSetting::findOrFail($id);
        return view('admin.referral.edit', compact('referralSetting'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'register_user' => 'required|integer',
            'total_deposit' => 'required|numeric',
            'commission' => 'required|numeric',
            'level' => 'required|string|max:255',
        ]);

        $setting = ReferralSetting::findOrFail($id);
        $setting->update($request->all());

        return redirect()->route('admin.referral.index')->with('success', 'Referral setting updated.');
    }

    public function destroy(ReferralSetting $referralSetting)
    {
        $referralSetting->delete();
        return redirect()->route('admin.referral.index')->with('success', 'Referral setting deleted.');
    }


}
