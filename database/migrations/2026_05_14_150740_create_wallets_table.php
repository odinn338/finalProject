<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول المحافظ الرقمية (Wallets)
     *
     * كل مستخدم — سواء أكان دائناً أم مديناً — يمتلك محفظة رقمية واحدة.
     * يحتفظ النظام بثلاثة أرصدة لكل محفظة:
     *
     *   available_balance  → الرصيد المتاح للاستخدام الفوري
     *   reserved_balance   → رصيد محجوز (Escrow) ريثما يتم التحقق من عملية ما
     *   total_deposited    → إجمالي ما أودعه المستخدم منذ إنشاء الحساب (للتدقيق)
     *   total_withdrawn    → إجمالي ما سُحب من الحساب
     *
     * القاعدة: available + reserved = الرصيد الكلي الفعلي في أي لحظة
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            // ربط المحفظة بالمستخدم (علاقة واحد-لواحد)
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('صاحب المحفظة');

            // ─── الأرصدة ──────────────────────────────────
            $table->decimal('available_balance', 15, 2)
                ->default(0.00)
                ->comment('الرصيد المتاح للدفع الفوري');

            $table->decimal('reserved_balance', 15, 2)
                ->default(0.00)
                ->comment('رصيد محجوز في Escrow - ينتظر التحقق');

            $table->decimal('total_deposited', 15, 2)
                ->default(0.00)
                ->comment('إجمالي الإيداعات التراكمية');

            $table->decimal('total_withdrawn', 15, 2)
                ->default(0.00)
                ->comment('إجمالي السحوبات التراكمية');

            // ─── حالة المحفظة ────────────────────────────
            $table->enum('status', ['active', 'frozen', 'suspended'])
                ->default('active')
                ->comment('active=نشطة | frozen=مجمّدة مؤقتاً | suspended=موقوفة');

            // ─── عملة المحفظة ────────────────────────────
            $table->string('currency', 3)
                ->default('EGP')
                ->comment('رمز العملة (ISO 4217): EGP, USD, SAR...');

            $table->timestamps();

            // ─── فهارس ───────────────────────────────────
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
