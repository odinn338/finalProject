<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول طلبات إعادة الجدولة
     *
     * سيناريو إعادة الجدولة:
     * 1. المستخدم يقدم طلب إعادة جدولة مع سبب التعثر
     * 2. المدير يوافق ويحدد: نسبة فائدة جديدة + عدد أشهر جديد
     * 3. النظام: يحسب الرصيد غير المسدد → يلغي الأقساط القديمة → ينشئ جدول جديد
     */
    public function up(): void
    {
        Schema::create('rescheduling_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts');
            $table->foreignId('user_id')->constrained('users');
            $table->text('reason')->comment('سبب طلب إعادة الجدولة');

            // بيانات وقت الطلب (لتوثيق الوضع الحالي للدين)
            $table->decimal('outstanding_balance', 15, 2)->comment('الرصيد غير المسدد وقت الطلب');
            $table->integer('remaining_installments')->comment('عدد الأقساط المتبقية وقت الطلب');

            // قرار المدير
            $table->decimal('new_interest_rate', 5, 2)->nullable()->comment('نسبة الفائدة الجديدة %');
            $table->integer('new_months')->nullable()->comment('عدد الأشهر الجديدة');
            $table->decimal('new_monthly_installment', 15, 2)->nullable()->comment('القسط الشهري الجديد');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();

            $table->enum('status', [
                'pending',   // قيد الانتظار
                'approved',  // موافق عليه
                'rejected',  // مرفوض
            ])->default('pending');

            $table->timestamps();

            $table->index(['debt_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rescheduling_requests');
    }
};
