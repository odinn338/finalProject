<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DebtRequestController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\ReschedulingController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// ════════════════════════════════════════════════════════
//  المسارات العامة (بدون مصادقة)
// ════════════════════════════════════════════════════════

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// ════════════════════════════════════════════════════════
//  المسارات المحمية (تطلب تسجيل الدخول)
// ════════════════════════════════════════════════════════

Route::middleware('auth')->group(function () {

    // تسجيل الخروج
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // لوحة التحكم
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── الديون (عرض وتفاصيل) ───────────────────────────
    Route::get('/debts',         [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/{debt}',  [DebtController::class, 'show'])->name('debts.show');

    // ── طلبات الديون ────────────────────────────────────
    Route::get('/debt-requests',              [DebtRequestController::class, 'index'])->name('debt-requests.index');
    Route::get('/debt-requests/create',       [DebtRequestController::class, 'create'])->name('debt-requests.create');
    Route::post('/debt-requests',             [DebtRequestController::class, 'store'])->name('debt-requests.store');
    Route::get('/debt-requests/{debtRequest}', [DebtRequestController::class, 'show'])->name('debt-requests.show');

    // ── الأقساط ─────────────────────────────────────────
    Route::get('/debts/{debt}/installments',          [InstallmentController::class, 'index'])->name('installments.index');
    Route::get('/installments/{installment}/pay',     [InstallmentController::class, 'payForm'])->name('installments.pay');
    Route::post('/installments/{installment}/pay',    [InstallmentController::class, 'pay'])->name('installments.pay.post');

    // ── إعادة الجدولة ────────────────────────────────────
    Route::get('/debts/{debt}/reschedule',            [ReschedulingController::class, 'create'])->name('rescheduling.create');
    Route::post('/debts/{debt}/reschedule',           [ReschedulingController::class, 'store'])->name('rescheduling.store');

    // ── التقارير ─────────────────────────────────────────
    Route::get('/reports',                            [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/debt-pdf',            [ReportController::class, 'exportDebtPdf'])->name('reports.debt.pdf');
    Route::get('/reports/export/payments-excel',      [ReportController::class, 'exportPaymentExcel'])->name('reports.payments.excel');

    // ════════════════════════════════════════════════════════
    //  مسارات المدير فقط
    // ════════════════════════════════════════════════════════

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        // طلبات الديون
        Route::get('/requests',                          [DebtRequestController::class, 'adminIndex'])->name('requests.index');
        Route::get('/requests/{debtRequest}',            [DebtRequestController::class, 'adminShow'])->name('requests.show');
        Route::post('/requests/{debtRequest}/approve',   [DebtRequestController::class, 'approve'])->name('requests.approve');
        Route::post('/requests/{debtRequest}/reject',    [DebtRequestController::class, 'reject'])->name('requests.reject');

        // إعادة الجدولة
        Route::get('/rescheduling',                      [ReschedulingController::class, 'adminIndex'])->name('rescheduling.index');
        Route::get('/rescheduling/{reschedule}',         [ReschedulingController::class, 'adminShow'])->name('rescheduling.show');
        Route::post('/rescheduling/{reschedule}/approve', [ReschedulingController::class, 'approve'])->name('rescheduling.approve');
        Route::post('/rescheduling/{reschedule}/reject', [ReschedulingController::class, 'reject'])->name('rescheduling.reject');

        // التقارير
        Route::get('/reports/admin-pdf',                 [ReportController::class, 'exportAdminSummaryPdf'])->name('reports.admin.pdf');
    });
});
