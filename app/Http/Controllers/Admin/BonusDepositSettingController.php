<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepositSetting;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CheckBonus;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\BonusUser;
use App\Models\UserDeposit;


class BonusDepositSettingController extends Controller
{
    public function index()
    {
        $settings = DepositSetting::all();
        return view('admin.deposit_settings.index', compact('settings'));
    }

    public function create()
    {
        $gateways = Gateway::all();

        $gatewayGroups = $gateways->groupBy('name')->map(function ($group) {
            return $group->pluck('id')->toArray(); // IDs under the same name
        });


        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $bonusTypes = [
            'First Deposit Bonus',
            'Provider',
            'Day',
            'Birthday Bonus',
            'Welcome Bonus',
            'Special Bonus'
        ];
        return view('admin.deposit_settings.create', compact('gatewayGroups', 'days', 'bonusTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'bonus_type' => 'nullable|in:First Deposit Bonus,Provider,Day,Birthday Bonus,Welcome Bonus,Special Bonus',
            'providers' => 'nullable|array',
            'days' => 'nullable|array',
            'wager' => 'nullable|numeric',
            'bonus_percentage' => 'nullable|numeric',
            'minimum_bonus' => 'nullable|numeric',
            'bonus_time' => 'nullable|in:12 hour,24 hour,3 days,7 days,15 days,1 month',
            'maximum_claim_in_a_day' => 'nullable|integer',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('deposit_settings', 'public');
        }

        $data['providers'] = $data['providers'] ?? [];
        $data['days'] = $data['days'] ?? [];

        DepositSetting::create($data);

        return redirect()->route('admin.depositsettings.index')->with('success', 'Deposit setting created successfully.');
    }

    public function edit(DepositSetting $depositSetting)
    {
        $gateways = Gateway::all();

        $gatewayGroups = $gateways->groupBy('name')->map(function ($group) {
            return $group->pluck('id')->toArray();
        });

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $bonusTypes = [
            'First Deposit Bonus',
            'Provider',
            'Day',
            'Birthday Bonus',
            'Welcome Bonus',
            'Special Bonus'
        ];

        return view('admin.deposit_settings.edit', compact('depositSetting', 'gatewayGroups', 'days', 'bonusTypes'));
    }


    public function update(Request $request, DepositSetting $depositSetting)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'bonus_type' => 'nullable|in:First Deposit Bonus,Provider,Day,Birthday Bonus,Welcome Bonus,Special Bonus',
            'providers' => 'nullable|array',
            'days' => 'nullable|array',
            'wager' => 'nullable|numeric',
            'bonus_percentage' => 'nullable|numeric',
            'minimum_bonus' => 'nullable|numeric',
            'bonus_time' => 'nullable|in:12 hour,24 hour,3 days,7 days,15 days,1 month',
            'maximum_claim_in_a_day' => 'nullable|integer',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($depositSetting->photo) Storage::disk('public')->delete($depositSetting->photo);
            $data['photo'] = $request->file('photo')->store('deposit_settings', 'public');
        }

        $data['providers'] = $data['providers'] ?? [];
        $data['days'] = $data['days'] ?? [];

        $depositSetting->update($data);

