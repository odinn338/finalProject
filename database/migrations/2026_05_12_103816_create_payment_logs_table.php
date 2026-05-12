<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول سجل الدفعات - يُسجَّل كل عملية سداد هنا للتدقيق والتقارير
     */
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained('installments');
            $table->foreignId('debt_id')->constrained('debts');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('recorded_by')->constrained('users')->comment('من سجّل الدفع (مدير أو المستخدم نفسه)');

            $table->decimal('amount_paid', 15, 2)->comment('المبلغ المدفوع');
            $table->string('payment_method')->default('cash')->comment('طريقة الدفع: cash / bank_transfer / cheque');
            $table->string('reference_number')->nullable()->comment('رقم مرجع الدفع');
            $table->text('notes')->nullable();
            $table->timestamp('payment_date')->comment('تاريخ ووقت الدفع');
            $table->timestamps();

            $table->index(['debt_id', 'payment_date']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
