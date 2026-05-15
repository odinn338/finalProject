<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يضمن دعم pending_approval مع الإبقاء على voided و partial لإعادة الجدولة.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE installments MODIFY COLUMN status
            ENUM(
                'pending',
                'pending_approval',
                'paid',
                'overdue',
                'voided',
                'partial'
            ) NOT NULL DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE installments MODIFY COLUMN status
            ENUM(
                'pending',
                'paid',
                'overdue',
                'voided',
                'partial'
            ) NOT NULL DEFAULT 'pending'"
        );
    }
};
