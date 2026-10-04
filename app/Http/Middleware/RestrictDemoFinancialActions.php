<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RestrictDemoFinancialActions
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->registration_type !== 'demo') {
            return $next($request);
        }

        $casinoFinancial = [
            'casino/play',
            'casino/close',
            'casino/session/*',
            'bonus-play/*',
            'casino-cashback/*',
            'vip-bonus-play/*',
        ];

        foreach ($casinoFinancial as $pattern) {
            if ($request->is($pattern)) {
                abort(403, 'Financial game actions are disabled in demo mode.');
            }
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $blocked = [
            'user/deposit*',
            'user/withdrew*',
            'lottaries/*/buy',
            'user/bonus/claim/*',
            'user/cashback/claim',
            'user/convert-points',
            'user/referral/collect-balance',
        ];

        foreach ($blocked as $pattern) {
            if ($request->is($pattern)) {
                abort(403, 'Financial actions are disabled in demo mode.');
            }
        }

        return $next($request);
    }
}
