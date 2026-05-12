<?php

namespace App\Http\Controllers;

use App\Models\DebtRequest;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtRequestController extends Controller
{
    public function __construct(private DebtService $debtService) {}

    // ════════════════════════════════════════════════════════
    //  للمستخدم العادي
    // ════════════════════════════════════════════════════════

    /** قائمة طلباتي */
    public function index()
    {
        $requests = DebtRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('debts.requests.index', compact('requests'));
    }

    /** نموذج طلب جديد */
    public function create()
    {
        return view('debts.requests.create');
    }

    /** حفظ الطلب الجديد */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'requested_amount'  => ['required', 'numeric', 'min:100', 'max:10000000'],
            'requested_months'  => ['required', 'integer', 'min:1', 'max:120'],
        ], [
            'title.required'            => 'عنوان الطلب مطلوب.',
            'requested_amount.required' => 'المبلغ المطلوب مطلوب.',
            'requested_amount.min'      => 'أقل مبلغ مقبول هو 100.',
            'requested_months.required' => 'عدد الأشهر مطلوب.',
            'requested_months.min'      => 'أقل مدة مقبولة شهر واحد.',
            'requested_months.max'      => 'أقصى مدة مقبولة 120 شهراً.',
        ]);

        DebtRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status'  => 'pending',
        ]);

        return redirect()->route('debt-requests.index')
            ->with('success', 'تم إرسال طلبك بنجاح! سيتم مراجعته من قبل الإدارة.');
    }

    /** تفاصيل الطلب */
    public function show(DebtRequest $debtRequest)
    {
        $this->authorize('view', $debtRequest);
        return view('debts.requests.show', ['request' => $debtRequest]);
    }

    // ════════════════════════════════════════════════════════
    //  للمدير
    // ════════════════════════════════════════════════════════

    /** قائمة الطلبات للمدير */
    public function adminIndex(Request $request)
    {
        $query = DebtRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15);
        return view('admin.requests.index', compact('requests'));
    }

    /** صفحة مراجعة الطلب */
    public function adminShow(DebtRequest $debtRequest)
    {
        $debtRequest->load('user');
        return view('admin.requests.show', ['request' => $debtRequest]);
    }

    /** الموافقة على الطلب وإنشاء الدين */
    public function approve(Request $request, DebtRequest $debtRequest)
    {
        if (!$debtRequest->isPending()) {
            return back()->with('error', 'هذا الطلب تمت مراجعته مسبقاً.');
        }

        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1'],
            'interest_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'approved_months' => ['required', 'integer', 'min:1', 'max:120'],
            'admin_notes'     => ['nullable', 'string', 'max:500'],
        ], [
            'approved_amount.required' => 'المبلغ المعتمد مطلوب.',
            'interest_rate.required'   => 'نسبة الفائدة مطلوبة.',
            'interest_rate.max'        => 'نسبة الفائدة لا يمكن أن تتجاوز 100%.',
            'approved_months.required' => 'عدد الأشهر مطلوب.',
        ]);

        // تحديث المبلغ المعتمد على الطلب أولاً
        $debtRequest->update(['approved_amount' => $validated['approved_amount']]);

        // إنشاء الدين وتوليد الأقساط
        $debt = $this->debtService->approveAndCreateDebt(
            $debtRequest,
            $validated['interest_rate'],
            $validated['approved_months'],
            Auth::id()
        );

        if ($validated['admin_notes']) {
            $debtRequest->update(['admin_notes' => $validated['admin_notes']]);
        }

        return redirect()->route('admin.requests.index')
            ->with('success', "تمت الموافقة على الطلب. رقم الدين: {$debt->reference_number}");
    }

    /** رفض الطلب */
    public function reject(Request $request, DebtRequest $debtRequest)
    {
        if (!$debtRequest->isPending()) {
            return back()->with('error', 'هذا الطلب تمت مراجعته مسبقاً.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ], [
            'admin_notes.required' => 'سبب الرفض مطلوب.',
        ]);

        $debtRequest->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.requests.index')
            ->with('success', 'تم رفض الطلب وإشعار المستخدم.');
    }
}
