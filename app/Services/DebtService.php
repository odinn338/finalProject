<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\DebtRequest;
use App\Models\Installment;
use App\Models\PaymentLog;
use App\Models\ReschedulingRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DebtService
{
    // ════════════════════════════════════════════════════════
    //  1. إنشاء الدين وتوليد الأقساط عند الموافقة
    // ════════════════════════════════════════════════════════

    /**
     * يُنفَّذ عند موافقة المدير على طلب الدين
     *
     * الصيغة المحاسبية:
     *   interest_amount      = principal × (rate / 100)
     *   total_amount         = principal + interest_amount
     *   monthly_installment  = total_amount / months
     *
     * @param DebtRequest $request  طلب الدين المعتمد
     * @param float       $rate     نسبة الفائدة %
     * @param int         $months   عدد الأشهر
     * @param int         $adminId  معرّف المدير الموافق
     * @return Debt
     */
    public function approveAndCreateDebt(DebtRequest $request, float $rate, int $months, int $adminId): Debt
    {
        return DB::transaction(function () use ($request, $rate, $months, $adminId) {

            // ── حساب المبالغ ─────────────────────────────
            $principal    = $request->approved_amount ?? $request->requested_amount;
            $interest     = round($principal * ($rate / 100), 2);
            $total        = $principal + $interest;
            $monthly      = round($total / $months, 2);

            // ── تعديل آخر قسط لتفادي فروق التقريب ────────
            $roundingDiff = $total - ($monthly * $months);

            // ── تحديث الطلب ───────────────────────────────
            $request->update([
                'status'          => 'approved',
                'interest_rate'   => $rate,
                'approved_months' => $months,
                'reviewed_by'     => $adminId,
                'reviewed_at'     => now(),
            ]);

            // ── إنشاء سجل الدين ───────────────────────────
            $debt = Debt::create([
                'user_id'             => $request->user_id,
                'debt_request_id'     => $request->id,
                'reference_number'    => $this->generateReferenceNumber(),
                'principal_amount'    => $principal,
                'interest_rate'       => $rate,
                'interest_amount'     => $interest,
                'total_amount'        => $total,
                'monthly_installment' => $monthly,
                'total_paid'          => 0,
                'remaining_balance'   => $total,
                'total_months'        => $months,
                'paid_months'         => 0,
                'start_date'          => today(),
                'end_date'            => today()->addMonths($months),
                'status'              => 'active',
            ]);

            // ── توليد جدول الأقساط ────────────────────────
            $this->generateInstallments($debt, $monthly, $months, $roundingDiff);

            return $debt;
        });
    }

    // ════════════════════════════════════════════════════════
    //  2. توليد جدول الأقساط الشهرية
    // ════════════════════════════════════════════════════════

    /**
     * ينشئ سجلاً لكل قسط شهري مع تاريخ الاستحقاق
     * القسط الأخير يُضاف إليه فارق التقريب لضمان التطابق الكامل
     *
     * @param Debt  $debt           سجل الدين
     * @param float $monthly        القسط الشهري
     * @param int   $months         عدد الأشهر
     * @param float $roundingDiff   فرق التقريب يُضاف للقسط الأخير
     */
    private function generateInstallments(Debt $debt, float $monthly, int $months, float $roundingDiff): void
    {
        $installments = [];
        $startDate    = Carbon::parse($debt->start_date);

        for ($i = 1; $i <= $months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i);
            $amount  = ($i === $months) ? round($monthly + $roundingDiff, 2) : $monthly;

            $installments[] = [
                'debt_id'            => $debt->id,
                'user_id'            => $debt->user_id,
                'installment_number' => $i,
                'amount'             => $amount,
                'paid_amount'        => 0,
                'penalty_amount'     => 0,
                'due_date'           => $dueDate->toDateString(),
                'status'             => 'pending',
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }

        Installment::insert($installments);
    }

    // ════════════════════════════════════════════════════════
    //  3. تسجيل سداد قسط
    // ════════════════════════════════════════════════════════

    /**
     * يسجّل عملية دفع لقسط معين ويحدّث أرصدة الدين
     *
     * @param Installment $installment  القسط المراد تسديده
     * @param float       $amount       المبلغ المدفوع
     * @param string      $method       طريقة الدفع
     * @param int         $recorderId   معرّف من سجّل الدفع
     * @param string|null $reference    رقم مرجع الدفع
     * @return PaymentLog
     */
    public function recordPayment(
        Installment $installment,
        float $amount,
        string $method,
        int $recorderId,
        ?string $reference = null
    ): PaymentLog {
        return DB::transaction(function () use ($installment, $amount, $method, $recorderId, $reference) {

            $debt        = $installment->debt;
            $totalDue    = $installment->amount + $installment->penalty_amount;
            $newPaid     = $installment->paid_amount + $amount;

            // ── تحديد الحالة الجديدة للقسط ───────────────
            $newStatus = match (true) {
                $newPaid >= $totalDue => 'paid',
                $newPaid > 0          => 'partial',
                default               => $installment->status,
            };

            $installment->update([
                'paid_amount' => $newPaid,
                'status'      => $newStatus,
                'paid_date'   => $newStatus === 'paid' ? today() : $installment->paid_date,
                'recorded_by' => $recorderId,
            ]);

            // ── تحديث رصيد الدين ────────────────────────
            $debt->increment('total_paid', $amount);
            $debt->decrement('remaining_balance', $amount);

            if ($newStatus === 'paid') {
                $debt->increment('paid_months');
            }

            // ── التحقق من اكتمال الدين ──────────────────
            if ($debt->remaining_balance <= 0) {
                $debt->update(['status' => 'completed', 'remaining_balance' => 0]);
            }

            // ── تسجيل سجل الدفع ─────────────────────────
            $log = PaymentLog::create([
                'installment_id'  => $installment->id,
                'debt_id'         => $debt->id,
                'user_id'         => $debt->user_id,
                'recorded_by'     => $recorderId,
                'amount_paid'     => $amount,
                'payment_method'  => $method,
                'reference_number' => $reference,
                'payment_date'    => now(),
            ]);

            return $log;
        });
    }

    // ════════════════════════════════════════════════════════
    //  4. الموافقة على إعادة الجدولة
    // ════════════════════════════════════════════════════════

    /**
     * منطق إعادة الجدولة:
     *   أ) يحسب الرصيد غير المسدد (الأقساط المتبقية غير المسددة)
     *   ب) يلغي جميع الأقساط القديمة غير المسددة (status = voided)
     *   ج) يطبق الفائدة الجديدة على الرصيد المتبقي
     *   د) يولّد جدول أقساط جديد
     *
     * @param ReschedulingRequest $reschedule  طلب إعادة الجدولة
     * @param float               $newRate     نسبة الفائدة الجديدة %
     * @param int                 $newMonths   عدد الأشهر الجديدة
     * @param int                 $adminId     معرّف المدير
     * @return Debt
     */
    public function approveRescheduling(
        ReschedulingRequest $reschedule,
        float $newRate,
        int $newMonths,
        int $adminId
    ): Debt {
        return DB::transaction(function () use ($reschedule, $newRate, $newMonths, $adminId) {

            $debt = $reschedule->debt;

            // ── أ) حساب الرصيد المتبقي الفعلي ────────────
            $outstandingBalance = $debt->remaining_balance;

            // ── ب) إلغاء الأقساط القديمة غير المسددة ─────
            $debt->installments()
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->update([
                    'status'     => 'voided',
                    'updated_at' => now(),
                ]);

            // ── ج) حساب القسط الجديد ─────────────────────
            //  الرصيد المتبقي هو أصل الدين الجديد
            $newInterest = round($outstandingBalance * ($newRate / 100), 2);
            $newTotal    = $outstandingBalance + $newInterest;
            $newMonthly  = round($newTotal / $newMonths, 2);
            $diff        = $newTotal - ($newMonthly * $newMonths);

            // ── د) تحديث سجل الدين ───────────────────────
            $debt->update([
                'interest_rate'       => $newRate,
                'interest_amount'     => $debt->interest_amount + $newInterest,
                'total_amount'        => $debt->total_paid + $newTotal,
                'monthly_installment' => $newMonthly,
                'remaining_balance'   => $newTotal,
                'total_months'        => $debt->paid_months + $newMonths,
                'end_date'            => today()->addMonths($newMonths),
                'status'              => 'active',
            ]);

            // ── هـ) توليد جدول أقساط جديد ────────────────
            $this->generateInstallments($debt, $newMonthly, $newMonths, $diff);

            // ── و) تحديث طلب إعادة الجدولة ───────────────
            $reschedule->update([
                'status'                  => 'approved',
                'new_interest_rate'       => $newRate,
                'new_months'              => $newMonths,
                'new_monthly_installment' => $newMonthly,
                'reviewed_by'             => $adminId,
                'reviewed_at'             => now(),
            ]);

            return $debt->fresh();
        });
    }

    // ════════════════════════════════════════════════════════
    //  5. تحديث الأقساط المتأخرة (يُشغَّل في Scheduled Task)
    // ════════════════════════════════════════════════════════

    /**
     * يُحدّث حالة الأقساط التي تجاوزت تاريخ الاستحقاق إلى "متأخر"
     * ويُحدّث حالة الدين المرتبط إذا كان هناك تأخر
     */
    public function markOverdueInstallments(): int
    {
        $count = Installment::where('status', 'pending')
            ->where('due_date', '<', today())
            ->update(['status' => 'overdue', 'updated_at' => now()]);

        // تحديث الديون المرتبطة بأقساط متأخرة
        $debtIds = Installment::where('status', 'overdue')
            ->distinct()
            ->pluck('debt_id');

        Debt::whereIn('id', $debtIds)
            ->where('status', 'active')
            ->update(['status' => 'overdue']);

        return $count;
    }

    // ════════════════════════════════════════════════════════
    //  6. الإحصائيات للوحة التحكم
    // ════════════════════════════════════════════════════════

    /**
     * إحصائيات لوحة تحكم المستخدم
     */
    public function getUserDashboardStats(int $userId): array
    {
        $debts = Debt::where('user_id', $userId)->where('status', '!=', 'completed');

        return [
            'total_debt'      => $debts->sum('total_amount'),
            'total_paid'      => $debts->sum('total_paid'),
            'remaining'       => $debts->sum('remaining_balance'),
            'overdue_amount'  => Installment::where('user_id', $userId)
                ->where('status', 'overdue')
                ->sum('amount'),
            'active_debts'    => Debt::where('user_id', $userId)->where('status', 'active')->count(),
            'pending_requests' => DebtRequest::where('user_id', $userId)->where('status', 'pending')->count(),
            'next_installment' => Installment::where('user_id', $userId)
                ->whereIn('status', ['pending', 'overdue'])
                ->orderBy('due_date')
                ->first(),
        ];
    }

    /**
     * إحصائيات لوحة تحكم المدير
     */
    public function getAdminDashboardStats(): array
    {
        return [
            'total_users'          => \App\Models\User::where('role', 'user')->count(),
            'pending_requests'     => DebtRequest::where('status', 'pending')->count(),
            'pending_reschedule'   => ReschedulingRequest::where('status', 'pending')->count(),
            'total_portfolio'      => Debt::where('status', '!=', 'completed')->sum('total_amount'),
            'total_collected'      => PaymentLog::sum('amount_paid'),
            'overdue_debts'        => Debt::where('status', 'overdue')->count(),
            'active_debts'         => Debt::where('status', 'active')->count(),
            'completed_debts'      => Debt::where('status', 'completed')->count(),
            'monthly_collections'  => PaymentLog::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount_paid'),
        ];
    }

    // ════════════════════════════════════════════════════════
    //  7. Helpers
    // ════════════════════════════════════════════════════════

    /**
     * توليد رقم مرجعي فريد للدين بصيغة: DM-YYYY-XXXXXX
     */
    private function generateReferenceNumber(): string
    {
        do {
            $ref = 'DM-' . now()->year . '-' . strtoupper(Str::random(6));
        } while (Debt::where('reference_number', $ref)->exists());

        return $ref;
    }
}
