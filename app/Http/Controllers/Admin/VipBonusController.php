<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VipBonus;
use App\Models\User;
use App\Mail\VipBonusMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class VipBonusController extends Controller
{
    public function index(Request $request)
    {
        $query = VipBonus::with('user');

        $searchedUser = null;
        $canSendBonus = false; // flag to check if bonus can be sent

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $userIds = User::where('user_id', $search)
                ->orWhere('username', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->pluck('id');

            $query->whereIn('user_id', $userIds);

            $searchedUser = User::whereIn('id', $userIds)->first();

            if ($searchedUser) {
                $activeBonus = VipBonus::where('user_id', $searchedUser->id)
                    ->whereRaw('DATE_ADD(created_at, INTERVAL playing_time HOUR) > NOW()')
                    ->exists();

                $hasAnyBalance = ($searchedUser->vip_bonus > 0) || ($searchedUser->bonus_balance > 0) || ($searchedUser->cashback > 0);

                $canSendBonus = !$activeBonus && !$hasAnyBalance;
            }
        }

        // Only show active bonuses
        $query->whereRaw('DATE_ADD(created_at, INTERVAL playing_time HOUR) > NOW()');

        $bonuses = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.vip_bonuses.index', compact('bonuses', 'searchedUser', 'canSendBonus'));
    }


    public function create(User $user)
    {
        return view('admin.vip_bonuses.create', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'bonus_amount' => 'required|numeric|min:0',
            'playing_time' => 'required|integer|min:1',
            'wager' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($data['user_id']);

        // Check for active bonus
        $activeBonus = VipBonus::where('user_id', $user->id)
            ->get()
            ->first(fn (VipBonus $bonus) => $bonus->created_at->copy()->addHours($bonus->playing_time)->isFuture());

        if ($activeBonus) {
            return redirect()->back()
                ->with('error', 'This user already has an active VIP bonus.');
        }

        // Proceed with creating bonus, transaction, notification, email
        DB::transaction(function () use ($data, $user) {

            // Create VIP Bonus
            $vipBonus = VipBonus::create($data);

            // Update user's vip_bonus column
            $user->increment('vip_bonus', $data['bonus_amount']);

            // Create Transaction
            $transactionNumber = $this->generateTransactionNumber();
            Transaction::create([
                'transaction_number' => $transactionNumber,
                'user_id' => $user->id,
                'transaction_type' => 'VIP Bonus',
                'amount' => $data['bonus_amount'],
                'status' => 'approved',
            ]);

            // Create Notification
            $title = "VIP Bonus Added";
            $message = "You have received a VIP bonus of " . $data['bonus_amount'] . ".";
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
            ]);

            // Send Email
            Mail::to($user->email)->send(new VipBonusMail($user, $data['bonus_amount']));
        });

        return redirect()->route('admin.vipbonuses.index')
            ->with('success', 'VIP Bonus assigned successfully!');
    }


    private function generateTransactionNumber()
    {
        $micro = (int) (microtime(true) * 1000000);
        $microStr = substr($micro, -6);

        $sec = date('s');
        $random = mt_rand(100, 999);

        return $microStr . $sec . $random;
    }

    public function showbonus()
    {
        $user = Auth::user();

        // Fetch only active bonuses for the current user
        $bonusSettings = VipBonus::where('user_id', $user->id)
            ->whereRaw('DATE_ADD(created_at, INTERVAL playing_time HOUR) > NOW()')
            ->orderBy('created_at', 'desc')
            ->get();

        // If the user has no active bonuses, return empty to the view
        if ($bonusSettings->isEmpty()) {
            return view('user.vip_bonus', ['bonusSettings' => collect()]);
        }

        $totalBonus = $bonusSettings->sum('bonus_amount');

        $bonusSettings = collect([
            (object)[
                'user' => $user,
                'user_id' => $user->id,
                'bonus_amount' => $totalBonus,
                'created_at' => now(),
                'updated_at' => now(),
                'playing_time' => $bonusSettings->first()->playing_time ?? 24,
                'wager' => $bonusSettings->first()->wager ?? 4,
            ]
        ]);

        return view('user.vip_bonus', compact('bonusSettings'));
    }
}
