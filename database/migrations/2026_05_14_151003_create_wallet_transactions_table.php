<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول حركات المحفظة الداخلية (Wallet Transactions)
     *
     * يُسجَّل كل تغيير في رصيد أي محفظة هنا — سواء أكان:
     *   - إيداعاً (Deposit)         → عند شحن المحفظة
     *   - خصماً (Debit)             → عند دفع قسط
     *   - تحويلاً صادراً (Transfer) → من محفظة المدين
     *   - تحويلاً وارداً (Receive)  → إلى محفظة الدائن
     *   - حجزاً (Reserve)           → تحويل من available إلى reserved (Escrow)
     *   - تحرير حجز (Release)       → تحويل من reserved إلى available
     *
     * عملية دفع قسط تُولِّد 4 سجلات في هذا الجدول:
     *   1. RESERVE    على محفظة المدين   (available → reserved)
     *   2. DEBIT      على محفظة المدين   (reserved → 0)
     *   3. CREDIT     على محفظة الدائن   (0 → available)
     *   4. سجل في payment_logs للاحتفاظ بالتاريخ التشغيلي
     *
     * هذا النموذج يضمن Double-Entry Bookkeeping داخل النظام.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            // ─── الأطراف ─────────────────────────────────
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->comment('المحفظة المعنية بهذه الحركة');

            $table->foreignId('user_id')
                ->constrained('users')
                ->comment('صاحب المحفظة');

            // ─── طرف الحركة المقابل (اختياري) ──────────
            $table->foreignId('counterpart_wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->comment('المحفظة المقابلة في حالة التحويل الداخلي');

            $table->foreignId('counterpart_user_id')
                ->nullable()
                ->constrained('users')
                ->comment('المستخدم المقابل في حالة التحويل');

            // ─── نوع الحركة ──────────────────────────────
            $table->enum('type', [
                'deposit',          // إيداع خارجي (شحن المحفظة)
                'withdrawal',       // سحب خارجي (يُنفَّذ مستقبلاً)
                'payment_debit',    // خصم لدفع قسط (من محفظة المدين)
                'payment_credit',   // إضافة من قسط مسدد (إلى محفظة الدائن)
                'reserve',          // حجز رصيد في Escrow
                'release',          // تحرير حجز من Escrow
                'refund',           // استرداد (Refund)
                'fee',              // رسوم النظام (Platform Fee)
                'adjustment',       // تسوية يدوية من المدير
            ])->comment('نوع الحركة المالية');

            // ─── المبالغ ─────────────────────────────────
            $table->decimal('amount', 15, 2)
                ->comment('قيمة الحركة (دائماً موجبة - الاتجاه يحدده type)');

            $table->decimal('balance_before', 15, 2)
                ->comment('الرصيد المتاح قبل الحركة');

            $table->decimal('balance_after', 15, 2)
                ->comment('الرصيد المتاح بعد الحركة');

            $table->decimal('reserved_before', 15, 2)
                ->default(0)
                ->comment('الرصيد المحجوز قبل الحركة');

            $table->decimal('reserved_after', 15, 2)
                ->default(0)
                ->comment('الرصيد المحجوز بعد الحركة');

            $table->string('currency', 3)->default('EGP');

            // ─── ربط بالكيانات الأخرى ────────────────────
            $table->string('referenceable_type')
                ->nullable()
                ->comment('نوع الكيان المرتبط: Installment, WalletTopup...');

            $table->unsignedBigInteger('referenceable_id')
                ->nullable()
                ->comment('معرّف الكيان المرتبط (polymorphic)');

            // ─── وصف ومرجع ───────────────────────────────
            $table->string('description')
                ->comment('وصف مقروء للحركة بالعربية');

            $table->string('reference_code')
                ->unique()
                ->comment('رمز مرجعي فريد للحركة: TXN-XXXXXX');

            $table->text('admin_notes')
                ->nullable()
                ->comment('ملاحظات المدير للتسويات اليدوية');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->comment('من أنشأ هذه الحركة (System أو Admin)');

            $table->timestamps();

            // ─── فهارس ───────────────────────────────────
            $table->index(['wallet_id', 'type']);
            $table->index(['wallet_id', 'created_at']);
            $table->index(['referenceable_type', 'referenceable_id']);
            $table->index('reference_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
