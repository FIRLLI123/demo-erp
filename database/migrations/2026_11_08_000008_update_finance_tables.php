<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('doc_type', 20)->default('TTK')->change();
        });

        Schema::table('bank_receipts', function (Blueprint $table) {
            $table->enum('receipt_type', ['TUNAI', 'NON_TUNAI'])->default('NON_TUNAI')->after('doc_number');
        });
    }

    public function down(): void
    {
        Schema::table('bank_receipts', function (Blueprint $table) {
            $table->dropColumn('receipt_type');
        });
    }
};