        return redirect()->route('admin.depositsettings.index')->with('success', 'Deposit setting updated successfully.');
    }

    public function destroy(DepositSetting $depositSetting)
    {
        if ($depositSetting->photo) Storage::disk('public')->delete($depositSetting->photo);
        $depositSetting->delete();

        return redirect()->route('admin.depositsettings.index')->with('success', 'Deposit setting deleted successfully.');
    }

    public function View()
    {

        $depositSettings = DepositSetting::all();

        $depositSettings->transform(function ($item) {
            $item->providers = is_string($item->providers) ? json_decode($item->providers, true) : ($item->providers ?? []);
            $item->days = is_string($item->days) ? json_decode($item->days, true) : ($item->days ?? []);
            
            $item->providers = \App\Models\Gateway::whereIn('id', $item->providers)->pluck('name')->toArray();
            
            return $item;
        });

        return view('bonuses.promotion', compact('depositSettings'));
    }


    public function showBonus()
    {
        $userId = auth::id();
        $bonus = CheckBonus($userId);

        $user = User::select('id', 'name', 'bonus_balance')
        ->where('id', $userId)
        ->first();


        return view('user.bonus', compact('bonus', 'user'));
    }
    
        public function active(Request $request)
    {
        $search = $request->get('search');

        $bonuses = BonusUser::with(['depositSetting', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('user_id', $search) // exact user_id match
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        // Filter active based on bonus_time
        $bonuses->getCollection()->transform(function ($bonusUser) {
            $ds = $bonusUser->depositSetting;
            if (!$ds) {
                $bonusUser->is_active = false;
                return $bonusUser;
            }

            $bonusTime = $this->parseBonusTime($ds->bonus_time);

            $activeUntil = Carbon::parse($bonusUser->created_at)
                ->addMinutes($bonusTime * 60);

            $bonusUser->is_active = now()->lessThanOrEqualTo($activeUntil);

            return $bonusUser;
        });

        $bonuses->setCollection(
            $bonuses->getCollection()->filter(fn($b) => $b->is_active)->values()
        );

        return view('bonuses.active', compact('bonuses', 'search'));
    }

    // Expired Bonuses (All Users)
    public function expired(Request $request)
    {
        $search = $request->get('search');

        $bonuses = BonusUser::with(['depositSetting', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('user_id', $search)
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        // Filter expired
        $bonuses->getCollection()->transform(function ($bonusUser) {
            $ds = $bonusUser->depositSetting;
            if (!$ds) {
                $bonusUser->is_active = false;
                return $bonusUser;
            }

            $bonusTime = $this->parseBonusTime($ds->bonus_time);

            $activeUntil = Carbon::parse($bonusUser->created_at)
                ->addMinutes($bonusTime * 60);

            $bonusUser->is_active = now()->lessThanOrEqualTo($activeUntil);

            return $bonusUser;
        });

        $bonuses->setCollection(
            $bonuses->getCollection()->reject(fn($b) => $b->is_active)->values()
        );

        return view('bonuses.expired', compact('bonuses', 'search'));
    }
    
    protected function parseBonusTime($time)
    {
        if (is_numeric($time)) {
            return (int) $time;
        }
    
        if (is_string($time)) {
            preg_match('/(\d+)/', $time, $matches);
            return isset($matches[1]) ? (int) $matches[1] : 0;
        }
    
        return 0;
    }
    
    public function searchUser(Request $request)
    {
        $query = $request->get('query');

        $user = User::where('user_id', $query)
            ->orWhere('username', 'LIKE', "%$query%")
            ->orWhere('email', 'LIKE', "%$query%")
            ->first();

        if (!$user) {
            return back()->with('error', 'User not found!');
        }

        // Active bonus check
        $activeBonuses = BonusUser::with('depositSetting')
            ->where('user_id', $user->id)
            ->get()
            ->filter(function ($bonusUser) {
                $ds = $bonusUser->depositSetting;
                if (!$ds) return false;

                $bonusTime = is_numeric($ds->bonus_time) ? (int) $ds->bonus_time : 0;
                $activeUntil = Carbon::parse($bonusUser->created_at)->addHours($bonusTime);

                return now()->lessThanOrEqualTo($activeUntil);
            });

        $depositSettings = DepositSetting::all();
        $userDeposits = UserDeposit::where('user_id', $user->id)->get();

        return view('bonuses.send', compact('user', 'activeBonuses', 'depositSettings', 'userDeposits'));
    }

    public function sendBonus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'deposit_setting_id' => 'required|exists:deposit_settings,id',
            'bonus_amount' => 'required|numeric|min:1',
        ]);

        $ds = DepositSetting::findOrFail($request->deposit_setting_id);

        // Save Bonus
        $bonus = BonusUser::create([
            'user_id' => $request->user_id,
            'deposit_setting_id' => $ds->id,
            'bonus_type' => $ds->bonus_type,
            'user_deposit_id' => $request->user_deposit_id ?? null,
            'bonus_amount' => $request->bonus_amount,
            'deposit_amount' => $request->deposit_amount ?? 0,
        ]);

        // Update User Bonus Balance
        $user = User::find($request->user_id);
        $user->bonus_balance += $request->bonus_amount;
        $user->save();

        // Save Notification
        Notification::create([
            'user_id' => $request->user_id,
            'title' => 'New Bonus Received',
            'message' => "You have received a {$bonus->bonus_type} bonus of {$bonus->bonus_amount}",
        ]);

        // Send Email
        $user = User::find($request->user_id);
        Mail::send('emails.bonus_received', ['user'  => $user,'bonus' => $bonus,], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('New Bonus Received');
        });
        

        return redirect()->back()->with('success', 'Bonus sent successfully.');
    }


}
