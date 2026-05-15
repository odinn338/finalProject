<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE installments MODIFY COLUMN status
         ENUM('pending','pending_approval','paid','overdue') NOT NULL DEFAULT 'pending'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE installments MODIFY COLUMN status
         ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending'"
        );
    }
};
