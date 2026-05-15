<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DebtRequestController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReschedulingController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::post('/webhook/paymob', [WalletController::class, 'handlePaymobWebhook'])
    ->name('webhook.paymob');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.post');
});

Route::middleware(['auth', 'role:admin,creditor,debtor'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/{debt}', [DebtController::class, 'show'])->name('debts.show');

    Route::get('/debt-requests', [DebtRequestController::class, 'index'])->name('debt-requests.index');
    Route::get('/debt-requests/create', [DebtRequestController::class, 'create'])->name('debt-requests.create');
    Route::post('/debt-requests', [DebtRequestController::class, 'store'])->name('debt-requests.store');
    Route::get('/debt-requests/{debtRequest}', [DebtRequestController::class, 'show'])->name('debt-requests.show');

    Route::get('/debts/{debt}/installments', [InstallmentController::class, 'index'])->name('installments.index');
    Route::get('/installments/{installment}/pay', [InstallmentController::class, 'payForm'])->name('installments.pay');
    Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay'])->name('installments.pay.post');

    Route::get('/debts/{debt}/reschedule', [ReschedulingController::class, 'create'])->name('rescheduling.create');
    Route::post('/debts/{debt}/reschedule', [ReschedulingController::class, 'store'])->name('rescheduling.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/debt-pdf', [ReportController::class, 'exportDebtPdf'])->name('reports.debt.pdf');
    Route::get('/reports/export/payments-excel', [ReportController::class, 'exportPaymentExcel'])->name('reports.payments.excel');
});

Route::middleware(['auth', 'role:creditor,debtor'])->prefix('wallet')->name('wallet.')->group(function (): void {
    Route::get('/', [WalletController::class, 'index'])->name('index');
    Route::get('/balance', [WalletController::class, 'showBalance'])->name('balance');
    Route::get('/topup', [WalletController::class, 'topupForm'])->name('topup');
    Route::post('/topup/request', [WalletController::class, 'topupRequest'])->name('topup.request');
    Route::post('/topup/vodafone', [WalletController::class, 'initiateVodafone'])->name('topup.vodafone');
    Route::post('/topup/manual', [WalletController::class, 'submitManual'])->name('topup.manual');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/requests', [DebtRequestController::class, 'adminIndex'])->name('requests.index');
    Route::get('/requests/{debtRequest}', [DebtRequestController::class, 'adminShow'])->name('requests.show');
    Route::post('/requests/{debtRequest}/approve', [DebtRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{debtRequest}/reject', [DebtRequestController::class, 'reject'])->name('requests.reject');

    Route::get('/rescheduling', [ReschedulingController::class, 'adminIndex'])->name('rescheduling.index');
    Route::get('/rescheduling/{reschedule}', [ReschedulingController::class, 'adminShow'])->name('rescheduling.show');
    Route::post('/rescheduling/{reschedule}/approve', [ReschedulingController::class, 'approve'])->name('rescheduling.approve');
    Route::post('/rescheduling/{reschedule}/reject', [ReschedulingController::class, 'reject'])->name('rescheduling.reject');

    Route::get('/reports/admin-pdf', [ReportController::class, 'exportAdminSummaryPdf'])->name('reports.admin.pdf');

    Route::get('/wallet/topups', [WalletController::class, 'adminTopupIndex'])->name('wallet.topups');
    Route::post('/wallet/topups/{topup}/approve', [WalletController::class, 'adminApproveTopup'])->name('wallet.topups.approve');
    Route::post('/wallet/topups/{topup}/reject', [WalletController::class, 'adminRejectTopup'])->name('wallet.topups.reject');

    Route::get('/installments/pending', [InstallmentController::class, 'adminPendingIndex'])->name('installments.pending');
    Route::post('/installments/{installment}/approve', [InstallmentController::class, 'approvePayment'])->name('installments.approve');
});
