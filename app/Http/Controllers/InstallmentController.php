<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Installment;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstallmentController extends Controller
{
    public function __construct(private DebtService $debtService) {}

    /** جدول أقساط دين معين */
    public function index(Debt $debt)
    {
        // التحقق من الصلاحية
        if (!Auth::user()->isAdmin() && $debt->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بعرض هذه البيانات.');
        }

        $installments = $debt->installments()->get();
        return view('installments.index', compact('debt', 'installments'));
    }

    /** نموذج تسجيل السداد */
    public function payForm(Installment $installment)
    {
        if (!Auth::user()->isAdmin() && $installment->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        if ($installment->status === 'paid') {
            return back()->with('error', 'هذا القسط مسدد بالفعل.');
        }

        return view('installments.pay', compact('installment'));
    }

    /** تسجيل الدفع */
    public function pay(Request $request, Installment $installment)
    {
        if (!Auth::user()->isAdmin() && $installment->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        if ($installment->status === 'paid') {
            return back()->with('error', 'هذا القسط مسدد بالفعل.');
        }

        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:1', 'max:' . $installment->remaining_amount],
            'payment_method'   => ['required', 'in:cash,bank_transfer,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required'         => 'مبلغ الدفع مطلوب.',
            'amount.min'              => 'أقل مبلغ للدفع هو 1.',
            'amount.max'              => 'المبلغ المدخل يتجاوز المبلغ المتبقي للقسط.',
            'payment_method.required' => 'طريقة الدفع مطلوبة.',
            'payment_method.in'       => 'طريقة الدفع غير صالحة.',
        ]);

        $this->debtService->recordPayment(
            $installment,
            $validated['amount'],
            $validated['payment_method'],
            Auth::id(),
            $validated['reference_number'] ?? null
        );

        return redirect()->route('debts.show', $installment->debt_id)
            ->with('success', 'تم تسجيل الدفع بنجاح. مبلغ: ' . number_format($validated['amount'], 2) . ' ج.م');
    }
}
