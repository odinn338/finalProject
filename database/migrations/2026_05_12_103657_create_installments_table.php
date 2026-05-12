<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول الأقساط - يُولَّد تلقائياً عند إنشاء الدين
     * كل قسط يمثل دفعة شهرية واحدة
     */
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('installment_number')->comment('رقم القسط (1, 2, 3...)');

            // المبالغ
            $table->decimal('amount', 15, 2)->comment('قيمة القسط');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('المبلغ المسدد فعلياً');
            $table->decimal('penalty_amount', 15, 2)->default(0)->comment('غرامة التأخير إن وجدت');

            // التواريخ
            $table->date('due_date')->comment('تاريخ الاستحقاق');
            $table->date('paid_date')->nullable()->comment('تاريخ السداد الفعلي');

            $table->enum('status', [
                'pending',   // لم يُسدد بعد
                'paid',      // مسدد
                'overdue',   // متأخر
                'voided',    // ملغي (عند إعادة الجدولة)
                'partial',   // مسدد جزئياً
            ])->default('pending');

            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->comment('من سجّل الدفع');
            $table->timestamps();

            $table->index(['debt_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
