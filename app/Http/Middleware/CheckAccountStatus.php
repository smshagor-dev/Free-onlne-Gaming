<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();

        if ($user && $user->is_banned == 1) {

            // List of allowed routes for banned users
            $allowedRoutes = [
                'user.dashboard',
                'user.profile',
                'user.profile.edit',
                'user.profile.update',
                'user.profile.changePassword',
                'user.notifications.index',
                'user.notifications.store',
                'user.notifications.read',
                'user.unban.form',
                'user.unban.submit',
            ];

            if (!in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('user.dashboard')
                    ->with('error', 'Your account is locked. Please submit an unban request.');
            }
        }


        return $next($request);
    }
}
