<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول طلبات شحن المحفظة (Wallet Top-ups)
     *
     * عند رغبة المدين في شحن محفظته يمر الطلب بمسارَين:
     *
     * المسار الأول  — الدفع الإلكتروني الآلي (Payment Gateway):
     *   status يتحول مباشرةً إلى 'completed' عند تأكيد البوابة.
     *
     * المسار الثاني — التحويل اليدوي (Manual Transfer):
     *   يرفع المستخدم صورة إيصال التحويل → Admin يراجع → يوافق أو يرفض.
     *   أثناء الانتظار: status = 'pending_review'
     *   عند الموافقة:    status = 'completed' + يُحتسب الرصيد
     *   عند الرفض:       status = 'rejected'
     */
    public function up(): void
    {
        Schema::create('wallet_topups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->onDelete('cascade')
                ->comment('المحفظة المستهدفة');

            $table->foreignId('user_id')
                ->constrained('users')
                ->comment('المستخدم صاحب الطلب');

            // ─── مبلغ الشحن ──────────────────────────────
            $table->decimal('amount', 15, 2)
                ->comment('مبلغ الشحن المطلوب');

            $table->string('currency', 3)
                ->default('EGP');

            // ─── طريقة الدفع ─────────────────────────────
            $table->enum('payment_method', [
                'gateway',        // بوابة دفع إلكترونية (Paymob, Stripe...)
                'bank_transfer',  // تحويل بنكي يدوي
                'cash_deposit',   // إيداع نقدي في البنك
                'cheque',         // شيك بنكي
            ])->comment('طريقة الدفع المختارة');

            // ─── بيانات بوابة الدفع الإلكترونية ─────────
            $table->string('gateway_transaction_id')
                ->nullable()
                ->comment('رقم المعاملة من بوابة الدفع (Paymob/Stripe)');

            $table->string('gateway_provider')
                ->nullable()
                ->comment('اسم بوابة الدفع المستخدمة');

            $table->json('gateway_response')
                ->nullable()
                ->comment('الاستجابة الكاملة من البوابة (JSON)');

            // ─── إثبات التحويل اليدوي (Receipt) ─────────
            $table->string('receipt_image_path')
                ->nullable()
                ->comment('مسار صورة إيصال التحويل المُرفوعة');

            $table->string('receipt_image_original_name')
                ->nullable()
                ->comment('الاسم الأصلي للملف المرفوع');

            $table->text('transfer_reference')
                ->nullable()
                ->comment('رقم مرجع التحويل البنكي (اختياري)');

            $table->text('user_notes')
                ->nullable()
                ->comment('ملاحظات المستخدم عند الإرسال');

            // ─── مراجعة المدير ───────────────────────────
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->comment('المدير الذي راجع الطلب');

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->text('admin_notes')
                ->nullable()
                ->comment('ملاحظات المدير عند الموافقة أو الرفض');

            // ─── حالة الطلب ──────────────────────────────
            $table->enum('status', [
                'pending_review', // ينتظر مراجعة المدير (للتحويلات اليدوية)
                'pending_gateway', // ينتظر تأكيد البوابة الإلكترونية
                'completed',      // تمت الموافقة وأُضيف الرصيد
                'rejected',       // رُفض الطلب
                'cancelled',      // ألغاه المستخدم
            ])->default('pending_review');

            $table->timestamps();

            // ─── فهارس ───────────────────────────────────
            $table->index(['wallet_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_topups');
    }
};
