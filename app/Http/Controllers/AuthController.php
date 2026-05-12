<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ════════════════════════════════════════════════════════
    //  تسجيل الدخول
    // ════════════════════════════════════════════════════════

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.email'       => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
        ]);

        // التحقق من حالة الحساب قبل المصادقة
        $user = User::where('email', $request->email)->first();

        if ($user && $user->status === 'suspended') {
            return back()->withErrors([
                'email' => 'حسابك موقوف. يرجى التواصل مع الإدارة.',
            ])->withInput($request->only('email'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'مرحباً بك ' . Auth::user()->name . '! تم تسجيل الدخول بنجاح.');
        }

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->withInput($request->only('email'));
    }

    // ════════════════════════════════════════════════════════
    //  إنشاء حساب جديد
    // ════════════════════════════════════════════════════════

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'national_id' => ['nullable', 'string', 'max:20', 'unique:users'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:500'],
            'password'    => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required'         => 'الاسم الكامل مطلوب.',
            'name.max'              => 'الاسم يجب ألا يتجاوز 255 حرفاً.',
            'email.required'        => 'البريد الإلكتروني مطلوب.',
            'email.email'           => 'صيغة البريد الإلكتروني غير صحيحة.',
            'email.unique'          => 'هذا البريد الإلكتروني مسجّل مسبقاً.',
            'national_id.unique'    => 'رقم الهوية مسجّل مسبقاً.',
            'password.required'     => 'كلمة المرور مطلوبة.',
            'password.confirmed'    => 'كلمة المرور وتأكيدها غير متطابقتين.',
            'password.min'          => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.letters'      => 'كلمة المرور يجب أن تحتوي على أحرف.',
            'password.numbers'      => 'كلمة المرور يجب أن تحتوي على أرقام.',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'national_id' => $request->national_id,
            'phone'       => $request->phone,
            'address'     => $request->address,
            'password'    => Hash::make($request->password),
            'role'        => 'user',
            'status'      => 'active',
            'credit_score' => 100.00,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'تم إنشاء حسابك بنجاح! مرحباً بك في Debt Mate.');
    }

    // ════════════════════════════════════════════════════════
    //  تسجيل الخروج
    // ════════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
