<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\DebtRequest;
use App\Models\PaymentLog;
use App\Models\ReschedulingRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isCreditor()) {
            return $this->creditorDashboard($user);
        }

        return $this->debtorDashboard($user);
    }

    private function adminDashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_creditors' => User::where('role', 'creditor')->count(),
            'total_debtors' => User::where('role', 'debtor')->count(),
            'total_debts' => Debt::count(),
            'active_debts' => Debt::where('status', 'active')->count(),
            'overdue_debts' => Debt::where('status', 'overdue')->count(),
            'completed_debts' => Debt::where('status', 'completed')->count(),
            'total_portfolio' => Debt::where('status', '!=', 'completed')->sum('remaining_balance'),
            'pending_requests' => DebtRequest::where('status', 'pending')->count(),
            'pending_reschedule' => ReschedulingRequest::where('status', 'pending')->count(),
            'monthly_collections' => PaymentLog::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount_paid'),
        ];

        $chartData = $this->buildAdminChartData();

        $recentDebts = Debt::with(['lender', 'borrower'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentDebts', 'chartData'));
    }

    /**
     * @return array{labels: array<int, string>, collections: array<int, float|int>, newDebts: array<int, int>}
     */
    private function buildAdminChartData(): array
    {
        $labels = [];
        $collections = [];
        $newDebts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $labels[] = $month->translatedFormat('M Y');
            $collections[] = (float) PaymentLog::query()
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount_paid');
            $newDebts[] = Debt::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            'labels' => $labels,
            'collections' => $collections,
            'newDebts' => $newDebts,
        ];
    }

    private function creditorDashboard(User $user)
    {
        $activeDebts = Debt::query()
            ->where('lender_id', $user->id)
            ->where('status', 'active')
            ->with('borrower')
            ->get();

        $totalLent = Debt::query()
            ->where('lender_id', $user->id)
            ->sum('total_amount');

        $totalReceived = PaymentLog::query()
            ->whereHas('debt', function ($q) use ($user): void {
                $q->where('lender_id', $user->id);
            })
            ->sum('amount_paid');

        $wallet = $user->wallet;

        return view('dashboard.creditor', compact(
            'activeDebts',
            'totalLent',
            'totalReceived',
            'wallet'
        ));
    }

    private function debtorDashboard(User $user)
    {
        $myDebts = Debt::query()
            ->where(function ($q) use ($user): void {
                $q->where('debtor_id', $user->id)
                    ->orWhere(function ($q2) use ($user): void {
                        $q2->whereNull('debtor_id')->where('user_id', $user->id);
                    });
            })
            ->where('status', 'active')
            ->with('lender')
            ->get();

        $totalOwed = Debt::query()
            ->where(function ($q) use ($user): void {
                $q->where('debtor_id', $user->id)
                    ->orWhere(function ($q2) use ($user): void {
                        $q2->whereNull('debtor_id')->where('user_id', $user->id);
                    });
            })
            ->where('status', 'active')
            ->sum('remaining_balance');

        $nextInstallments = Debt::query()
            ->where(function ($q) use ($user): void {
                $q->where('debtor_id', $user->id)
                    ->orWhere(function ($q2) use ($user): void {
                        $q2->whereNull('debtor_id')->where('user_id', $user->id);
                    });
            })
            ->where('status', 'active')
            ->with(['installments' => function ($q): void {
                $q->where('status', 'pending')
                    ->orderBy('due_date')
                    ->take(1);
            }])
            ->get()
            ->pluck('installments')
            ->flatten();

        $wallet = $user->wallet;

        return view('dashboard.debtor', compact(
            'myDebts',
            'totalOwed',
            'nextInstallments',
            'wallet'
        ));
    }
}
