<?php

namespace App\Http\Controllers;

use App\Models\WalletTopup;
use App\Services\PaymobService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function __construct(
        private WalletService  $walletService,
        private PaymobService  $paymobService,
    ) {}

    // ═══════════════════════════════════════════════════
    //  صفحة المحفظة الرئيسية
    // ═══════════════════════════════════════════════════

    public function index()
    {
        $user    = Auth::user();
        $summary = $this->walletService->getWalletSummary($user);

        $topups = WalletTopup::where('user_id', $user->id)
            ->latest()->paginate(10);

        return view('wallet.index', compact('summary', 'topups'));
    }

    // ═══════════════════════════════════════════════════
    //  شحن المحفظة — نموذج
    // ═══════════════════════════════════════════════════

    public function topupForm()
    {
        $wallet = $this->walletService->getUserWallet(Auth::user());
        return view('wallet.topup', compact('wallet'));
    }

    // ═══════════════════════════════════════════════════
    //  شحن المحفظة — المسار الأول: فودافون كاش
    // ═══════════════════════════════════════════════════

    /**
     * يُنشئ طلب شحن ثم يُطلق تدفق Paymob Vodafone Cash.
     * POST /wallet/topup/vodafone
     */
    public function initiateVodafone(Request $request)
    {
        $validated = $request->validate([
            'amount'        => ['required', 'numeric', 'min:10', 'max:50000'],
            'wallet_phone'  => ['required', 'string', 'regex:/^01[0-9]{9}$/'],
        ], [
            'amount.required'       => 'المبلغ مطلوب.',
            'amount.min'            => 'أقل مبلغ للشحن هو 10 ج.م.',
            'amount.max'            => 'أقصى مبلغ للشحن هو 50,000 ج.م.',
            'wallet_phone.required' => 'رقم محفظة فودافون كاش مطلوب.',
            'wallet_phone.regex'    => 'رقم الهاتف غير صحيح. يجب أن يبدأ بـ 01 ويتكوّن من 11 رقماً.',
        ]);

        $user = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);

        try {
            // إنشاء طلب الشحن بحالة pending_gateway
            $topup = WalletTopup::create([
                'wallet_id'      => $wallet->id,
                'user_id'        => $user->id,
                'amount'         => $validated['amount'],
                'currency'       => 'EGP',
                'payment_method' => 'vodafone_cash',
                'status'         => 'pending_gateway',
            ]);

            // بناء بيانات المستخدم للفاتورة
            $nameParts = explode(' ', $user->name, 2);
            $userInfo  = [
                'email'      => $user->email,
                'first_name' => $nameParts[0],
                'last_name'  => $nameParts[1] ?? 'User',
                'phone'      => $user->phone ?? $validated['wallet_phone'],
                'address'    => $user->address ?? 'Egypt',
            ];

            // إطلاق تدفق Paymob (STEPS 1-4)
            $result = $this->paymobService->initiateVodafonePayment(
                amountEGP: (float) $validated['amount'],
                topupId: $topup->id,
                userInfo: $userInfo,
                walletPhone: $validated['wallet_phone']
            );

            // حفظ order_id و payment_key للربط عند الـ Webhook
            $topup->update([
                'gateway_order_id' => $result['order_id'],
                'paymob_token'     => $result['payment_key'],
                'gateway_provider' => 'paymob_vodafone',
            ]);

            // إذا كان هناك redirect_url → أعِد التوجيه
            if ($result['redirect_url']) {
                return redirect()->away($result['redirect_url']);
            }

            return redirect()->route('wallet.index')
                ->with('info', 'تم إرسال طلب الدفع. ستصلك رسالة SMS للتأكيد على رقم فودافون كاش.');
        } catch (\Exception $e) {
            Log::error('Vodafone Cash Initiation Error', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    //  شحن المحفظة — المسار الثاني: تحويل يدوي + إيصال
    // ═══════════════════════════════════════════════════

    /**
     * يحفظ طلب الشحن اليدوي مع صورة الإيصال.
     * POST /wallet/topup/manual
     * enctype="multipart/form-data" مطلوب في الـ Form
     */
    public function submitManual(Request $request)
    {
        $validated = $request->validate([
            'amount'             => ['required', 'numeric', 'min:10', 'max:100000'],
            'payment_method'     => ['required', 'in:bank_transfer,cash_deposit,cheque'],
            'transfer_reference' => ['nullable', 'string', 'max:100'],
            'receipt_image'      => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'user_notes'         => ['nullable', 'string', 'max:500'],
        ], [
            'amount.required'        => 'المبلغ مطلوب.',
            'amount.min'             => 'أقل مبلغ 10 ج.م.',
            'payment_method.required' => 'طريقة الدفع مطلوبة.',
            'receipt_image.mimes'    => 'الإيصال يجب أن يكون صورة (JPG/PNG) أو PDF.',
            'receipt_image.max'      => 'حجم الملف لا يتجاوز 5 ميجابايت.',
        ]);

        $user   = Auth::user();
        $wallet = $this->walletService->getUserWallet($user);

        // رفع الإيصال إلى private disk
        $topup = $this->walletService->requestManualTopup(
            user: $user,
            amount: (float) $validated['amount'],
            receipt: $request->file('receipt_image'),
            method: $validated['payment_method'],
            ref: $validated['transfer_reference'] ?? null,
            notes: $validated['user_notes'] ?? null
        );

        return redirect()->route('wallet.index')
            ->with('success', "تم إرسال طلب الشحن بمبلغ " . number_format($validated['amount'], 2) . " ج.م. سيتم مراجعته من قبل الإدارة.");
    }

    // ═══════════════════════════════════════════════════
    //  Webhook — استقبال نتيجة الدفع من Paymob
    // ═══════════════════════════════════════════════════

    /**
     * يستقبل الـ Callback من Paymob ويُضيف الرصيد تلقائياً.
     *
     * المسار: POST /webhook/paymob (مُستثنى من CSRF في BootstrapServiceProvider)
     * Paymob يُرسل: transaction data + HMAC signature
     */
    public function handlePaymobWebhook(Request $request)
    {
        $data = $request->all();

        Log::info('Paymob Webhook Received', ['data' => $data]);

        // 1. التحقق من HMAC لضمان أن الطلب من Paymob فعلاً
        if (!$this->paymobService->validateHmac($data)) {
            Log::warning('Paymob HMAC Validation Failed', ['ip' => $request->ip()]);
            return response()->json(['error' => 'Invalid HMAC'], 401);
        }

        $transaction = $data['obj'] ?? [];
        $isSuccess   = filter_var($transaction['success'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isPending   = filter_var($transaction['pending'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $orderId     = $transaction['order']['id'] ?? null;

        // 2. العثور على طلب الشحن المرتبط بـ order_id
        $topup = WalletTopup::where('gateway_order_id', (string) $orderId)->first();

        if (!$topup) {
            Log::warning('Paymob Webhook: Topup not found', ['order_id' => $orderId]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // 3. تجاهل المعاملات المكررة (Idempotency)
        if ($topup->isCompleted()) {
            return response()->json(['status' => 'already_processed']);
        }

        // 4. حفظ الاستجابة الخام دائماً
        $topup->update([
            'gateway_transaction_id' => $transaction['id'] ?? null,
            'gateway_response'       => $transaction,
        ]);

        // 5. معالجة النتيجة
        if ($isSuccess && !$isPending) {
            // ✅ الدفع ناجح — أضِف الرصيد
            try {
                $this->walletService->processGatewayTopup(
                    user: $topup->user,
                    amount: (float) $topup->amount,
                    gatewayTransactionId: (string) ($transaction['id'] ?? ''),
                    provider: 'paymob_vodafone',
                    rawResponse: $transaction
                );

                Log::info('Paymob Wallet Credited Successfully', [
                    'user_id'  => $topup->user_id,
                    'amount'   => $topup->amount,
                    'order_id' => $orderId,
                ]);
            } catch (\Exception $e) {
                Log::error('Paymob Credit Failed After Success', [
                    'topup_id' => $topup->id,
                    'error'    => $e->getMessage(),
                ]);
                return response()->json(['error' => 'Credit failed'], 500);
            }
        } elseif (!$isSuccess && !$isPending) {
            // ❌ الدفع فشل
            $topup->update(['status' => 'rejected']);
            Log::info('Paymob Payment Failed', ['order_id' => $orderId]);
        }
        // إذا pending=true → لا إجراء، ننتظر webhook آخر

        return response()->json(['status' => 'ok']);
    }

    // ═══════════════════════════════════════════════════
    //  Admin: مراجعة الإيصالات اليدوية
    // ═══════════════════════════════════════════════════

    public function adminTopupIndex()
    {
        $pending = WalletTopup::with('user', 'wallet')
            ->where('status', 'pending_review')
            ->latest()->paginate(20);

        $all = WalletTopup::with('user')
            ->whereNot('status', 'pending_review')
            ->latest()->paginate(20, ['*'], 'all_page');

        return view('admin.wallet.topups', compact('pending', 'all'));
    }

    public function adminApproveTopup(Request $request, WalletTopup $topup)
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->walletService->approveTopup($topup, Auth::user(), $request->admin_notes);

            return redirect()->route('admin.wallet.topups')
                ->with('success', "✅ تمت الموافقة. أُضيف {$topup->amount} ج.م إلى محفظة {$topup->user?->name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function adminRejectTopup(Request $request, WalletTopup $topup)
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'admin_notes.required' => 'سبب الرفض مطلوب.',
        ]);

        $this->walletService->rejectTopup($topup, Auth::user(), $request->admin_notes);

        return redirect()->route('admin.wallet.topups')
            ->with('success', 'تم رفض طلب الشحن.');
    }
}
