<?php

namespace App\Services;

use App\Models\Installment;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTopup;
use App\Models\WalletTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    // ══════════════════════════════════════════════════════════════════
    //  1. إنشاء المحفظة — يُستدعى تلقائياً عند تسجيل مستخدم جديد
    // ══════════════════════════════════════════════════════════════════

    /**
     * ينشئ محفظة رقمية جديدة لمستخدم ويعيد الكائن.
     * يُستدعى من AuthController بعد User::create() مباشرةً.
     *
     * @param User $user المستخدم الجديد
     * @return Wallet
     */
    public function createWalletForUser(User $user): Wallet
    {
        return Wallet::create([
            'user_id'           => $user->id,
            'available_balance' => 0.00,
            'reserved_balance'  => 0.00,
            'total_deposited'   => 0.00,
            'total_withdrawn'   => 0.00,
            'status'            => 'active',
            'currency'          => 'EGP',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  2. شحن المحفظة — مسار التحويل اليدوي (مع رفع الإيصال)
    // ══════════════════════════════════════════════════════════════════

    /**
     * يُنشئ طلب شحن يدوي ويحفظ صورة الإيصال.
     * الرصيد لا يُضاف حتى يوافق المدير.
     *
     * @param User          $user    المستخدم الذي يشحن محفظته
     * @param float         $amount  المبلغ المراد شحنه
     * @param UploadedFile|null $receipt صورة إيصال التحويل (اختيارية)
     * @param string        $method  طريقة الدفع
     * @param string|null   $ref     رقم مرجع التحويل البنكي
     * @param string|null   $notes   ملاحظات المستخدم
     * @return WalletTopup
     */
    public function requestManualTopup(
        User $user,
        float $amount,
        ?UploadedFile $receipt,
        string $method = 'bank_transfer',
        ?string $ref = null,
        ?string $notes = null
    ): WalletTopup {

        $wallet = $this->getUserWallet($user);
        $this->assertWalletActive($wallet);

        // حفظ صورة الإيصال في التخزين
        $receiptPath = null;
        $originalName = null;
        if ($receipt) {
            $receiptPath  = $receipt->store("receipts/{$user->id}", 'private');
            $originalName = $receipt->getClientOriginalName();
        }

        return WalletTopup::create([
            'wallet_id'                   => $wallet->id,
            'user_id'                     => $user->id,
            'amount'                      => $amount,
            'currency'                    => $wallet->currency,
            'payment_method'              => $method,
            'receipt_image_path'          => $receiptPath,
            'receipt_image_original_name' => $originalName,
            'transfer_reference'          => $ref,
            'user_notes'                  => $notes,
            'status'                      => 'pending_review',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  3. شحن المحفظة — مسار بوابة الدفع الإلكترونية (آلي)
    // ══════════════════════════════════════════════════════════════════

    /**
     * إتمام طلب شحن كان قيد انتظار البوابة (مثل pending_gateway لـ Paymob) دون إنشاء سجل شحن مكرر.
     *
     * @throws \Exception
     */
    public function completeGatewayTopup(WalletTopup $topup, string $gatewayTransactionId, array $rawResponse): WalletTopup
    {
        return DB::transaction(function () use ($topup, $gatewayTransactionId, $rawResponse) {
            /** @var WalletTopup $locked */
            $locked = WalletTopup::query()->whereKey($topup->id)->lockForUpdate()->firstOrFail();

            if ($locked->isCompleted()) {
                return $locked;
            }

            $duplicateElsewhere = WalletTopup::query()
                ->where('gateway_transaction_id', $gatewayTransactionId)
                ->where('id', '!=', $locked->id)
                ->where('status', 'completed')
                ->exists();

            if ($duplicateElsewhere) {
                return $locked;
            }

            $wallet = $this->getUserWallet($locked->user);
            $this->assertWalletActive($wallet);

            $provider = $locked->gateway_provider ?? 'gateway';

            $this->creditWallet(
                wallet: $wallet,
                amount: (float) $locked->amount,
                description: "شحن عبر {$provider} — معاملة #{$gatewayTransactionId}",
                type: 'deposit',
                reference: $locked
            );

            $locked->update([
                'gateway_transaction_id' => $gatewayTransactionId,
                'gateway_response'         => $rawResponse,
                'status'                   => 'completed',
            ]);

            return $locked->fresh();
        });
    }

    /**
     * يُنفَّذ بعد تأكيد بوابة الدفع (Webhook callback) عندما لا يوجد سجل طلب شحن مسبق.
     * يضيف الرصيد فوراً دون مراجعة بشرية.
     *
     * @param User   $user                المستخدم
     * @param float  $amount              المبلغ المؤكَّد
     * @param string $gatewayTransactionId معرّف المعاملة من البوابة
     * @param string $provider            اسم البوابة (paymob, stripe...)
     * @param array  $rawResponse         الاستجابة الخام من البوابة
     * @return WalletTopup
     */
    public function processGatewayTopup(
        User $user,
        float $amount,
        string $gatewayTransactionId,
        string $provider,
        array $rawResponse
    ): WalletTopup {

        return DB::transaction(function () use ($user, $amount, $gatewayTransactionId, $provider, $rawResponse) {

            $wallet = $this->getUserWallet($user);
            $this->assertWalletActive($wallet);

            // منع المعالجة المكررة (Idempotency)
            $existing = WalletTopup::where('gateway_transaction_id', $gatewayTransactionId)->first();
            if ($existing) {
                return $existing;
            }

            // إنشاء سجل الشحن بحالة مكتملة مباشرةً
            $topup = WalletTopup::create([
                'wallet_id'              => $wallet->id,
                'user_id'                => $user->id,
                'amount'                 => $amount,
                'currency'               => $wallet->currency,
                'payment_method'         => 'gateway',
                'gateway_transaction_id' => $gatewayTransactionId,
                'gateway_provider'       => $provider,
                'gateway_response'       => $rawResponse,
                'status'                 => 'completed',
            ]);

            // إضافة الرصيد مباشرةً
            $this->creditWallet(
                wallet: $wallet,
                amount: $amount,
                description: "شحن عبر {$provider} — معاملة #{$gatewayTransactionId}",
                type: 'deposit',
                reference: $topup
            );

            return $topup;
        });
    }

    // ══════════════════════════════════════════════════════════════════
    //  4. موافقة المدير على شحن يدوي
    // ══════════════════════════════════════════════════════════════════

    /**
     * عند موافقة المدير:
     *   - يتغير status إلى 'completed'
     *   - يُضاف الرصيد إلى available_balance
     *   - يُسجَّل في wallet_transactions
     *
     * @param WalletTopup $topup      طلب الشحن المعلق
     * @param User        $admin      المدير الذي يوافق
     * @param string|null $adminNotes ملاحظاته
     * @return WalletTopup
     * @throws \Exception إذا كان الطلب في حالة غير قابلة للموافقة
     */
    public function approveTopup(WalletTopup $topup, User $admin, ?string $adminNotes = null): WalletTopup
    {
        if ($topup->status !== 'pending_review') {
            throw new \Exception('لا يمكن الموافقة على طلب في حالته الحالية: ' . $topup->status);
        }

        return DB::transaction(function () use ($topup, $admin, $adminNotes) {

            $topup->update([
                'status'      => 'completed',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            // إضافة الرصيد إلى محفظة المستخدم
            $this->creditWallet(
                wallet: $topup->wallet,
                amount: $topup->amount,
                description: "شحن يدوي معتمد من المدير — طلب #{$topup->id}",
                type: 'deposit',
                reference: $topup
            );

            return $topup->fresh();
        });
    }

    /**
     * رفض طلب الشحن اليدوي.
     */
    public function rejectTopup(WalletTopup $topup, User $admin, string $adminNotes): WalletTopup
    {
        if ($topup->status !== 'pending_review') {
            throw new \Exception('لا يمكن رفض طلب في حالته الحالية.');
        }

        $topup->update([
            'status'      => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        return $topup;
    }

    // ══════════════════════════════════════════════════════════════════
    //  5. دفع قسط من المحفظة — منطق Escrow الكامل
    // ══════════════════════════════════════════════════════════════════

    /**
     * ينفّذ عملية دفع قسط من محفظة المدين إلى محفظة الدائن.
     *
     * آلية Escrow (الضمان الداخلي) — 4 خطوات:
     * ─────────────────────────────────────────
     * الخطوة 1: RESERVE   → ينقل المبلغ من available إلى reserved في محفظة المدين
     * الخطوة 2: DEBIT     → يخصم من reserved في محفظة المدين (يصفّر الحجز)
     * الخطوة 3: CREDIT    → يُضيف إلى available في محفظة الدائن
     * الخطوة 4: سجل دفع في payment_logs (ينفّذه DebtService::recordPayment)
     *
     * @param Installment $installment القسط المراد تسديده
     * @param User        $debtor      المدين (صاحب المحفظة الخاصمة)
     * @param User        $creditor    الدائن (صاحب المحفظة المستلِمة)
     * @return array ['debit_tx' => WalletTransaction, 'credit_tx' => WalletTransaction]
     * @throws \Exception إذا كان الرصيد غير كافٍ
     */
    public function payInstallmentFromWallet(
        Installment $installment,
        User $debtor,
        User $creditor
    ): array {

        return DB::transaction(function () use ($installment, $debtor, $creditor) {

            $amount          = $installment->remaining_amount;
            $debtorWallet    = $this->getUserWallet($debtor);
            $creditorWallet  = $this->getUserWallet($creditor);

            // ── التحقق من الرصيد ──────────────────────────
            $this->assertWalletActive($debtorWallet);
            $this->assertWalletActive($creditorWallet);
            $this->assertSufficientBalance($debtorWallet, $amount);

            // ── الخطوة 1: حجز المبلغ (Reserve / Escrow) ──
            $reserveTx = $this->reserveBalance(
                wallet: $debtorWallet,
                amount: $amount,
                description: "حجز مبلغ قسط #{$installment->installment_number} — دين {$installment->debt->reference_number}",
                reference: $installment
            );

            // ── الخطوة 2: خصم من الحجز (Debit Reserved) ──
            $debitTx = $this->debitReserved(
                wallet: $debtorWallet,
                amount: $amount,
                counterpartWallet: $creditorWallet,
                description: "سداد قسط #{$installment->installment_number} — {$installment->debt->reference_number}",
                reference: $installment
            );

            // ── الخطوة 3: إضافة إلى محفظة الدائن (Credit) ─
            $creditTx = $this->creditWallet(
                wallet: $creditorWallet,
                amount: $amount,
                description: "استلام قسط #{$installment->installment_number} من {$debtor->name}",
                type: 'payment_credit',
                reference: $installment,
                counterpartWallet: $debtorWallet
            );

            return [
                'reserve_tx' => $reserveTx,
                'debit_tx'   => $debitTx,
                'credit_tx'  => $creditTx,
            ];
        });
    }

    // ══════════════════════════════════════════════════════════════════
    //  6. العمليات الأساسية الداخلية (Private Primitives)
    // ══════════════════════════════════════════════════════════════════

    /**
     * يُضيف رصيداً إلى available_balance (إيداع أو استلام دفعة).
     */
    private function creditWallet(
        Wallet       $wallet,
        float        $amount,
        string       $description,
        string       $type = 'deposit',
        mixed        $reference = null,
        ?Wallet      $counterpartWallet = null
    ): WalletTransaction {

        $balanceBefore = $wallet->available_balance;

        $wallet->increment('available_balance', $amount);
        $wallet->increment('total_deposited', $amount);
        $wallet->refresh();

        return $this->logTransaction(
            wallet: $wallet,
            type: $type,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $wallet->available_balance,
            reservedBefore: $wallet->reserved_balance,
            reservedAfter: $wallet->reserved_balance,
            description: $description,
            reference: $reference,
            counterpartWallet: $counterpartWallet
        );
    }

    /**
     * ينقل مبلغاً من available إلى reserved (حجز Escrow).
     */
    private function reserveBalance(
        Wallet $wallet,
        float  $amount,
        string $description,
        mixed  $reference = null
    ): WalletTransaction {

        $balanceBefore  = $wallet->available_balance;
        $reservedBefore = $wallet->reserved_balance;

        $wallet->decrement('available_balance', $amount);
        $wallet->increment('reserved_balance',  $amount);
        $wallet->refresh();

        return $this->logTransaction(
            wallet: $wallet,
            type: 'reserve',
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $wallet->available_balance,
            reservedBefore: $reservedBefore,
            reservedAfter: $wallet->reserved_balance,
            description: $description,
            reference: $reference
        );
    }

    /**
     * يخصم من reserved_balance (تحويل إلى الدائن).
     */
    private function debitReserved(
        Wallet  $wallet,
        float   $amount,
        Wallet  $counterpartWallet,
        string  $description,
        mixed   $reference = null
    ): WalletTransaction {

        $balanceBefore  = $wallet->available_balance;
        $reservedBefore = $wallet->reserved_balance;

        $wallet->decrement('reserved_balance', $amount);
        $wallet->increment('total_withdrawn',  $amount);
        $wallet->refresh();

        return $this->logTransaction(
            wallet: $wallet,
            type: 'payment_debit',
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $wallet->available_balance,
            reservedBefore: $reservedBefore,
            reservedAfter: $wallet->reserved_balance,
            description: $description,
            reference: $reference,
            counterpartWallet: $counterpartWallet
        );
    }

    /**
     * يُسجِّل حركة في جدول wallet_transactions.
     */
    private function logTransaction(
        Wallet   $wallet,
        string   $type,
        float    $amount,
        float    $balanceBefore,
        float    $balanceAfter,
        float    $reservedBefore,
        float    $reservedAfter,
        string   $description,
        mixed    $reference = null,
        ?Wallet  $counterpartWallet = null
    ): WalletTransaction {

        return WalletTransaction::create([
            'wallet_id'              => $wallet->id,
            'user_id'                => $wallet->user_id,
            'counterpart_wallet_id'  => $counterpartWallet?->id,
            'counterpart_user_id'    => $counterpartWallet?->user_id,
            'type'                   => $type,
            'amount'                 => $amount,
            'balance_before'         => $balanceBefore,
            'balance_after'          => $balanceAfter,
            'reserved_before'        => $reservedBefore,
            'reserved_after'         => $reservedAfter,
            'currency'               => $wallet->currency,
            'referenceable_type'     => $reference ? get_class($reference) : null,
            'referenceable_id'       => $reference?->id,
            'description'            => $description,
            'reference_code'         => $this->generateReferenceCode(),
        ]);
    }

    // ══════════════════════════════════════════════════════════════════
    //  7. تقارير وإحصائيات المحفظة
    // ══════════════════════════════════════════════════════════════════

    /**
     * ملخص محفظة المستخدم مع آخر العمليات.
     */
    public function getWalletSummary(User $user): array
    {
        $wallet = $this->getUserWallet($user);

        return [
            'wallet'              => $wallet,
            'available_balance'   => $wallet->available_balance,
            'reserved_balance'    => $wallet->reserved_balance,
            'total_balance'       => $wallet->available_balance + $wallet->reserved_balance,
            'total_deposited'     => $wallet->total_deposited,
            'total_withdrawn'     => $wallet->total_withdrawn,
            'recent_transactions' => WalletTransaction::where('wallet_id', $wallet->id)
                ->latest()
                ->limit(10)
                ->get(),
            'pending_topups'      => WalletTopup::where('wallet_id', $wallet->id)
                ->where('status', 'pending_review')
                ->count(),
        ];
    }

    // ══════════════════════════════════════════════════════════════════
    //  8. Helpers
    // ══════════════════════════════════════════════════════════════════

    public function getUserWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'available_balance' => 0,
                'reserved_balance'  => 0,
                'total_deposited'   => 0,
                'total_withdrawn'   => 0,
                'status'            => 'active',
                'currency'          => 'EGP',
            ]
        );
    }

    private function assertWalletActive(Wallet $wallet): void
    {
        if ($wallet->status !== 'active') {
            throw new \Exception(
                'المحفظة غير نشطة: ' . match ($wallet->status) {
                    'frozen'    => 'المحفظة مجمّدة مؤقتاً. يرجى التواصل مع الإدارة.',
                    'suspended' => 'المحفظة موقوفة. يرجى التواصل مع الإدارة.',
                    default     => $wallet->status,
                }
            );
        }
    }

    private function assertSufficientBalance(Wallet $wallet, float $amount): void
    {
        if ($wallet->available_balance < $amount) {
            $shortfall = $amount - $wallet->available_balance;
            throw new \Exception(
                sprintf(
                    'رصيد المحفظة غير كافٍ. المتاح: %s ج.م | المطلوب: %s ج.م | النقص: %s ج.م',
                    number_format($wallet->available_balance, 2),
                    number_format($amount, 2),
                    number_format($shortfall, 2)
                )
            );
        }
    }

    private function generateReferenceCode(): string
    {
        do {
            $code = 'TXN-' . strtoupper(Str::random(8));
        } while (WalletTransaction::where('reference_code', $code)->exists());

        return $code;
    }
}
