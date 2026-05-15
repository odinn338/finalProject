<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        if (! Auth::user()->isAdmin()) {
            abort(403, 'هذه الصفحة مخصصة للمدير فقط.');
        }

        if (! Auth::user()->isActive()) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'حسابك موقوف. يرجى التواصل مع الإدارة.');
        }

        return $next($request);
    }
}
