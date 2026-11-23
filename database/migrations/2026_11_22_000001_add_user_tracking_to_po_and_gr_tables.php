<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders') && !Schema::hasColumn('purchase_orders', 'created_by')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            });

            // Isi data existing dengan user ID pertama yang ada
            $firstUser = DB::table('users')->first();
            if ($firstUser) {
                DB::table('purchase_orders')->whereNull('created_by')->update(['created_by' => $firstUser->id]);
            }
        }

        if (Schema::hasTable('goods_receipts') && !Schema::hasColumn('goods_receipts', 'received_by')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->foreignId('received_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            });

            // Isi data existing dengan user ID pertama yang ada
            $firstUser = DB::table('users')->first();
            if ($firstUser) {
                DB::table('goods_receipts')->whereNull('received_by')->update(['received_by' => $firstUser->id]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_orders') && Schema::hasColumn('purchase_orders', 'created_by')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('goods_receipts') && Schema::hasColumn('goods_receipts', 'received_by')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->dropForeign(['received_by']);
                $table->dropColumn('received_by');
            });
        }
    }
};
