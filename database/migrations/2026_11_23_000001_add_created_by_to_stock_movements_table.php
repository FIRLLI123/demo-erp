<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movements') && !Schema::hasColumn('stock_movements', 'created_by')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            });

            // Isi data existing dengan user pertama
            $firstUser = DB::table('users')->first();
            if ($firstUser) {
                DB::table('stock_movements')->whereNull('created_by')->update(['created_by' => $firstUser->id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_movements') && Schema::hasColumn('stock_movements', 'created_by')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }
    }
};
