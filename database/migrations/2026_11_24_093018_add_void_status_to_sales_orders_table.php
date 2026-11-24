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
        // Ubah enum status pada sales_orders untuk menyertakan 'VOID'
        DB::statement("ALTER TABLE `sales_orders` MODIFY COLUMN `status` ENUM('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'DELIVERED', 'COMPLETED', 'VOID') NOT NULL DEFAULT 'APPROVED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `sales_orders` MODIFY COLUMN `status` ENUM('DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'DELIVERED', 'COMPLETED') NOT NULL DEFAULT 'APPROVED'");
    }
};
