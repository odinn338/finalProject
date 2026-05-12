<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\PaymentLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /** صفحة التقارير */
    public function index()
    {
        return view('reports.index');
    }

    // ════════════════════════════════════════════════════════
    //  تصدير PDF - ملخص ديون مستخدم
    // ════════════════════════════════════════════════════════

    /**
     * تصدير ملخص الديون إلى PDF
     * يستخدم مكتبة DomPDF عبر barryvdh/laravel-dompdf
     */
    public function exportDebtPdf(Request $request)
    {
        $userId = Auth::user()->isAdmin() ? ($request->user_id ?? Auth::id()) : Auth::id();
        $user   = User::findOrFail($userId);

        $debts = Debt::where('user_id', $userId)
            ->with('installments', 'debtRequest')
            ->get();

        $totalDebt      = $debts->sum('total_amount');
        $totalPaid      = $debts->sum('total_paid');
        $totalRemaining = $debts->sum('remaining_balance');

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.pdf.debt-summary', compact(
            'user',
            'debts',
            'totalDebt',
            'totalPaid',
            'totalRemaining'
        ));

        $pdf->setPaper('A4', 'portrait');

        $filename = 'debt-summary-' . $user->id . '-' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    // ════════════════════════════════════════════════════════
    //  تصدير Excel - سجل الدفعات
    // ════════════════════════════════════════════════════════

    /**
     * تصدير سجل الدفعات إلى Excel
     * يستخدم مكتبة Maatwebsite/Laravel-Excel
     */
    public function exportPaymentExcel(Request $request)
    {
        $userId = Auth::user()->isAdmin() ? ($request->user_id ?? null) : Auth::id();

        $query = PaymentLog::with('user', 'debt', 'installment', 'recorder')
            ->latest('payment_date');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }

        $logs = $query->get();

        // بناء CSV مباشرة (بديل بسيط بدون مكتبة خارجية إضافية)
        $filename = 'payment-log-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // BOM لدعم اللغة العربية في Excel
            fwrite($file, "\xEF\xBB\xBF");

            // رأس الجدول
            fputcsv($file, [
                'رقم المرجع',
                'اسم المستخدم',
                'رقم الدين',
                'مبلغ القسط',
                'المبلغ المدفوع',
                'طريقة الدفع',
                'رقم مرجع الدفع',
                'تاريخ الدفع',
                'سُجِّل بواسطة',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user->name ?? '-',
                    $log->debt->reference_number ?? '-',
                    number_format($log->installment->amount ?? 0, 2),
                    number_format($log->amount_paid, 2),
                    $log->payment_method_arabic,
                    $log->reference_number ?? '-',
                    $log->payment_date->format('Y-m-d H:i'),
                    $log->recorder->name ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ════════════════════════════════════════════════════════
    //  تصدير PDF - ملخص تنفيذي للمدير
    // ════════════════════════════════════════════════════════

    public function exportAdminSummaryPdf()
    {
        $this->authorizeAdmin();

        $data = [
            'total_users'     => User::where('role', 'user')->count(),
            'total_portfolio' => Debt::sum('total_amount'),
            'total_collected' => PaymentLog::sum('amount_paid'),
            'active_debts'    => Debt::where('status', 'active')->count(),
            'overdue_debts'   => Debt::where('status', 'overdue')->count(),
            'top_debtors'     => Debt::with('user')
                ->where('status', 'active')
                ->orderByDesc('remaining_balance')
                ->limit(10)
                ->get(),
            'generated_at'    => now()->format('Y-m-d H:i'),
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.pdf.admin-summary', compact('data'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('admin-summary-' . now()->format('Ymd') . '.pdf');
    }

    private function authorizeAdmin(): void
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }
    }
}
