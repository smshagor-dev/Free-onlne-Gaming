<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use Illuminate\Http\Request;
use App\Models\UserDeposit;
use App\Services\BonusService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\DepositSetting;
use App\Models\BonusUser;

class BonusController extends Controller
{
    public function index()
    {
        $bonuses = Bonus::latest()->paginate(10);
        return view('admin.bonuses.index', compact('bonuses'));
    }

    public function create()
    {
        return view('admin.bonuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'bonus_amount' => 'required|numeric',
            'bonus_type'   => 'required|in:permanent,offer,daily',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description'  => 'nullable|string',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'status'       => 'required|boolean',
            'is_featured'  => 'required|boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('bonuses', 'public');
        }

        Bonus::create($validated);

        return redirect()->route('admin.bonuses.index')->with('success', 'Bonus created successfully!');
    }

    public function edit($id)
    {
        $bonus = Bonus::findOrFail($id);
        return view('admin.bonuses.edit', compact('bonus'));
    }

    public function update(Request $request, $id)
    {
        $bonus = Bonus::findOrFail($id);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'bonus_amount' => 'required|numeric',
            'bonus_type'   => 'required|in:permanent,offer,daily',
            'photo'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description'  => 'nullable|string',
            'start_date'   => 'nullable|date',
            'end_date'     => 'nullable|date|after_or_equal:start_date',
            'status'       => 'required|boolean',
            'is_featured'  => 'required|boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($bonus->photo) {
                Storage::disk('public')->delete($bonus->photo);
            }
            $validated['photo'] = $request->file('photo')->store('bonuses', 'public');
        }

        $bonus->update($validated);

        return redirect()->route('admin.bonuses.index')->with('success', 'Bonus updated successfully!');
    }

    public function destroy($id)
    {
        $bonus = Bonus::findOrFail($id);

        if ($bonus->photo) {
            Storage::disk('public')->delete($bonus->photo);
        }

        $bonus->delete();

        return redirect()->route('admin.bonuses.index')->with('success', 'Bonus deleted successfully!');
    }

    public function view()
    {
        $bonuses = Bonus::where('status', 1)->get();
        return view('bonuses.bonus', compact('bonuses'));
    }


    /**
     * Claim Welcome Bonus
     */
    public function claimWelcome()
    {
        $user = Auth::user();
        $service = new BonusService();

        $setting = DepositSetting::where('bonus_type', 'Welcome Bonus')->first();

        if (!$setting) {
            return redirect()->route('my.bonus')->with('error', 'No Welcome Bonus setting available.');
        }

        // Check if user already claimed Welcome Bonus
        $hasBonus = BonusUser::where('user_id', $user->id)
            ->where('bonus_type', 'Welcome Bonus')
            ->exists();

        if ($hasBonus) {
            return redirect()->route('my.bonus')->with('info', 'You already claimed your Welcome Bonus.');
        }

        $service->giveWelcomeBonus($user);

        // Clear popup session
        session()->forget('show_bonus_popup');

        return redirect()->route('my.bonus')->with('success', 'Welcome Bonus successfully claimed!');
    }

    /**
     * Claim First Deposit Bonus
     */
    public function claimFirstDeposit()
    {
        $user = Auth::user();
        $service = new BonusService();

        $setting = DepositSetting::where('bonus_type', 'First Deposit Bonus')->first();

        if (!$setting) {
            return redirect()->route('user.deposit.index')->with('error', 'No First Deposit Bonus setting available.');
        }

        // Check if user already claimed First Deposit Bonus
        $hasBonus = BonusUser::where('user_id', $user->id)
            ->where('bonus_type', 'First Deposit Bonus')
            ->exists();

        if ($hasBonus) {
            return redirect()->route('my.bonus')->with('info', 'You already claimed your First Deposit Bonus.');
        }

        return redirect()->route('user.deposit.index')->with('message', 'Make your first deposit to claim your First Deposit Bonus!');
    }


    public function bonus()
    {
        $user = Auth::user();
        $bonusSettings = DepositSetting::whereIn('bonus_type', ['First Deposit Bonus', 'Welcome Bonus'])->get();

        return view('components.bonus-popup', compact('bonusSettings'));
    }
}
