<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserDeposit;
use App\Models\UserWithdrew;
use App\Models\LottariesWinner;
use App\Models\LottaryWinner;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\LotteryTransaction;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Collect user profile info
        $userInfo = User::select(
            'id',
            'name',
            'email',
            'date_of_birth',
            'balance',
            'available_balance',
            'bonus_balance',
            'mobile_number',
            'photo',
            'user_location',
            'user_country',
            'user_region',
            'user_city',
            'username',
            'verification_code',
            'is_verified',
            'kyc_verified',
            'is_banned',
            'points',
            'level_id',
            'currency'
        )->where('id', $user->id)->first();

        $search = $request->get('transaction_number');

        $transactions = Transaction::where('user_id', $user->id)
            ->when($search, function ($query, $search) {
                return $query->where('transaction_number', 'LIKE', "%{$search}%");
            })
            ->select(
                'transaction_number',
                'user_id',
                'user_deposit_id',
                'user_withdrew_id',
                'amount',
                'transaction_type',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'transactions_page');

        $casinotransactions = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'casino game') // filter only casino game transactions
            ->when($search, function ($query, $search) {
                return $query->where('transaction_number', 'LIKE', "%{$search}%");
            })
            ->select(
                'transaction_number',
                'session_id',
                'user_id',
                'user_deposit_id',
                'user_withdrew_id',
                'amount',
                'transaction_type',
                'status',
                'comments',
                'trx_type',
                'casino_details',
                'remark',
                'trx',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'casinotransactions_page');


        $deposits = UserDeposit::where('user_id', $user->id)
            ->select(
                'transaction_number',
                'user_id',
                'gateway_id',
                'amount',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'deposits_page');

        $withdrawals = UserWithdrew::where('user_id', $user->id)
            ->select(
                'transaction_number',
                'user_id',
                'gateway_id',
                'amount',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page');

        $lotteryWins = LottaryWinner::where('user_id', $user->id)
            ->select(
                'lottary_id',
                'user_id',
                'transaction_id',
                'prize_id',
                'ticket_number',
                'price',
                'position',
                'created_at',
                'updated_at'
            )

            ->latest()
            ->paginate(10, ['*'], 'lottery_page');

        $totalDeposit = UserDeposit::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $totalWithdrew = UserWithdrew::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $totalWin = LottaryWinner::where('user_id', $user->id)
            ->sum('price');

        $lotteryTransactions = LotteryTransaction::where('user_id', $user->id)
            ->select('user_id', 'lottary_id', 'ticket_number', 'transaction_number', 'amount', 'created_at')
            ->latest()
            ->paginate(10, ['*'], 'lottery_transactions_page');


        $setting = Setting::first();

        return view('user.dashboard', compact(
            'userInfo',
            'transactions',
            'casinotransactions',
            'deposits',
            'withdrawals',
            'lotteryWins',
            'lotteryTransactions',
            'totalDeposit',
            'totalWithdrew',
            'totalWin',
            'setting'
        ));
    }

    public function transction(Request $request)
    {
        $user = Auth::user();

        // Collect user profile info
        $userInfo = User::select(
            'id',
            'name',
            'email',
            'date_of_birth',
            'balance',
            'available_balance',
            'bonus_balance',
            'mobile_number',
            'photo',
            'user_location',
            'user_country',
            'user_region',
            'user_city',
            'username',
            'verification_code',
            'is_verified',
            'kyc_verified',
            'is_banned',
            'points',
            'level_id'
        )->where('id', $user->id)->first();

        $search = $request->get('transaction_number');

        $transactions = Transaction::where('user_id', $user->id)
            ->when($search, function ($query, $search) {
                return $query->where('transaction_number', 'LIKE', "%{$search}%");
            })
            ->select(
                'transaction_number',
                'user_id',
                'user_deposit_id',
                'user_withdrew_id',
                'amount',
                'transaction_type',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'transactions_page');

        $casinotransactions = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'casino game') // filter only casino game transactions
            ->when($search, function ($query, $search) {
                return $query->where('transaction_number', 'LIKE', "%{$search}%");
            })
            ->select(
                'transaction_number',
                'session_id',
                'user_id',
                'user_deposit_id',
                'user_withdrew_id',
                'amount',
                'transaction_type',
                'status',
                'comments',
                'trx_type',
                'casino_details',
                'remark',
                'trx',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'casinotransactions_page');


        $deposits = UserDeposit::where('user_id', $user->id)
            ->select(
                'transaction_number',
                'user_id',
                'gateway_id',
                'amount',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'deposits_page');

        $withdrawals = UserWithdrew::where('user_id', $user->id)
            ->select(
                'transaction_number',
                'user_id',
                'gateway_id',
                'amount',
                'status',
                'comments',
                'created_at',
                'updated_at'
            )
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page');

        $lotteryWins = LottaryWinner::where('user_id', $user->id)
            ->select(
                'lottary_id',
                'user_id',
                'transaction_id',
                'prize_id',
                'ticket_number',
                'price',
                'position',
                'created_at',
                'updated_at'
            )

            ->latest()
            ->paginate(10, ['*'], 'lottery_page');

        $totalDeposit = UserDeposit::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $totalWithdrew = UserWithdrew::where('user_id', $user->id)
            ->where('status', 'approved')
            ->sum('amount');

        $totalWin = LottaryWinner::where('user_id', $user->id)
            ->sum('price');

        $lotteryTransactions = LotteryTransaction::where('user_id', $user->id)
            ->select('user_id', 'lottary_id', 'ticket_number', 'transaction_number', 'amount', 'created_at')
            ->latest()
            ->paginate(10, ['*'], 'lottery_transactions_page');


        $setting = Setting::first();

        return view('user.transction', compact(
            'userInfo',
            'transactions',
            'casinotransactions',
            'deposits',
            'withdrawals',
            'lotteryWins',
            'lotteryTransactions',
            'totalDeposit',
            'totalWithdrew',
            'totalWin',
            'setting'
        ));
    }
}
