<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware لحماية المسارات بناءً على الأدوار
 *
 * الاستخدام في web.php:
 *   Route::middleware('role:admin')           // مدير فقط
 *   Route::middleware('role:creditor')         // دائن فقط
 *   Route::middleware('role:debtor')           // مدين فقط
 *   Route::middleware('role:admin,creditor')   // مدير أو دائن
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // التحقق من تسجيل الدخول أولاً
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $user = Auth::user();

        // التحقق من أن الحساب نشط
        if (! $user->isActive()) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'حسابك موقوف. يرجى التواصل مع الإدارة.');
        }

        // التحقق من الدور
        if (! $user->hasRole($roles)) {
            // توجيه لصفحة مناسبة بدلاً من 403 مباشرة
            return redirect()->route('dashboard')
                ->with('error', 'ليس لديك صلاحية للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
