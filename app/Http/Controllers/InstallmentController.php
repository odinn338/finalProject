<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\Installment;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    public function __construct(private DebtService $debtService) {}

    public function index(Debt $debt): View
    {
        $user = Auth::user();

        if (! $user->isAdmin()) {
            $debtorId = (int) ($debt->debtor_id ?? $debt->user_id);
            if ($debtorId !== (int) $user->id && (int) $debt->lender_id !== (int) $user->id) {
                abort(403, 'غير مصرح لك بعرض هذه البيانات.');
            }
        }

        $installments = $debt->installments()->get();

        return view('installments.index', compact('debt', 'installments'));
    }

    /**
     * لوحة المدير: جميع الأقساط بانتظار تأكيد الدفع.
     */
    public function adminPendingIndex(): View
    {
        $installments = Installment::query()
            ->where('status', Installment::STATUS_PENDING_APPROVAL)
            ->with(['debt.lender', 'debt.borrower', 'user'])
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.installments.pending', compact('installments'));
    }

    public function payForm(Installment $installment): View
    {
        $debtorId = (int) ($installment->debt->debtor_id ?? $installment->debt->user_id);

        abort_unless(
            Auth::user()->isDebtor() && $debtorId === (int) Auth::id(),
            403,
            'غير مصرح لك بهذا الإجراء.'
        );

        abort_unless(
            in_array($installment->status, [Installment::STATUS_PENDING, Installment::STATUS_OVERDUE], true),
            422,
            'لا يمكن دفع هذا القسط في حالته الحالية.'
        );

        $installment->load('debt');

        return view('installments.pay', compact('installment'));
    }

    /**
     * المدين يُقدّم طلب سداد — لا يُحرَّك أي رصيد من المحفظة هنا.
     */
    public function pay(Request $request, Installment $installment): RedirectResponse
    {
        $debtorId = (int) ($installment->debt->debtor_id ?? $installment->debt->user_id);

        abort_unless(
            $debtorId === (int) Auth::id(),
            403,
            'غير مصرح لك بتسجيل هذه الدفعة.'
        );

        abort_unless(
            in_array($installment->status, [Installment::STATUS_PENDING, Installment::STATUS_OVERDUE], true),
            422,
            'لا يمكن دفع هذا القسط في حالته الحالية.'
        );

        $validated = $request->validate([
            'reference_number' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'reference_number.required' => 'رقم المرجع أو الإيصال مطلوب.',
        ]);

        $installment->update([
            'status' => Installment::STATUS_PENDING_APPROVAL,
            'payment_reference' => $validated['reference_number'],
            'notes' => $validated['notes'] ?? $installment->notes,
        ]);

        return redirect()
            ->route('debts.show', $installment->debt_id)
            ->with('success', 'تم إرسال طلب الدفع. سيتم تحويل المبلغ بعد موافقة الإدارة.');
    }

    /**
     * المدير يؤكّد الدفع — تحويل المحفظة وتسجيل السداد.
     */
    public function approvePayment(Installment $installment): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'هذا الإجراء مخصص للمدير فقط.');

        abort_unless(
            $installment->status === Installment::STATUS_PENDING_APPROVAL,
            422,
            'هذا القسط لا يحتاج إلى موافقة في الوقت الحالي.'
        );

        try {
            $this->debtService->approveInstallmentPayment($installment, (int) Auth::id());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'تمت الموافقة على الدفع وتحويل المبلغ من محفظة المدين إلى الدائن بنجاح.');
    }
}
