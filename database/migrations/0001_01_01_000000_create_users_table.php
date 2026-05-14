<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول المستخدمين - يدعم أدوار: مدير / مستخدم عادي
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('national_id')->unique()->nullable()->comment('رقم الهوية الوطنية');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->decimal('credit_score', 5, 2)->default(100.00)->comment('درجة الائتمان من 100');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
