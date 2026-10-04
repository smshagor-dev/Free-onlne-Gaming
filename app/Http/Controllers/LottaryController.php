<?php

namespace App\Http\Controllers;

use App\Models\Lottary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\LottaryPrice;
use App\Models\Setting;
use App\Models\LotteryTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\LottaryWinner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Mail\LotteryPurchasedMail;
use Illuminate\Support\Facades\Mail;
use App\Mail\LottaryPrizesAddedMail;

class LottaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lottaries = Lottary::latest()->paginate(12);
        return view('admin.lottaries.index', compact('lottaries'));
    }

    public function create()
    {
        return view('admin.lottaries.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'photo'          => ['nullable', 'image', 'max:2048'],
            'price'          => ['required', 'numeric', 'min:0'],
            'prize_number'   => ['required', 'integer', 'min:1'],
            'winner_number'  => ['nullable', 'integer', 'min:1'],
            'draw_date'      => ['required', 'date'],
            'is_draw'        => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')
                ->store('lottaries', 'public');
        }

        $data['is_draw'] = (bool) ($data['is_draw'] ?? false);

        Lottary::create($data);

        return redirect()->route('admin.lottaries.index')
            ->with('success', 'Lottary created successfully.');
    }

    public function edit(Lottary $lottary)
    {
        return view('admin.lottaries.edit', compact('lottary'));
    }

    public function update(Request $request, Lottary $lottary)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'photo'          => ['nullable', 'image', 'max:2048'],
            'price'          => ['required', 'numeric', 'min:0'],
            'prize_number'   => ['required', 'integer', 'min:1'],
            'winner_number'  => ['nullable', 'integer', 'min:1'],
            'draw_date'      => ['required', 'date'],
            'is_draw'        => ['sometimes', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            // delete old
            if ($lottary->photo) {
                Storage::disk('public')->delete($lottary->photo);
            }
            $data['photo'] = $request->file('photo')
                ->store('lottaries', 'public');
        }

        $data['is_draw'] = (bool) ($data['is_draw'] ?? false);

        $lottary->update($data);

        return redirect()->route('admin.lottaries.index')
            ->with('success', 'Lottary updated successfully.');
    }

    public function destroy(Lottary $lottary)
    {
        if ($lottary->photo) {
            Storage::disk('public')->delete($lottary->photo);
        }
        $lottary->delete();

        return redirect()->route('admin.lottaries.index')
            ->with('success', 'Lottary deleted successfully.');
    }


    public function createPrizes($lottaryId)
    {
        $lottary = Lottary::findOrFail($lottaryId);

        $prizeCount = $lottary->prize_number;

        return view('admin.lottaries.prizes.create', compact('lottary', 'prizeCount'));
    }

    public function storePrizes(Request $request, $lottaryId)
    {
        $lottary = Lottary::findOrFail($lottaryId);

        $data = $request->validate([
            'prizes'                     => ['required', 'array', 'size:' . $lottary->prize_number],
            'prizes.*.name'              => ['required', 'string', 'max:255'],
            'prizes.*.description'       => ['nullable', 'string'],
            'prizes.*.price'             => ['required', 'numeric', 'min:0'],
            'prizes.*.price_number'      => ['required', 'integer', 'min:1'],
        ]);

        $rows = array_map(function ($p) use ($lottary) {
            return [
                'lottary_id'   => $lottary->id,
                'name'         => $p['name'],
                'description'  => $p['description'] ?? null,
                'price'        => $p['price'],
                'price_number' => $p['price_number'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }, $data['prizes']);

        LottaryPrice::insert($rows);

        $users = User::whereNotNull('email')->get();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new LottaryPrizesAddedMail($lottary));
        }

        return redirect()->route('admin.lottaries.index')
            ->with('success', 'Prizes added successfully.');
    }

    public function editPrizes($lottaryId)
    {
        $lottary = Lottary::with('prizes')->findOrFail($lottaryId);

        return view('admin.lottaries.prizes.edit', compact('lottary'));
    }

    public function updatePrizes(Request $request, $lottaryId)
    {
        $lottary = Lottary::with('prizes')->findOrFail($lottaryId);

        $data = $request->validate([
            'prizes'                     => ['required', 'array'],
            'prizes.*.id'                => ['required', 'exists:lottary_prices,id'],
            'prizes.*.name'              => ['required', 'string', 'max:255'],
            'prizes.*.description'       => ['nullable', 'string'],
            'prizes.*.price'             => ['required', 'numeric', 'min:0'],
            'prizes.*.price_number'      => ['required', 'integer', 'min:1'],
        ]);

        foreach ($data['prizes'] as $p) {
            LottaryPrice::where('id', $p['id'])->update([
                'name'         => $p['name'],
                'description'  => $p['description'] ?? null,
                'price'        => $p['price'],
                'price_number' => $p['price_number'],
                'updated_at'   => now(),
            ]);
        }

        return redirect()->route('admin.lottaries.index')
            ->with('success', 'Prizes updated successfully.');
    }

    // -----------------------------

    public function transactions()
    {
        $lottaries = Lottary::withCount(['transactions as total_tickets' => function ($q) {
            $q->select(DB::raw('count(*)'));
        }])->withSum('transactions as total_income', 'amount')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $setting = Setting::first();

        return view('admin.lottaries.transactions', compact('lottaries', 'setting'));
    }

    public function transactionsshow($lottaryId)
    {
        $lottary = Lottary::findOrFail($lottaryId);

        $transactions = LotteryTransaction::with('user')
            ->where('lottary_id', $lottary->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = LotteryTransaction::where('lottary_id', $lottary->id)
            ->selectRaw('COUNT(*) as total_tickets, SUM(amount) as total_income')
            ->first();

        $setting = Setting::first();

        return view('admin.lottaries.transaction_show', compact('lottary', 'transactions', 'summary', 'setting'));
    }


    public function drawLotteryWinners($lottaryId)
    {
        try {
            $response = null;

            DB::transaction(function () use ($lottaryId, &$response) {
                $lottary = Lottary::with('prices')->findOrFail($lottaryId);
                $prizes  = $lottary->prices;

                $usedTransactionIds = [];
                $winners = [];

                foreach ($prizes as $prize) {

                    $winnerTransaction = LotteryTransaction::with('user')
                        ->where('lottary_id', $lottary->id)
                        ->where('ticket_number', $prize->winner_number)
                        ->whereNotIn('id', $usedTransactionIds)
                        ->first();

                    if (!$winnerTransaction) {
                        $winnerTransaction = LotteryTransaction::with('user')
                            ->where('lottary_id', $lottary->id)
                            ->whereNotIn('id', $usedTransactionIds)
                            ->inRandomOrder()
                            ->first();

                        if (!$winnerTransaction) {
                            continue;
                        }
                    }

                    $user = $winnerTransaction->user;

                    LottaryWinner::create([
                        'lottary_id'     => $lottary->id,
                        'user_id'        => $user->id,
                        'transaction_id' => $winnerTransaction->id,
                        'prize_id'       => $prize->id,
                        'ticket_number'  => $winnerTransaction->ticket_number,
                        'price'          => $prize->price,
                        'position'       => $prize->name,
                    ]);

                    $prize->update(['user_id' => $user->id]);

                    $transactionNumber = 'WIN' . strtoupper(Str::random(10));

                    Transaction::create([
                        'user_id'            => $user->id,
                        'transaction_type'   => 'Lottery Win',
                        'amount'             => $prize->price,
                        'status'             => 'approved',
                        'transaction_number' => $transactionNumber,
                    ]);

                    $user->increment('available_balance', $prize->price);

                    Notification::create([
                        'user_id' => $user->id,
                        'title'   => '🎉 You Won a Lottery Prize!',
                        'message' => "Congratulations! You won the {$prize->name} prize of ৳{$prize->price}.",
                        'is_read' => false,
                    ]);

                    Mail::to($user->email)->send(new \App\Mail\LotteryWinMail($user, $lottary, $prize));

                    $usedTransactionIds[] = $winnerTransaction->id;

                    $winners[] = [
                        'user'          => $user->name,
                        'email'         => $user->email,
                        'prize'         => $prize->name,
                        'amount'        => $prize->price,
                        'ticket_number' => $winnerTransaction->ticket_number,
                        'transaction_id' => $winnerTransaction->id,
                    ];
                }

                $lottary->update(['is_draw' => true]);

                $response = [
                    'status'  => 'success',
                    'message' => 'Lottery winners drawn successfully!',
                    'winners' => $winners
                ];
            });

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function winners($lottaryId)
    {
        
        $lottary = Lottary::findOrFail($lottaryId);

        $winners = LottaryWinner::with([
            'user',       
            'transaction', 
            'prize'      
        ])
            ->where('lottary_id', $lottary->id)
            ->orderBy('id', 'asc')
            ->get();

        $setting = Setting::first();

        return view('admin.lottaries.winners', compact('lottary', 'winners', 'setting'));
    }




    // User side lottary view


    public function view()
    {
        $lottaries = Lottary::where('draw_date', '>=', now())
            ->latest()
            ->paginate(30);

        $setting = Setting::first();

        $soldTickets = LotteryTransaction::select('lottary_id', DB::raw('count(*) as total'))
            ->groupBy('lottary_id')
            ->pluck('total', 'lottary_id');


        return view('user.lottaries.index', compact('lottaries', 'setting', 'soldTickets'));
    }


    // Show single lottary with prizes
    public function show($id)
    {
        $lottary = Lottary::with('prizes')->findOrFail($id);

        $setting = Setting::first();

        return view('user.lottaries.show', compact('lottary', 'setting'));
    }

    public function viewdrwn()
    {
        $lottaries = Lottary::where('draw_date', '<=', now())
            ->latest()
            ->paginate(30);

        $setting = Setting::first();

        return view('user.lottaries.drew', compact('lottaries', 'setting'));
    }



    // Buy ticket for lottary

    public function buy(Request $request, $lottaryId)
    {
        $user = Auth::user();
        $lottary = Lottary::findOrFail($lottaryId);

        if ($user->balance < $lottary->price) {
            return back()->with('error', 'Insufficient balance to buy this lottery ticket.');
        }

        DB::transaction(function () use ($user, $lottary) {
            // Deduct balance
            $user->decrement('balance', $lottary->price);

            // Generate unique ticket number (16–20 digits)
            $ticketLength = random_int(16, 20);
            $ticketNumber = strtoupper(Str::random($ticketLength));


            // Generate transaction number (nanosecond-based 10 digits)
            $transactionNumber = substr(
                str_pad((int)(microtime(true) * 1000000), 10, '0', STR_PAD_LEFT),
                0,
                10
            );

            // Save in lottery_transactions
            $lotteryTx = LotteryTransaction::create([
                'user_id'           => $user->id,
                'lottary_id'        => $lottary->id,
                'ticket_number'     => $ticketNumber,
                'transaction_number' => $transactionNumber,
                'amount'            => $lottary->price,
            ]);

            // Save in global transactions table
            Transaction::create([
                'user_id'           => $user->id,
                'amount'            => $lottary->price,
                'transaction_number' => $transactionNumber,
                'transaction_type'  => 'lottary Buy',
                'status'            => 'approved',
            ]);


            // -----------------------------
            // Local Notification
            // -----------------------------
            $title   = "Lottery Ticket Purchased";
            $message = "You have successfully purchased a lottery ticket for '{$lottary->title}'. 
        Ticket Number: {$ticketNumber}, Transaction Number: {$transactionNumber}, Price: {$lottary->price}.";

            Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'is_read' => false,
            ]);

            Mail::to($user->email)->send(new LotteryPurchasedMail($user, $lottary, $ticketNumber, $transactionNumber));
        });

        return redirect()->route('user.lottaries.my')
            ->with('success', 'Lottery ticket purchased successfully!');
    }

    public function myLottaries()
    {
        $user = auth::user();

        $transactions = LotteryTransaction::with(['lottary', 'winner' => function ($q) use ($user) {
            $q->where('user_id', $user->id);
        }])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $setting = Setting::first();

        return view('user.lottaries.my', compact('transactions', 'setting'));
    }

    public function mywin()
    {
        $user = auth::user();

        $transactions = LotteryTransaction::where('user_id', $user->id)
            ->whereHas('winner') 
            ->with(['lottary', 'winner'])
            ->latest()
            ->paginate(20);

        $setting = Setting::first();

        return view('user.lottaries.mywin', compact('transactions', 'setting'));
    }



    public function viewTicket($ticketNumber)
    {
        $ticket = LotteryTransaction::with('lottary')
            ->where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('user.lottaries.view_ticket', compact('ticket'));
    }

    public function userwinners($lottaryId)
    {
        
        $lottary = Lottary::findOrFail($lottaryId);

        $winners = LottaryWinner::with([
            'user',        
            'transaction', 
            'prize'        
        ])
            ->where('lottary_id', $lottary->id)
            ->orderBy('id', 'asc')
            ->get();

        $setting = Setting::first();

        return view('user.lottaries.winner', compact('lottary', 'winners', 'setting'));
    }
}
