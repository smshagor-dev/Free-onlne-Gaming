<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin*')) {

            $email = session('admin_email');
            $password = session('admin_password');

            if (!$email || !$password) {
                return redirect ('/'); 
            }

            $admin = Admin::where('email', $email)->first();

            if (!$admin || !password_verify($password, $admin->password)) {
                return redirect('/'); 
            }

        }

        return $next($request);
    }
}
