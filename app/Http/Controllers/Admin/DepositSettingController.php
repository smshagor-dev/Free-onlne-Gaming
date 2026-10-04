<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gateway;
use App\Models\RequiredDocument;
use App\Models\UserDeposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Models\Userwithdrew;
use App\Services\BonusService;
use App\Models\Level;

class DepositSettingController extends Controller
{
    public function index()
    {
        $gateways = Gateway::with('requiredDocuments')->get();
        return view('admin.deposit.index', compact('gateways'));
    }

    public function create()
    {
        return view('admin.deposit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'currency' => 'required|string|max:10',
            'symbol' => 'required|string|max:10',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric|gte:min_amount',
            'instruction' => 'nullable|string',
            'withdraw_instruction' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.name' => 'required_with:documents|string|max:255',
            'documents.*.type' => 'required_with:documents|in:text,file',
            'status' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {

            $data = $request->only(['name', 'currency', 'symbol', 'min_amount', 'max_amount', 'instruction', 'withdraw_instruction']);

            $data['status'] = $request->input('status', true);

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('gateway_images', 'public');
            }

            $gateway = Gateway::create($data);

            if ($request->has('documents')) {
                foreach ($request->documents as $doc) {
                    RequiredDocument::create([
                        'gateway_id' => $gateway->id,
                        'name' => $doc['name'],
                        'type' => $doc['type'],
                        'name_withdraw' => $doc['name_withdraw'],
                        'type_withdrew' => $doc['type'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.deposit-settings.index')->with('success', 'Gateway created successfully.');
    }

    public function edit(Gateway $gateway)
    {
        $gateway->load('requiredDocuments');
        return view('admin.deposit.edit', compact('gateway'));
    }

    public function update(Request $request, Gateway $gateway)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'currency' => 'required|string|max:10',
            'symbol' => 'required|string|max:10',
            'min_amount' => 'required|numeric',
            'max_amount' => 'required|numeric|gte:min_amount',
            'instruction' => 'nullable|string',
            'withdraw_instruction' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*.id' => 'nullable|exists:required_documents,id',
            'documents.*.name' => 'required_with:documents|string|max:255',
            'documents.*.type' => 'required_with:documents|in:text,file',
            'status' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $gateway) {
            $data = $request->only(['name', 'currency', 'symbol', 'min_amount', 'max_amount', 'instruction', 'withdraw_instruction']);

            if ($request->has('status')) {
                $data['status'] = $request->input('status');
            }

            if ($request->hasFile('image')) {
                // Delete old image
                if ($gateway->image) {
                    Storage::disk('public')->delete($gateway->image);
                }
                $data['image'] = $request->file('image')->store('gateway_images', 'public');
            }

            $gateway->update($data);

            // Update or create documents
            if ($request->has('documents')) {
                foreach ($request->documents as $doc) {
                    if (isset($doc['id'])) {
                        // Update existing
                        $existingDoc = RequiredDocument::find($doc['id']);
                        if ($existingDoc) {
                            $existingDoc->update([
                                'name' => $doc['name'],
                                'type' => $doc['type'],
                                'name_withdraw' => $doc['name_withdraw'],
                                'type_withdrew' => $doc['type'],
                            ]);
                        }
                    } else {
                        // Create new
                        RequiredDocument::create([
                            'gateway_id' => $gateway->id,
                            'name' => $doc['name'],
                            'type' => $doc['type'],
                            'name_withdraw' => $doc['name_withdraw'],
                            'type_withdrew' => $doc['type'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.deposit-settings.index')->with('success', 'Gateway updated successfully.');
    }

    public function destroy(Gateway $gateway)
    {
        if ($gateway->image) {
            Storage::disk('public')->delete($gateway->image);
        }

        $gateway->delete();
        return redirect()->route('admin.deposit-settings.index')->with('success', 'Gateway deleted successfully.');
    }


    public function viewdepositindex()
    {
        $deposits = UserDeposit::with(['user', 'gateway', 'documents.requiredDocument'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.deposit.view', compact('deposits'));
    }

    public function pending()
    {
        $deposits = UserDeposit::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.deposit.pending', compact('deposits'));
    }

    public function approved()
    {
        $deposits = UserDeposit::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.deposit.approved', compact('deposits'));
    }

    public function rejected()
    {
        $deposits = UserDeposit::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'reject')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.deposit.rejected', compact('deposits'));
    }



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:approved,reject',
            'comments' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($id, $request) {
            $deposit = UserDeposit::with('gateway')->findOrFail($id);
            $previousStatus = $deposit->status;

            // Update deposit
            $deposit->status   = $request->status;
            $deposit->comments = $request->comments ?? null;
            $deposit->save();

            // Update transaction
            $transaction = Transaction::where('user_deposit_id', $deposit->id)->first();
            if ($transaction) {
                $transaction->status   = $request->status;
                $transaction->comments = $request->comments ?? null;
                $transaction->save();
            }

            // If approved → update user balance
            $user = $deposit->user;
            if ($request->status === 'approved' && $previousStatus !== 'approved') {
                $user->balance += $deposit->amount;
                $user->points += $deposit->amount;
                $user->available_points += $deposit->amount;
                $user->save();

                app(BonusService::class)->assignBonus($user, $deposit);

                $currentLevelId = $user->level_id;
                $userPoints = $user->points;
            
                $nextLevel = Level::where('points', '<=', $userPoints)
                                  ->orderBy('points', 'desc')
                                  ->first();
            
                if ($nextLevel && $nextLevel->id != $currentLevelId) {
                    // Upgrade user level
                    $user->level_id = $nextLevel->id;
                    $user->save();
                }
            }

            // Prepare notification text
            $title   = "Deposit " . ucfirst($request->status);
            $message = "Hello ($user->name ?? $user->username),\n\n"
                . "Your deposit has been processed.\n\n"
                . "Details:\n"
                . "Transaction #: {$transaction->transaction_number}\n"
                . "Amount: {$deposit->amount}\n"
                . "Gateway: {$deposit->gateway->name}\n"
                . "Status: {$deposit->status}\n"
                . "Comments: " . ($deposit->comments ?? 'N/A') . "\n\n"
                . "Thank you for using our service.";

            // Store notification in DB
            Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'is_read' => 0,
            ]);

            // Send email
            if (!empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Mail::send('emails.deposit_approved', [
                    'user'        => $user,
                    'deposit'     => $deposit,
                    'transaction' => $transaction,
                    'title'       => $title,
                ], function ($mail) use ($user, $title) {
                    $mail->to($user->email)
                         ->subject($title);
                });
            }
        });

        return redirect()->back()->with('success', 'Deposit status updated successfully!');
    }

    // Withdrew

    public function viewwithdrewindex()
    {
        $withdrews = Userwithdrew::with(['user', 'gateway', 'documents.requiredDocument'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.withdrew.view', compact('withdrews'));
    }

    public function withdrewpending()
    {
        $withdrews = Userwithdrew::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.withdrew.pending', compact('withdrews'));
    }

    public function withdrewapproved()
    {
        $withdrews = Userwithdrew::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.withdrew.approved', compact('withdrews'));
    }

    public function withdrewrejected()
    {
        $withdrews = Userwithdrew::with(['user', 'gateway', 'documents.requiredDocument'])
            ->where('status', 'reject')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.withdrew.rejected', compact('withdrews'));
    }



    public function updateStatuswithdrew(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:approved,reject',
            'comments' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($id, $request) {
            $withdrew = UserWithdrew::with('gateway', 'user')->findOrFail($id);
            $previousStatus = $withdrew->status;

            // Update withdrawal
            $withdrew->status   = $request->status;
            $withdrew->comments = $request->comments ?? null;
            $withdrew->save();

            // Update transaction
            $transaction = Transaction::where('user_withdrew_id', $withdrew->id)->first();
            if ($transaction) {
                $transaction->status   = $request->status;
                $transaction->comments = $request->comments ?? null;
                $transaction->save();
            }

            // Update user balance
            $user = $withdrew->user;
            if ($request->status === 'approved') {
                // Approved → balance remains deducted
            } elseif ($request->status === 'reject' && $previousStatus !== 'reject') {
                // Rejected → return amount to user balance
                $user->available_balance += $withdrew->amount;
                $user->save();
            }

            // Prepare notification text
            $title   = "Withdrawal " . ucfirst($request->status);
            $message = "Hello ($user->name ?? $user->username),\n\n"
                . "Your withdrawal request has been processed.\n\n"
                . "Details:\n"
                . "Transaction #: {$transaction->transaction_number}\n"
                . "Amount: {$withdrew->amount}\n"
                . "Gateway: {$withdrew->gateway->name}\n"
                . "Status: {$withdrew->status}\n"
                . "Comments: " . ($withdrew->comments ?? 'N/A') . "\n\n"
                . "Thank you for using our service.";

            // Store notification in DB
            Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'is_read' => 0,
            ]);

            // Send email
            if (!empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Mail::send('emails.withdrawal_approved', [
                    'user'        => $user,
                    'withdrawal'     => $withdrew,
                    'transaction' => $transaction,
                    'title'       => $title, // optional if used in Blade
                ], function ($mail) use ($user, $title) {
                    $mail->to($user->email)
                         ->subject($title);
                });
            }
        });

        return redirect()->back()->with('success', 'Withdrawal status updated successfully!');
    }
}
