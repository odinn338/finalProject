<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // خطوة أمان: تحويل أي مستخدم دوره 'user' إلى 'debtor' قبل التعديل عشان الميغريشن ميفشلش
        DB::table('users')->where('role', 'user')->update(['role' => 'debtor']);

        Schema::table('users', function (Blueprint $table) {

            // تعديل عمود الأدوار
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','creditor','debtor') NOT NULL DEFAULT 'debtor'");

            // إضافة الحقول الجديدة مع التأكد إنها مش موجودة (عشان لو كنت ضفتها يدوي قبل كدة)
            if (!Schema::hasColumn('users', 'national_id_verified')) {
                $table->string('national_id_verified')->nullable()->after('national_id');
            }

            if (!Schema::hasColumn('users', 'kyc_status')) {
                $table->enum('kyc_status', ['not_submitted', 'pending', 'verified', 'rejected'])->default('not_submitted')->after('credit_score');
            }

            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','user') NOT NULL DEFAULT 'user'");
            $table->dropColumn(['national_id_verified', 'kyc_status', 'kyc_verified_at', 'credit_limit']);
        });
    }
};
