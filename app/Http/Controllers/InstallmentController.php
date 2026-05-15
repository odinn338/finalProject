<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Services\DebtService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InstallmentController extends Controller
{
    public function __construct(protected DebtService $debtService) {}

    // ── Existing methods (show, index, …) stay untouched above/below ─────────

    /**
     * User initiates payment → moves installment to `pending_approval`.
     * The actual wallet transfer is deferred until an Admin approves it.
     */
    public function pay(Request $request, Installment $installment): RedirectResponse
    {
        // Guard: only the debtor of this debt may submit payment
        abort_unless(
            $installment->debt->debtor_id === auth()->id(),
            403,
            'غير مصرح لك بتسجيل هذه الدفعة.'
        );

        // Guard: only actionable if currently pending
        abort_unless(
            $installment->status === Installment::STATUS_PENDING ||
                $installment->status === Installment::STATUS_OVERDUE,
            422,
            'لا يمكن دفع هذه القسط في حالته الحالية.'
        );

        $validated = $request->validate([
            'reference_number' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $installment->update([
            'status' => Installment::STATUS_PENDING_APPROVAL,
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إرسال طلب الدفع وبانتظار تأكيد الأدمن.');
    }

    /**
     * Admin confirms the payment → executes the wallet transfer via DebtService.
     * Route is protected by the `admin` middleware (see routes/web.php).
     */
    public function approvePayment(Installment $installment): RedirectResponse
    {
        // Secondary gate check — defence-in-depth on top of the route middleware
        abort_unless(auth()->user()?->is_admin, 403, 'هذا الإجراء مخصص للمسؤولين فقط.');

        abort_unless(
            $installment->status === Installment::STATUS_PENDING_APPROVAL,
            422,
            'هذه القسط لا تحتاج إلى موافقة في الوقت الحالي.'
        );

        // Delegate to the existing DebtService — wallet transfer happens here.
        // Uses lender_id / debtor_id as already defined in the service.
        $this->debtService->recordPayment($installment);

        return redirect()
            ->back()
            ->with('success', 'تمت الموافقة على الدفع وتحويل المبلغ بنجاح.');
    }
    public function payForm(Installment $installment): \Illuminate\View\View
    {
        abort_unless(
            $installment->debt->debtor_id === auth()->id() || auth()->user()->is_admin,
            403
        );

        return view('installments.pay', compact('installment'));
    }
}
