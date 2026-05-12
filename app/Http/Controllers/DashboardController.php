<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\PaymentLog;
use App\Services\DebtService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private DebtService $debtService) {}

    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $stats = $this->debtService->getAdminDashboardStats();

            // بيانات الرسوم البيانية - آخر 6 أشهر
            $chartData = $this->getAdminChartData();

            return view('dashboard.admin', compact('stats', 'chartData'));
        }

        $stats = $this->debtService->getUserDashboardStats($user->id);

        // آخر المعاملات
        $recentPayments = PaymentLog::where('user_id', $user->id)
            ->with('installment', 'debt')
            ->latest('payment_date')
            ->limit(5)
            ->get();

        // الأقساط القادمة
        $upcomingInstallments = Installment::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->with('debt')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // بيانات الرسم الدائري
        $pieData = [
            'paid'      => $stats['total_paid'],
            'remaining' => $stats['remaining'],
        ];

        // بيانات الرسم الشهري (آخر 6 أشهر)
        $monthlyData = $this->getUserMonthlyChart($user->id);

        return view('dashboard.user', compact(
            'stats',
            'recentPayments',
            'upcomingInstallments',
            'pieData',
            'monthlyData'
        ));
    }

    private function getUserMonthlyChart(int $userId): array
    {
        $months = [];
        $amounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');
            $amounts[] = PaymentLog::where('user_id', $userId)
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');
        }

        return ['labels' => $months, 'data' => $amounts];
    }

    private function getAdminChartData(): array
    {
        $months = [];
        $collections = [];
        $newDebts = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('M Y');
            $collections[] = PaymentLog::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount_paid');
            $newDebts[] = \App\Models\Debt::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        }

        return ['labels' => $months, 'collections' => $collections, 'newDebts' => $newDebts];
    }
}
