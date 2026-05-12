<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\ReschedulingRequest;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReschedulingController extends Controller
{
    public function __construct(private DebtService $debtService) {}

    /** نموذج طلب إعادة الجدولة */
    public function create(Debt $debt)
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        if (!$debt->canBeRescheduled()) {
            return back()->with('error', 'لا يمكن تقديم طلب إعادة جدولة في الوقت الحالي.');
        }

        $pendingInstallments = $debt->pendingInstallments()->count();
        return view('debts.reschedule.create', compact('debt', 'pendingInstallments'));
    }

    /** حفظ طلب إعادة الجدولة */
    public function store(Request $request, Debt $debt)
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بهذا الإجراء.');
        }

        if (!$debt->canBeRescheduled()) {
            return back()->with('error', 'لا يمكن تقديم طلب إعادة جدولة في الوقت الحالي.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'reason.required' => 'سبب طلب إعادة الجدولة مطلوب.',
            'reason.min'      => 'الرجاء توضيح السبب بشكل كافٍ (20 حرف على الأقل).',
        ]);

        ReschedulingRequest::create([
            'debt_id'                => $debt->id,
            'user_id'                => Auth::id(),
            'reason'                 => $request->reason,
            'outstanding_balance'    => $debt->remaining_balance,
            'remaining_installments' => $debt->pendingInstallments()->count(),
            'status'                 => 'pending',
        ]);

        return redirect()->route('debts.show', $debt->id)
            ->with('success', 'تم إرسال طلب إعادة الجدولة بنجاح. سيتم مراجعته من قبل الإدارة.');
    }

    /** قائمة طلبات إعادة الجدولة للمدير */
    public function adminIndex()
    {
        $requests = ReschedulingRequest::with('user', 'debt')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin.rescheduling.index', compact('requests'));
    }

    /** مراجعة طلب إعادة الجدولة */
    public function adminShow(ReschedulingRequest $reschedule)
    {
        $reschedule->load('user', 'debt.installments');
        return view('admin.rescheduling.show', compact('reschedule'));
    }

    /** الموافقة وتنفيذ إعادة الجدولة */
    public function approve(Request $request, ReschedulingRequest $reschedule)
    {
        if (!$reschedule->isPending()) {
            return back()->with('error', 'هذا الطلب تمت مراجعته مسبقاً.');
        }

        $validated = $request->validate([
            'new_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'new_months'        => ['required', 'integer', 'min:1', 'max:120'],
            'admin_notes'       => ['nullable', 'string', 'max:500'],
        ], [
            'new_interest_rate.required' => 'نسبة الفائدة الجديدة مطلوبة.',
            'new_months.required'        => 'عدد الأشهر الجديدة مطلوب.',
        ]);

        $reschedule->update(['admin_notes' => $validated['admin_notes']]);

        $debt = $this->debtService->approveRescheduling(
            $reschedule,
            $validated['new_interest_rate'],
            $validated['new_months'],
            Auth::id()
        );

        return redirect()->route('admin.rescheduling.index')
            ->with('success', "تمت إعادة الجدولة بنجاح. القسط الجديد: " . number_format($debt->monthly_installment, 2) . " ج.م");
    }

    /** رفض طلب إعادة الجدولة */
    public function reject(Request $request, ReschedulingRequest $reschedule)
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ], [
            'admin_notes.required' => 'سبب الرفض مطلوب.',
        ]);

        $reschedule->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.rescheduling.index')
            ->with('success', 'تم رفض طلب إعادة الجدولة.');
    }
}
