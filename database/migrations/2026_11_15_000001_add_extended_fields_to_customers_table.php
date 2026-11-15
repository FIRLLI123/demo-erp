<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'sales_id')) {
                $table->foreignId('sales_id')->nullable()->after('tap_id')->constrained('sales')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'customer_pic')) {
                $table->string('customer_pic')->nullable()->after('name')->comment('Nama PIC / Pemilik Toko');
            }
            if (!Schema::hasColumn('customers', 'customer_ktp')) {
                $table->string('customer_ktp', 30)->nullable()->after('customer_pic')->comment('Nomor KTP Pemilik Outlet');
            }
            if (!Schema::hasColumn('customers', 'hari_pjp')) {
                $table->string('hari_pjp', 20)->nullable()->after('customer_ktp')->comment('Hari Kunjungan PJP (SENIN, SELASA, dst)');
            }
            if (!Schema::hasColumn('customers', 'kelurahan_name')) {
                $table->string('kelurahan_name')->nullable()->after('address')->comment('Kelurahan / Desa');
            }
            if (!Schema::hasColumn('customers', 'kecamatan_name')) {
                $table->string('kecamatan_name')->nullable()->after('kelurahan_name')->comment('Kecamatan');
            }
            if (!Schema::hasColumn('customers', 'kota_name')) {
                $table->string('kota_name')->nullable()->after('kecamatan_name')->comment('Kota / Kabupaten');
            }
            if (!Schema::hasColumn('customers', 'provinsi_name')) {
                $table->string('provinsi_name')->nullable()->after('kota_name')->comment('Provinsi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'sales_id')) {
                $table->dropForeign(['sales_id']);
                $table->dropColumn('sales_id');
            }
            $cols = ['customer_pic', 'customer_ktp', 'hari_pjp', 'kelurahan_name', 'kecamatan_name', 'kota_name', 'provinsi_name'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
