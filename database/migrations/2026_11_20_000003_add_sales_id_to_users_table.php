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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'sales_id')) {
                $table->foreignId('sales_id')->nullable()->after('tap_id')->constrained('sales')->nullOnDelete()->comment('Pemetaan user ke Tenaga Sales');
            }
        });

        // Sinkronisasi dua arah jika sudah ada data sales yang memiliki user_id
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'user_id')) {
            $salesWithUser = DB::table('sales')->whereNotNull('user_id')->get();
            foreach ($salesWithUser as $s) {
                DB::table('users')->where('id', $s->user_id)->whereNull('sales_id')->update([
                    'sales_id' => $s->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sales_id')) {
                $table->dropForeign(['sales_id']);
                $table->dropColumn('sales_id');
            }
        });
    }
};
