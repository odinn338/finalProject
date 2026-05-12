<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول طلبات الديون
     * المستخدم يقدم طلب → المدير يراجع ويوافق بتحديد نسبة الفائدة والمدة
     */
    public function up(): void
    {
        Schema::create('debt_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->comment('عنوان الطلب');
            $table->text('description')->nullable()->comment('وصف الغرض من القرض');
            $table->decimal('requested_amount', 15, 2)->comment('المبلغ المطلوب');
            $table->integer('requested_months')->comment('عدد الأشهر المطلوبة للسداد');

            // بيانات القرار - يملؤها المدير عند الموافقة
            $table->decimal('approved_amount', 15, 2)->nullable()->comment('المبلغ المعتمد');
            $table->decimal('interest_rate', 5, 2)->nullable()->comment('نسبة الفائدة المحددة من المدير %');
            $table->integer('approved_months')->nullable()->comment('عدد الأشهر المعتمدة');
            $table->text('admin_notes')->nullable()->comment('ملاحظات المدير');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->comment('المدير المراجع');
            $table->timestamp('reviewed_at')->nullable();

            $table->enum('status', [
                'pending',    // قيد الانتظار
                'approved',   // موافق عليه
                'rejected',   // مرفوض
                'cancelled',  // ملغي
            ])->default('pending');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_requests');
    }
};
