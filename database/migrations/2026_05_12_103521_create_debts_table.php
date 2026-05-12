<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الديون - يُنشأ تلقائياً عند موافقة المدير على الطلب
     * يحتوي على المبلغ الأصلي + الفائدة + إجمالي الأقساط
     */
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('debt_request_id')->constrained('debt_requests');
            $table->string('reference_number')->unique()->comment('رقم مرجعي فريد للدين');

            // المبالغ المالية - decimal(15,2) للدقة الكاملة
            $table->decimal('principal_amount', 15, 2)->comment('المبلغ الأصلي');
            $table->decimal('interest_rate', 5, 2)->comment('نسبة الفائدة %');
            $table->decimal('interest_amount', 15, 2)->comment('قيمة الفائدة = principal * (rate/100)');
            $table->decimal('total_amount', 15, 2)->comment('إجمالي المبلغ = أصل + فائدة');
            $table->decimal('monthly_installment', 15, 2)->comment('قيمة القسط الشهري = total / months');
            $table->decimal('total_paid', 15, 2)->default(0)->comment('إجمالي المبالغ المسددة');
            $table->decimal('remaining_balance', 15, 2)->comment('الرصيد المتبقي');

            // المدة والتواريخ
            $table->integer('total_months')->comment('إجمالي عدد الأشهر');
            $table->integer('paid_months')->default(0)->comment('عدد الأشهر المسددة');
            $table->date('start_date')->comment('تاريخ بدء الدين');
            $table->date('end_date')->comment('تاريخ انتهاء الدين');

            $table->enum('status', [
                'active',     // نشط
                'completed',  // مكتمل السداد
                'overdue',    // متأخر
                'rescheduled', // تمت إعادة جدولته
            ])->default('active');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
