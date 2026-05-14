<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtRequest;
use App\Models\Installment;
use App\Models\PaymentLog;
use App\Models\ReschedulingRequest;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    /**
     * يعيد لوحة التحكم المناسبة لدور المستخدم
     */
    public function index()
    {
        $user = Auth::user();

        return match (true) {
            $user->isAdmin()    => $this->adminDashboard(),
            $user->isCreditor() => $this->creditorDashboard(),
            $user->isDebtor()   => $this->debtorDashboard(),
            default             => abort(403),
        };
    }

    // ═══════════════════════════════════════════════════
    //  لوحة تحكم المدير
    // ═══════════════════════════════════════════════════

    private function adminDashboard()
    {
        $stats = [
            // ── إحصائيات المستخدمين ──────────────────
            'total_users'     => \App\Models\User::where('role', '!=', 'admin')->count(),
            'total_creditors' => \App\Models\User::where('role', 'creditor')->count(),
            'total_debtors'   => \App\Models\User::where('role', 'debtor')->count(),

            // ── إحصائيات الديون ──────────────────────
            'pending_requests'   => DebtRequest::where('status', 'pending')->count(),
            'pending_reschedule' => ReschedulingRequest::where('status', 'pending')->count(),
            'active_debts'       => Debt::where('status', 'active')->count(),
            'overdue_debts'      => Debt::where('status', 'overdue')->count(),
            'completed_debts'    => Debt::where('status', 'completed')->count(),

            // ── إحصائيات المحفظة ─────────────────────
            'pending_topups'      => WalletTopup::where('status', 'pending_review')->count(),
            'total_portfolio'     => Debt::whereIn('status', ['active', 'overdue'])->sum('total_amount'),
            'total_collected'     => PaymentLog::sum('amount_paid'),
            'monthly_collections' => PaymentLog::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount_paid'),

            // ── بيانات الرسم البياني (6 أشهر) ────────
            'chart' => $this->getMonthlyChartData(),
        ];

        // آخر طلبات الشحن المعلقة
        $pendingTopups = WalletTopup::with('user', 'wallet')
            ->where('status', 'pending_review')
            ->latest()->limit(5)->get();

        // آخر طلبات الديون المعلقة
        $pendingDebts = DebtRequest::with('user')
            ->where('status', 'pending')
            ->latest()->limit(5)->get();

        return view('dashboard.admin', compact('stats', 'pendingTopups', 'pendingDebts'));
    }

    // ═══════════════════════════════════════════════════
    //  لوحة تحكم الدائن
    // ═══════════════════════════════════════════════════

    private function creditorDashboard()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);

        $stats = [
            // ── المحفظة ──────────────────────────────
            'wallet_balance'   => $wallet->available_balance,
            'wallet_reserved'  => $wallet->reserved_balance,
            'total_received'   => $wallet->total_deposited,

            // ── محفظة الإقراض ─────────────────────────
            'total_lent'       => Debt::where('creditor_id', $user->id)->sum('principal_amount'),
            'active_loans'     => Debt::where('creditor_id', $user->id)
                ->where('status', 'active')->count(),
            'total_interest'   => Debt::where('creditor_id', $user->id)
                ->where('status', 'completed')->sum('interest_amount'),

            // ── الأقساط القادمة ───────────────────────
            'due_this_month'   => Installment::whereHas('debt', fn($q) =>
            $q->where('creditor_id', $user->id))
                ->whereMonth('due_date', now()->month)
                ->whereIn('status', ['pending', 'overdue'])
                ->sum('amount'),

            'overdue_count'    => Installment::whereHas('debt', fn($q) =>
            $q->where('creditor_id', $user->id))
                ->where('status', 'overdue')->count(),

            // ── رسم بياني ────────────────────────────
            'chart' => $this->getCreditorMonthlyChart($user->id),
        ];

        // الديون النشطة التي يمتلكها
        $activeLoans = Debt::where('creditor_id', $user->id)
            ->with('user', 'installments')
            ->whereIn('status', ['active', 'overdue'])
            ->latest()->limit(5)->get();

        // آخر حركات المحفظة
        $recentTransactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()->limit(8)->get();

        return view('dashboard.creditor', compact('stats', 'activeLoans', 'recentTransactions', 'wallet'));
    }

    // ═══════════════════════════════════════════════════
    //  لوحة تحكم المدين
    // ═══════════════════════════════════════════════════

    private function debtorDashboard()
    {
        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);

        $stats = [
            // ── المحفظة ──────────────────────────────
            'wallet_balance'   => $wallet->available_balance,
            'wallet_reserved'  => $wallet->reserved_balance,

            // ── الديون ───────────────────────────────
            'total_debt'       => Debt::where('user_id', $user->id)
                ->whereIn('status', ['active', 'overdue'])->sum('remaining_balance'),
            'total_paid'       => Debt::where('user_id', $user->id)->sum('total_paid'),
            'active_debts'     => Debt::where('user_id', $user->id)->where('status', 'active')->count(),

            // ── الأقساط ──────────────────────────────
            'overdue_amount'   => Installment::where('user_id', $user->id)
                ->where('status', 'overdue')->sum('amount'),
            'next_installment' => Installment::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->orderBy('due_date')->first(),

            // ── الطلبات ──────────────────────────────
            'pending_requests' => DebtRequest::where('user_id', $user->id)
                ->where('status', 'pending')->count(),
            'pending_topups'   => WalletTopup::where('user_id', $user->id)
                ->where('status', 'pending_review')->count(),

            // ── رسم بياني ────────────────────────────
            'pie' => [
                'paid'      => Debt::where('user_id', $user->id)->sum('total_paid'),
                'remaining' => Debt::where('user_id', $user->id)
                    ->whereIn('status', ['active', 'overdue'])->sum('remaining_balance'),
            ],
            'chart' => $this->getDebtorMonthlyChart($user->id),
        ];

        // الأقساط القادمة
        $upcomingInstallments = Installment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('debt')->orderBy('due_date')->limit(5)->get();

        // آخر حركات المحفظة
        $recentTransactions = WalletTransaction::where('wallet_id', $wallet->id)
            ->latest()->limit(8)->get();

        return view('dashboard.debtor', compact('stats', 'upcomingInstallments', 'recentTransactions', 'wallet'));
    }

    // ═══════════════════════════════════════════════════
    //  بيانات الرسوم البيانية
    // ═══════════════════════════════════════════════════

    private function getMonthlyChartData(): array
    {
        $labels = $collections = $newDebts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[]      = $date->format('M Y');
            $collections[] = PaymentLog::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');
            $newDebts[]    = Debt::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        }
        return compact('labels', 'collections', 'newDebts');
    }

    private function getCreditorMonthlyChart(int $creditorId): array
    {
        $labels = $received = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[]   = $date->format('M Y');
            $received[] = PaymentLog::whereHas('debt', fn($q) => $q->where('creditor_id', $creditorId))
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');
        }
        return compact('labels', 'received');
    }

    private function getDebtorMonthlyChart(int $debtorId): array
    {
        $labels = $paid = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            $paid[]   = PaymentLog::where('user_id', $debtorId)
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');
        }
        return compact('labels', 'paid');
    }
}
