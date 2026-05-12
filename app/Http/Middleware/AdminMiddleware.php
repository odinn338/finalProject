<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * يحمي المسارات المخصصة للمدير فقط
     * يُعيد المستخدم العادي إلى لوحته الخاصة
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'هذه الصفحة مخصصة للمدير فقط.');
        }

        return $next($request);
    }
}
