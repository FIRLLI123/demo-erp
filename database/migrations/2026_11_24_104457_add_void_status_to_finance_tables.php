<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom status pada tabel payments (TTK)
        if (!Schema::hasColumn('payments', 'status')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->enum('status', ['COMPLETED', 'VOID'])->default('COMPLETED')->after('cashier_name');
            });
        }

        // 2. Tambahkan nilai 'VOID' pada kolom status di bank_receipts (BBM)
        DB::statement("ALTER TABLE `bank_receipts` MODIFY COLUMN `status` ENUM('VERIFIED', 'PENDING', 'VOID') NOT NULL DEFAULT 'VERIFIED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payments', 'status')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        DB::statement("ALTER TABLE `bank_receipts` MODIFY COLUMN `status` ENUM('VERIFIED', 'PENDING') NOT NULL DEFAULT 'VERIFIED'");
    }
};
