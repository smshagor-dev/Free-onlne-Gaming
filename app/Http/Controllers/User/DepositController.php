<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gateway;
use App\Models\UserDeposit;
use App\Models\UserDepositDocument;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\DepositSubmittedMail;
use App\Models\RequiredDocument;
use App\Models\UserWithdrew;
use App\Models\UserWithdrawDocument;
use App\Mail\WithdrawalSubmittedMail;

class DepositController extends Controller
{

    public function index()
    {
        $allGateways = Gateway::select('id', 'name', 'image')
            ->where('status', 1)
            ->get()
            ->groupBy('name');

        $gateways = collect();

        foreach ($allGateways as $name => $group) {
            $group = $group->values();

            $lastIndex = session()->get("gateway_rotation.$name", -1);

            $nextIndex = ($lastIndex + 1) % $group->count();

            $gateways->push($group[$nextIndex]);

            session()->put("gateway_rotation.$name", $nextIndex);
        }

        return view('user.deposit.index', compact('gateways'));
    }




    public function create(Gateway $gateway)
    {
        $gateway->load('requiredDocuments');
        return view('user.deposit.create', compact('gateway'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'gateway_id' => 'required|exists:gateways,id',
                'amount' => 'required|numeric|min:0.01',
                'documents' => 'nullable|array',
                'documents.*.value' => 'required|string|max:10000',
                'documents.*.file' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:2048',
            ]);

            DB::transaction(function () use ($request) {
                $user = Auth::user();

                // Generate unique transaction number (10 digits)
                $transactionNumber = substr(
                    str_pad((int)(microtime(true) * 1000000), 10, '0', STR_PAD_LEFT),
                    0,
                    10
                );

                // Create deposit
                $deposit = UserDeposit::create([
                    'user_id'            => $user->id,
                    'gateway_id'         => $request->gateway_id,
                    'amount'             => $request->amount,
                    'status'             => 'pending',
                    'transaction_number' => $transactionNumber,
                ]);

                // Handle required documents
                if ($request->has('documents')) {
                    foreach ($request->documents as $doc_id => $doc_data) {
                        $value = $doc_data['value'] ?? null;
                        if (isset($doc_data['file'])) {
                            $value = $doc_data['file']->store('user_deposit_docs', 'local');
                        }

                        UserDepositDocument::create([
                            'user_deposit_id'      => $deposit->id,
                            'required_document_id' => $doc_id,
                            'value'                => $value,
                        ]);
                    }
                }

                // Create transaction
                Transaction::create([
                    'user_id'          => $user->id,
                    'user_deposit_id'  => $deposit->id,
                    'amount'           => $deposit->amount,
                    'status'           => 'pending',
                    'transaction_number' => $transactionNumber,
                    'transaction_type' => 'Deposit',
                ]);

                // Local notification
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => 'Deposit Submitted',
                    'message' => 'Your deposit of ' . $deposit->amount .
                        ' via ' . $deposit->gateway->name .
                        ' has been submitted. Transaction #: ' . $transactionNumber .
                        ' Status: Pending',
                    'is_read' => 0,
                ]);

                // Email notification
                Mail::to($user->email)->send(new DepositSubmittedMail($deposit));
            });

            return redirect()
                ->route('user.deposit.history')
                ->with('success', 'Deposit submitted successfully. Status: Pending');
        } catch (\Exception $e) {
            Log::warning('Deposit submission failed', [
                'user_id' => Auth::id(),
                'exception' => $e::class,
            ]);

            return redirect()
                ->route('user.deposit.history')
                ->with('error', 'Deposit failed. Please try again or contact support.');
        }
    }

    public function depositHistory()
    {
        $user = Auth::user();

        $deposits = UserDeposit::with('gateway')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('user.deposit.history', compact('deposits'));
    }


    // Withdrawal

    public function withdrewindex()
    {
        $allGateways = Gateway::select('id', 'name', 'image')
            ->where('status', 1)
            ->get()
            ->groupBy('name');

        $gateways = collect();

        foreach ($allGateways as $name => $group) {
            $group = $group->values();

            $lastIndex = session()->get("gateway_rotation.$name", -1);

            $nextIndex = ($lastIndex + 1) % $group->count();

            $gateways->push($group[$nextIndex]);

            session()->put("gateway_rotation.$name", $nextIndex);
        }

        return view('user.withdrew.index', compact('gateways'));
    }

    public function withdrewcreate(Gateway $gateway)
    {
        $gateway->load('requiredDocuments');
        return view('user.withdrew.create', compact('gateway'));
    }

    public function withdrewstore(Request $request)
    {
        try {
            $request->validate([
                'gateway_id' => 'required|exists:gateways,id',
                'amount' => 'required|numeric|min:0.01',
                'documents' => 'nullable|array',
                'documents.*.value' => 'nullable|string|max:10000',
                'documents.*.file' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:2048',
            ]);

            DB::transaction(function () use ($request) {
                $user = Auth::user();

                // Ensure user has enough balance
                if ($user->available_balance < $request->amount) {
                    throw new \Exception('Insufficient balance for withdrew. Play Casino Games and withdrew again.');
                }

                // Generate unique transaction number
                $transactionNumber = substr(
                    str_pad((int)(microtime(true) * 1000000), 10, '0', STR_PAD_LEFT),
                    0,
                    10
                );

                // Create withdraw
                $withdraw = UserWithdrew::create([
                    'user_id'            => $user->id,
                    'gateway_id'         => $request->gateway_id,
                    'amount'             => $request->amount,
                    'status'             => 'pending',
                    'transaction_number' => $transactionNumber,
                ]);

                // Deduct from balance
                $user->available_balance -= $request->amount;
                $user->save();

                // Save documents
                if ($request->has('documents')) {
                    foreach ($request->documents as $doc_id => $doc_data) {
                        $value = $doc_data['value'] ?? null;
                        if (isset($doc_data['file'])) {
                            $value = $doc_data['file']->store('user_withdraw_docs', 'local');
                        }

                        UserWithdrawDocument::create([
                            'user_withdrew_id'     => $withdraw->id,
                            'required_document_id' => $doc_id,
                            'value'                => $value,
                        ]);
                    }
                }

                // Create transaction
                Transaction::create([
                    'user_id'           => $user->id,
                    'user_withdrew_id'  => $withdraw->id,
                    'amount'            => $withdraw->amount,
                    'status'            => 'pending',
                    'transaction_number' => $transactionNumber,
                    'transaction_type' => 'Withdrew',
                ]);

                // Notification
                Notification::create([
                    'user_id' => $user->id,
                    'title'   => 'Withdrawal Request Submitted',
                    'message' => 'Your withdrawal of ' . $withdraw->amount . ' via ' . $withdraw->gateway->name .
                        ' has been submitted. Transaction #: ' . $transactionNumber . ' Status: Pending',
                    'is_read' => 0,
                ]);

                // Email
                Mail::to($user->email)->send(new WithdrawalSubmittedMail($withdraw));
            });

            return redirect()
                ->route('user.withdrew.history')
                ->with('success', 'Withdrawal request submitted successfully. Status: Pending');
        } catch (\Exception $e) {
            Log::warning('Withdrawal submission failed', [
                'user_id' => Auth::id(),
                'exception' => $e::class,
            ]);

            return redirect()
                ->route('user.withdrew.history')
                ->with('error', 'Withdrawal request failed. Please try again or contact support.');
        }
    }


    public function withdrewHistory()
    {
        $user = Auth::user();

        $withdrews = Userwithdrew::with('gateway')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('user.withdrew.history', compact('withdrews'));
    }
}
