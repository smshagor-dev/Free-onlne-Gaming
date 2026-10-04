<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Support\Facades\Hash;
use App\Models\UserWithdrew;
use App\Models\UserDeposit;
use App\Models\Transaction;
use App\Models\LottaryWinner;
use App\Models\LotteryTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\BanDocument;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('id', $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users', 'search'));
    }


    public function userView($id)
    {
        $user = User::where('id', $id)->firstOrFail();

        $deposits = UserDeposit::where('user_id', $user->id)->latest()->paginate(20);
        $withdrawals = UserWithdrew::where('user_id', $user->id)->latest()->paginate(20);
        $transactions = Transaction::where('user_id', $user->id)->latest()->paginate(20);
        $logins = UserLogin::where('user_id', $user->id)->latest()->paginate(20);
        $lotteryWins = LottaryWinner::where('user_id', $user->id)->latest()->paginate(20);
        $lotteryTransactions = LotteryTransaction::with('lottary')->where('user_id', $user->id)->latest()->paginate(20);

        $totalDeposits = UserDeposit::where('user_id', $user->id)->sum('amount');
        $totalWithdrawals = $withdrawals->sum('amount');
        
        $casinoTransactions = Transaction::where('user_id', $user->id)
            ->where('trx_type', '+')
            ->get();
    
        $totalWins = 0;
        foreach ($casinoTransactions as $trx) {
            if (!empty($trx->casino_details)) {
                $details = json_decode($trx->casino_details, true);
                if (isset($details['win'])) {
                    $totalWins += floatval($details['win']);
                }
            }
        }
        
        $lotteryBuy = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'lottary Buy')
            ->sum('amount');
        
        $lotteryWinAmount = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'Lottery Win')
            ->sum('amount');

        $bonus_release = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'Bonus Release')
            ->sum('amount');
        
        $adminProfit = ($totalDeposits + $lotteryBuy + $bonus_release) - ($totalWins + $lotteryWinAmount);
        $adminStatus = $adminProfit >= 0 ? 'Profit' : 'Lose';

        return view('admin.users.view', [
            'user' => $user,
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'transactions' => $transactions,
            'logins' => $logins,
            'lotteryWins' => $lotteryWins,
            'totalDeposits' => $totalDeposits,
            'totalWithdrawals' => $totalWithdrawals,
            'adminProfit' => $adminProfit,
            'adminStatus' => $adminStatus,
            'lotteryTransactions' => $lotteryTransactions,
        ]);
    }


    public function pending()
    {
        $users = User::where('is_verified', 0)->latest()->paginate(20);
        return view('admin.users.pending', compact('users'));
    }

    public function active()
    {
        $users = User::where('is_verified', 1)->latest()->paginate(20);
        return view('admin.users.active', compact('users'));
    }

    public function logins($id)
    {
        $user = User::findOrFail($id);
        $logins = UserLogin::where('user_id', $id)->latest()->get();
        return view('admin.users.logins', compact('user', 'logins'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $countries = Country::select('id', 'name', 'currency')->get();

        return view('admin.users.edit', compact('user', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_number' => 'nullable|string|max:20',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|min:6',
            'country' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date',
            'email_verified' => 'nullable|boolean',
            'google2fa_status' => 'nullable|boolean',
            'balance' => 'nullable|numeric',
            'available_balance' => 'nullable|numeric',
            'bonus_balance' => 'nullable|numeric',
            'kyc_verified' => 'nullable|boolean',
        ]);

        $data = $request->only(
            'name',
            'email',
            'mobile_number',
            'username',
            'is_verified',
            'country',
            'currency',
            'date_of_birth',
            'balance',
            'available_balance',
            'bonus_balance',
        );

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $data['photo'] = $path;
        }

        if ($request->boolean('email_verified')) {
            $data['email_verified_at'] = now();
        }

        if ($request->filled('google2fa_status')) {
            if ($request->google2fa_status) {
                $data['google2fa_status'] = 1;
                $data['google2fa_secret'] = $user->google2fa_secret ?? app('pragmarx.google2fa')->generateSecretKey();
            } else {
                $data['google2fa_status'] = 0;
                $data['google2fa_secret'] = null;
            }
        }

        if ($request->filled('kyc_verified')) {
            $data['kyc_verified'] = $request->kyc_verified;
            if (!$request->kyc_verified) {
                // Delete user KYC submissions if set to 0
                DB::table('user_kyc_submissions')->where('user_id', $user->id)->delete();
            }
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'User deleted successfully');
    }

    public function userBan(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->where('is_banned', 1) // Only banned users
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return view('admin.users.banned', compact('users', 'search'));
    }

    public function viewApprovedbanUser(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->whereHas('banDocuments', function ($query) {
                $query->where('status', 'approved');
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            })
            ->with(['banDocuments' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->latest()
            ->paginate(20);

        return view('admin.users.banned_approved', compact('users', 'search'));
    }

    public function viewApprovedUserDocuments($userId)
    {
        $user = User::with(['banDocuments' => function ($query) {
            $query->where('status', 'approved');
        }])->findOrFail($userId);

        return view('admin.users.banned_approved_documents', compact('user'));
    }



    public function banCreate($id)
    {
        $user = User::findOrFail($id); // Find user by ID

        return view('admin.users.ban_create', compact('user'));
    }

    public function banStore(Request $request, $id)
    {
        $request->validate([
            'ban_reason' => 'required|string|max:500',
            'documents' => 'nullable|array',
            'documents.*' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);

        // Update user table
        $user->is_banned = 1;
        $user->ban_reason = $request->ban_reason;
        $user->save();

        // Store documents titles if provided
        if ($request->filled('documents')) {
            foreach ($request->documents as $docTitle) {
                BanDocument::create([
                    'user_id' => $user->id,
                    'name' => $docTitle,
                ]);
            }
        }

        $notificationTitle = "You have been Locked";
        $notificationMessage = "Your account has been Locked for the following reason: " . $request->ban_reason;

        Notification::create([
            'user_id' => $user->id,
            'title' => $notificationTitle,
            'message' => $notificationMessage,
        ]);

        Mail::send('emails.user_banned', ['user' => $user, 'reason' => $request->ban_reason], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Your Account has been Locked');
        });

        return redirect()->route('admin.users.banned')->with('success', 'User has been banned successfully.');
    }

    public function showUnbanForm()
    {
        $userId = Auth::id();

        $user = User::select('id', 'is_banned', 'ban_reason')->findOrFail($userId);

        // Get all ban documents (if any exist)
        $allDocs = BanDocument::where('user_id', $userId)->get();

        // If no documents exist → account considered Active
        if ($allDocs->isEmpty()) {
            return view('user.unban_form', [
                'banDocuments' => collect([]),
                'user' => $user,
                'allApproved' => false,
                'verifiedDate' => null,
                'accountActive' => true,
            ]);
        }

        // Check if all are approved
        $allApproved = $allDocs->every(fn($d) => $d->status === 'approved');

        // Latest verification date
        $verifiedDate = $allApproved
            ? Carbon::parse($allDocs->where('status', 'approved')->max('updated_at'))
            : null;

        // Get only pending or rejected docs
        $banDocuments = $allDocs->filter(function ($doc) {
            return is_null($doc->submit_documents) || $doc->status === 'rejected';
        });

        return view('user.unban_form', [
            'banDocuments' => $banDocuments,
            'user' => $user,
            'allApproved' => $allApproved,
            'verifiedDate' => $verifiedDate,
            'accountActive' => false, // since docs exist, user is/was banned
        ]);
    }



    public function submitUnbanDocuments(Request $request)
    {
        $request->validate([
            'submit_documents.*' => 'required|file|mimetypes:image/jpeg,image/png,application/pdf|max:2048',
            'why_unban' => 'required|string|max:5000',
        ]);

        $userId = Auth::id();
        $user = User::findOrFail($userId);

        $files = $request->file('submit_documents');

        $resubmitting = false;

        foreach ($files as $docId => $file) {
            $path = $file->store('unban_docs', 'local');

            $doc = BanDocument::where('id', $docId)
                ->where('user_id', $userId)
                ->first();

            if ($doc && $doc->status === 'rejected') {
                $resubmitting = true;
            }

            BanDocument::where('id', $docId)
                ->where('user_id', $userId)
                ->update([
                    'submit_documents' => $path,
                    'status' => 'pending',
                    'why_unban' => $request->why_unban,
                ]);
        }

        // Notification
        Notification::create([
            'user_id' => $userId,
            'title' => $resubmitting ? 'Unlock Request Resubmitted' : 'Unlock Request Submitted',
            'message' => $resubmitting
                ? 'You have resubmitted your Unlock request. The documents are now under review.'
                : 'You have submitted your Unlock request. The documents are now under review.',
        ]);

        // Email
        Mail::send('emails.user_unban_submitted', ['user' => $user, 'resubmitting' => $resubmitting], function ($message) use ($user, $resubmitting) {
            $subject = $resubmitting ? 'Your Unlock Request Has Been Resubmitted' : 'Your Unlock Request Has Been Submitted';
            $message->to($user->email, $user->name)
                ->subject($subject);
        });

        // Reload all documents for view
        $allDocs = BanDocument::where('user_id', $userId)->get();

        $allApproved = $allDocs->every(fn($d) => $d->status === 'approved');

        $verifiedDate = $allApproved
            ? Carbon::parse($allDocs->where('status', 'approved')->max('updated_at'))
            : null;

        $banDocuments = $allDocs->filter(function ($doc) {
            return is_null($doc->submit_documents) || $doc->status === 'rejected';
        });

        $accountActive = $allDocs->isEmpty();

        return redirect()->route('user.unban.form')->with([
            'banDocuments' => $banDocuments,
            'user' => $user,
            'allApproved' => $allApproved,
            'verifiedDate' => $verifiedDate,
            'accountActive' => $accountActive,
            'success' => $resubmitting
                ? 'Your documents have been re-submitted for review.'
                : 'Your documents have been submitted for review.'
        ]);
    }

    public function listUsersWithSubmissions()
    {
        $users = User::whereHas('banDocuments', function ($q) {
            $q->where('status', 'pending');
        })->get();

        return view('admin.ban_documents.users_list', compact('users'));
    }

    public function viewPendingSubmissions()
    {
        // Fetch all banned users who have any documents
        $users = User::where('is_banned', 1)
            ->whereHas('banDocuments')
            ->with(['banDocuments']) // fetch all documents regardless of status
            ->get();

        return view('admin.users.ban_pending', compact('users'));
    }


    public function updateBanDocuments(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $statuses = $request->input('status', []);
        $comments = $request->input('comments', []);

        DB::transaction(function () use ($statuses, $comments, $user) {

            foreach ($statuses as $docId => $status) {
                $updateData = [
                    'status' => $status,
                    'comments' => $comments[$docId] ?? null,
                ];

                if ($status !== 'approved') {
                    $updateData['why_unban'] = null;
                }

                BanDocument::where('id', $docId)->update($updateData);
            }

            // Reload documents
            $documents = $user->banDocuments;

            $allApproved = $documents->count() > 0 &&
                $documents->every(fn($d) => $d->status === 'approved');

            $anyRejected = $documents->contains(fn($d) => $d->status === 'rejected');

            if ($allApproved) {
                // Unban user
                $user->update([
                    'is_banned' => 0,
                    'ban_reason' => null,
                ]);

                // Notification
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Account Unlocked',
                    'message' => 'Your submitted documents have been approved. Your account is now active.',
                ]);

                // Email using Mail::send()
                Mail::send('emails.user_unbanned', ['user' => $user], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Your Account Has Been Unlocked');
                });
            } elseif ($anyRejected) {
                // Notification
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Document Submission Rejected',
                    'message' => 'Some of your submitted documents were rejected. Please resubmit the required documents.',
                ]);

                // Email using Mail::send()
                Mail::send('emails.user_documents_rejected', ['user' => $user], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Your Document Submission Was Rejected');
                });
            }
        });

        return redirect()->route('admin.users.banned')
            ->with('success', 'User documents updated successfully.');
    }
}
