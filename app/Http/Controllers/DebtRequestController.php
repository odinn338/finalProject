<?php

namespace App\Http\Controllers;

use App\Models\DebtRequest;
use App\Models\User;
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
        if (! Auth::user()->isDebtor()) {
            abort(403, 'هذه الصفحة مخصصة للمدينين فقط.');
        }

        $requests = DebtRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('debts.requests.index', compact('requests'));
    }

    /** نموذج طلب جديد */
    public function create()
    {
        if (! Auth::user()->isDebtor()) {
            abort(403, 'هذه الصفحة مخصصة للمدينين فقط.');
        }

        return view('debts.requests.create');
    }

    /** حفظ الطلب الجديد */
    public function store(Request $request)
    {
        if (! Auth::user()->isDebtor()) {
            abort(403, 'هذه الصفحة مخصصة للمدينين فقط.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'requested_amount' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'requested_months' => ['required', 'integer', 'min:1', 'max:120'],
        ], [
            'title.required' => 'عنوان الطلب مطلوب.',
            'requested_amount.required' => 'المبلغ المطلوب مطلوب.',
            'requested_amount.min' => 'أقل مبلغ مقبول هو 100.',
            'requested_months.required' => 'عدد الأشهر مطلوب.',
            'requested_months.min' => 'أقل مدة مقبولة شهر واحد.',
            'requested_months.max' => 'أقصى مدة مقبولة 120 شهراً.',
        ]);

        DebtRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return redirect()->route('debt-requests.index')
            ->with('success', 'تم إرسال طلبك بنجاح! سيتم مراجعته من قبل الإدارة.');
    }

    /** تفاصيل الطلب */
    public function show(DebtRequest $debtRequest)
    {
        if (! Auth::user()->isAdmin() && (int) $debtRequest->user_id !== (int) Auth::id()) {
            abort(403, 'غير مصرح لك بعرض هذا الطلب.');
        }

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
        $creditors = User::query()
            ->where('role', 'creditor')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.requests.show', [
            'request' => $debtRequest,
            'creditors' => $creditors,
        ]);
    }

    /** الموافقة على الطلب وإنشاء الدين */
    public function approve(Request $request, DebtRequest $debtRequest)
    {
        if (! $debtRequest->isPending()) {
            return back()->with('error', 'هذا الطلب تمت مراجعته مسبقاً.');
        }

        $validated = $request->validate([
            'approved_amount' => ['required', 'numeric', 'min:1'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'approved_months' => ['required', 'integer', 'min:1', 'max:120'],
            'lender_id' => ['required', 'integer', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'approved_amount.required' => 'المبلغ المعتمد مطلوب.',
            'interest_rate.required' => 'نسبة الفائدة مطلوبة.',
            'interest_rate.max' => 'نسبة الفائدة لا يمكن أن تتجاوز 100%.',
            'approved_months.required' => 'عدد الأشهر مطلوب.',
            'lender_id.required' => 'يجب اختيار الدائن (المقرض) المرتبط بهذا الدين.',
            'lender_id.exists' => 'الدائن المحدد غير موجود.',
        ]);

        $lender = User::query()->find($validated['lender_id']);
        if (! $lender || ! $lender->isCreditor() || ! $lender->isActive()) {
            return back()
                ->withErrors(['lender_id' => 'يجب اختيار حساب دائن نشط.'])
                ->withInput();
        }

        // تحديث المبلغ المعتمد على الطلب أولاً
        $debtRequest->update(['approved_amount' => $validated['approved_amount']]);

        // إنشاء الدين وتوليد الأقساط
        $debt = $this->debtService->approveAndCreateDebt(
            $debtRequest,
            $validated['interest_rate'],
            $validated['approved_months'],
            Auth::id(),
            (int) $validated['lender_id']
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
        if (! $debtRequest->isPending()) {
            return back()->with('error', 'هذا الطلب تمت مراجعته مسبقاً.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ], [
            'admin_notes.required' => 'سبب الرفض مطلوب.',
        ]);

        $debtRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.requests.index')
            ->with('success', 'تم رفض الطلب وإشعار المستخدم.');
    }
}
