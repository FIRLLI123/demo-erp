<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('tap_id')->constrained('vendors')->nullOnDelete();
            }
        });

        // Pastikan minimal ada vendor aktif di tabel vendors
        $defaultVendor = DB::table('vendors')->orderBy('id')->first();
        if (!$defaultVendor) {
            $vendorId = DB::table('vendors')->insertGetId([
                'code' => 'VND-TSEL-01',
                'name' => 'PT TELKOMSEL DISTRIBUSI PUSAT',
                'category' => 'PRINCIPAL_TELCO',
                'contact_person' => 'Account Manager Telkomsel',
                'phone' => '021-52991100',
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $vendorId = $defaultVendor->id;
        }

        // Backfill PO lama yang belum memiliki vendor_id
        DB::table('purchase_orders')
            ->whereNull('vendor_id')
            ->update(['vendor_id' => $vendorId]);
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
                $table->dropColumn('vendor_id');
            }
        });
    }
};
