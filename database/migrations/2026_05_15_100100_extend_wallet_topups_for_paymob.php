<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_topups', function (Blueprint $table): void {
            if (! Schema::hasColumn('wallet_topups', 'gateway_order_id')) {
                $table->string('gateway_order_id')->nullable()->unique()->after('gateway_provider');
            }
            if (! Schema::hasColumn('wallet_topups', 'paymob_token')) {
                $table->text('paymob_token')->nullable()->after('gateway_order_id');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wallet_topups MODIFY COLUMN payment_method ENUM(
                'gateway',
                'bank_transfer',
                'cash_deposit',
                'cheque',
                'vodafone_cash'
            ) NOT NULL COMMENT 'طريقة الدفع المختارة'");
        }
    }

    public function down(): void
    {
        Schema::table('wallet_topups', function (Blueprint $table): void {
            if (Schema::hasColumn('wallet_topups', 'gateway_order_id')) {
                $table->dropUnique('wallet_topups_gateway_order_id_unique');
                $table->dropColumn('gateway_order_id');
            }
            if (Schema::hasColumn('wallet_topups', 'paymob_token')) {
                $table->dropColumn('paymob_token');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE wallet_topups MODIFY COLUMN payment_method ENUM(
                'gateway',
                'bank_transfer',
                'cash_deposit',
                'cheque'
            ) NOT NULL COMMENT 'طريقة الدفع المختارة'");
        }
    }
};
