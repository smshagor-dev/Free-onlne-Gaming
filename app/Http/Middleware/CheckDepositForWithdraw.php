<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\UserDeposit;
use Illuminate\Support\Facades\Auth;

class CheckDepositForWithdraw
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {

            if (empty($user->balance) || $user->balance <= 0) {
                return redirect()->route('user.deposit.index')
                    ->with('error', 'You need at least 1 deposit and play games to unlock withdraw.');
            }

            $hasDeposit = UserDeposit::where('user_id', $user->id)
                                      ->where('status', 'approved') 
                                      ->exists();

            if (!$hasDeposit) {
                return redirect()->route('user.deposit.index')
                    ->with('error', 'You need at least 1 deposit and play games to unlock withdraw.');
            }
        }

        return $next($request);
    }
}
