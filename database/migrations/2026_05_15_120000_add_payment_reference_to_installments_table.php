<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table): void {
            if (! Schema::hasColumn('installments', 'payment_reference')) {
                $table->string('payment_reference')
                    ->nullable()
                    ->after('notes')
                    ->comment('مرجع الدفع المُقدَّم من المدين عند طلب السداد');
            }
        });
    }

    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table): void {
            if (Schema::hasColumn('installments', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
        });
    }
};
