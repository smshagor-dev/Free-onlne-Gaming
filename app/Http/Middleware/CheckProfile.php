<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckProfile
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
            if (
                empty($user->name) ||
                empty($user->date_of_birth) ||
                empty($user->email) ||
                empty($user->mobile_number)
            ) {
                return redirect()->route('user.profile.edit')
                    ->with('error', 'Please complete your profile first.');
            }
        }

        return $next($request);
    }
}
