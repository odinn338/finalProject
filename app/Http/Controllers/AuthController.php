<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * تسجيل مستخدم جديد (مدين أو دائن) مع إنشاء محفظة في نفس المعاملة.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'national_id' => ['required', 'string', 'max:14', 'regex:/^[0-9]{10,14}$/', 'unique:users,national_id'],
            'role' => ['required', 'in:creditor,debtor'],
        ], [
            'name.required' => 'الاسم مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.unique' => 'هذا البريد مسجّل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'صيغة رقم الهاتف غير صالحة.',
            'national_id.required' => 'الرقم القومي مطلوب.',
            'national_id.regex' => 'الرقم القومي يجب أن يتكوّن من أرقام فقط (10–14 رقماً).',
            'national_id.unique' => 'هذا الرقم القومي مسجّل مسبقاً.',
            'role.required' => 'يجب اختيار نوع الحساب.',
            'role.in' => 'نوع الحساب غير صالح.',
        ]);

        $user = null;

        try {
            DB::transaction(function () use ($validated, &$user): void {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'],
                    'national_id' => $validated['national_id'],
                    'role' => $validated['role'],
                    'status' => 'active',
                ]);

                $this->walletService->createWalletForUser($user);
            });
        } catch (\Throwable) {
            return back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.');
        }

        if (! $user instanceof User) {
            return back()
                ->withInput()
                ->with('error', 'تعذّر إنشاء الحساب. يرجى المحاولة مرة أخرى.');
        }

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'تم إنشاء الحساب بنجاح! مرحباً بك في DebtMate.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors(['email' => 'حسابك موقوف. يرجى التواصل مع الإدارة.'])
                    ->onlyInput('email');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'تم تسجيل الدخول بنجاح. مرحباً بك.');
        }

        return back()
            ->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'تم تسجيل الخروج بنجاح.');
    }
}
