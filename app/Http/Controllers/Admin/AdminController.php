<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\UserWithdrew;
use App\Models\UserDeposit;
use App\Models\Transaction;
use App\Models\UserLogin;
use App\Models\Country;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = auth()->guard('admin')->user();

        $users = User::all();
        $user_withdrew = UserWithdrew::all();
        $user_deposits = UserDeposit::all();
        $transactions = Transaction::all();
        $user_logins = UserLogin::all();

        // Counts
        $users_count = $users->count();
        $user_withdrew_count = $user_withdrew->count();
        $user_deposits_count = $user_deposits->count();
        $transactions_count = $transactions->count();
        $user_logins_count = $user_logins->count();

        // Deposits by status
        $deposits_approved = $user_deposits->where('status', 'approved')->count();
        $deposits_pending = $user_deposits->where('status', 'pending')->count();
        $deposits_rejected = $user_deposits->where('status', 'reject')->count();

        // Withdrawals by status
        $withdrew_approved = $user_withdrew->where('status', 'approved')->count();
        $withdrew_pending = $user_withdrew->where('status', 'pending')->count();
        $withdrew_rejected = $user_withdrew->where('status', 'reject')->count();

        // Chart Data: Countries
        $countries = $user_logins->pluck('country')->filter()->countBy();
        $regions = $user_logins->pluck('region')->filter()->countBy();
        $cities = $user_logins->pluck('city')->filter()->countBy();

        // Chart Data: Devices & Browsers
        $browsers = $user_logins->pluck('browser')->filter()->countBy();
        $device_types = $user_logins->pluck('device_type')->filter()->countBy();
        $device_names = $user_logins->pluck('device_name')->filter()->countBy();

        $total_deposit_amount = $user_deposits->sum('amount'); // All deposits
        $approved_deposit_amount = $user_deposits->where('status', 'approved')->sum('amount');

        $total_withdrew_amount = $user_withdrew->sum('amount'); // All withdrawals
        $approved_withdrew_amount = $user_withdrew->where('status', 'approved')->sum('amount');

        $casino_transactions_count = $transactions->where('transaction_type', 'casino game')->count();
        $points_transactions_count = $transactions->where('transaction_type', 'Point to Bonus')->count();

        $totalDeposits = UserDeposit::sum('amount');

        $casinoTransactions = Transaction::where('trx_type', '+')->get();

        $totalWins = 0;
        foreach ($casinoTransactions as $trx) {
            if (!empty($trx->casino_details)) {
                $details = json_decode($trx->casino_details, true);
                if (isset($details['win'])) {
                    $totalWins += floatval($details['win']);
                }
            }
        }

        $lotteryBuy = Transaction::where('transaction_type', 'lottary Buy')->sum('amount');
        $lotteryWinAmount = Transaction::where('transaction_type', 'Lottery Win')->sum('amount');
        $bonus_release = Transaction::where('transaction_type', 'Bonus Release')->sum('amount');

        $admin_profit = ($totalDeposits + $lotteryBuy + $bonus_release) - ($totalWins + $lotteryWinAmount);
        $admin_status = $admin_profit >= 0 ? 'Profit' : 'Lose';



        return view('admin.dashboard', compact(
            'users',
            'user_withdrew',
            'user_deposits',
            'transactions',
            'user_logins',
            'users_count',
            'user_withdrew_count',
            'user_deposits_count',
            'transactions_count',
            'user_logins_count',
            'deposits_approved',
            'deposits_pending',
            'deposits_rejected',
            'withdrew_approved',
            'withdrew_pending',
            'withdrew_rejected',
            'countries',
            'regions',
            'cities',
            'browsers',
            'device_types',
            'device_names',
            'total_deposit_amount',
            'approved_deposit_amount',
            'total_withdrew_amount',
            'approved_withdrew_amount',
            'casino_transactions_count',
            'points_transactions_count',
            'admin_profit',
            'admin_status'
        ));
    }


    /**
     * Display the admin profile.
     *
     * @return \Illuminate\View\View
     */

    public function viewProfile()
    {
        // Assuming admin is logged in
        $admin = Auth::guard('admin')->user();

        return view('admin.profile', compact('admin'));
    }

    public function editProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.edit-profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:admins,email,' . $admin->id,
            'mobile_number' => 'nullable|string|max:20',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'email', 'mobile_number']);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($admin->photo && Storage::exists('public/' . $admin->photo)) {
                Storage::delete('public/' . $admin->photo);
            }
            $data['photo'] = $request->file('photo')->store('admin_photos', 'public');
        }

        $admin->update($data);

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }

    public function editPassword()
    {
        return view('admin.edit-password');
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $admin->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Password updated successfully.');
    }

    public function userTransactions(Request $request)
    {
        $search = $request->input('transaction_number');

        $query = Transaction::query();

        if ($search) {
            $query->where('transaction_number', 'like', "%$search%");
        }

        $transactions = $query->orderBy('id', 'asc')->paginate(15);

        return view('admin.users.transactions', compact('transactions', 'search'));
    }

    public function index()
    {
        $countries = Country::select('id', 'name', 'currency', 'currency_name', 'currency_symbol', 'status')
            ->orderBy('name')
            ->get();

        return view('admin.countries.index', compact('countries'));
    }

    public function editCurrency($id)
    {
        $country = Country::findOrFail($id);
        return view('admin.countries.edit-currency', compact('country'));
    }

    public function updateCurrency(Request $request, $id)
    {
        $request->validate([
            'currency' => 'required|string|max:10',
            'currency_name' => 'required|string|max:100',
            'currency_symbol' => 'required|string|max:5',
            'status' => 'required|in:active,inactive',
        ]);

        $country = Country::findOrFail($id);
        $country->currency = $request->currency;
        $country->currency_name = $request->currency_name;
        $country->currency_symbol = $request->currency_symbol;
        $country->status = $request->status;
        $country->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Currency updated successfully']);
        }

        return redirect()->route('admin.countries.index')->with('success', 'Currency updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $country->status = $request->status; 
        $country->save();

        return response()->json([
            'success' => true,
            'status'  => $country->status,
            'label'   => $country->status == 1 ? 'Active' : 'Deactive',
        ]);
    }
}
