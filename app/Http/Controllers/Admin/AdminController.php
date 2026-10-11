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
        return view('admin.dashboard', ['users_count' => User::count()]);
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
