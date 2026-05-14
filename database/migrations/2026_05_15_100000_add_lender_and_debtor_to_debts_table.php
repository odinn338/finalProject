<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يضيف lender_id و debtor_id مع الإبقاء على user_id (المدين) كما هو.
     */
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table): void {
            if (! Schema::hasColumn('debts', 'lender_id')) {
                $table->foreignId('lender_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('debts', 'debtor_id')) {
                $table->foreignId('debtor_id')
                    ->nullable()
                    ->after('lender_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        if (Schema::hasColumn('debts', 'debtor_id')) {
            DB::statement('UPDATE debts SET debtor_id = user_id WHERE debtor_id IS NULL');
        }

        Schema::table('debts', function (Blueprint $table): void {
            if (Schema::hasColumn('debts', 'lender_id')) {
                $table->index(['lender_id', 'status']);
            }
            if (Schema::hasColumn('debts', 'debtor_id')) {
                $table->index(['debtor_id', 'status']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table): void {
            if (Schema::hasColumn('debts', 'debtor_id')) {
                $table->dropForeign(['debtor_id']);
                $table->dropIndex(['debtor_id', 'status']);
                $table->dropColumn('debtor_id');
            }
            if (Schema::hasColumn('debts', 'lender_id')) {
                $table->dropForeign(['lender_id']);
                $table->dropIndex(['lender_id', 'status']);
                $table->dropColumn('lender_id');
            }
        });
    }
};
