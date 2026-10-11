<?php

namespace App\Http\Middleware;

use App\Models\MobileApiToken;
use Closure;
use Illuminate\Http\Request;

class MobileTokenAuth
{
    public function handle(Request $request, Closure $next)
    {
        $raw = $request->bearerToken();
        $token = $raw && strlen($raw) <= 256 ? MobileApiToken::where('token_hash', hash('sha256', $raw))->first() : null;
        abort_unless($token && $token->expires_at->isFuture() && $token->user, 401, 'Invalid or expired access token.');
        $user = $token->user;
        abort_unless(hash_equals($token->auth_fingerprint, MobileApiToken::fingerprint($user)), 401, 'Account security changed. Sign in again.');
        abort_if($user->is_banned, 403, 'Account is restricted.');
        abort_unless($user->is_verified, 403, 'Email verification required.');
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('mobile_token', $token);
        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinutes(5))) {
            $token->update(['last_used_at' => now()]);
        }

        return $next($request);
    }
}
