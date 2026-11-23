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
            if (!Schema::hasColumn('customers', 'npwp')) {
                $table->string('npwp', 30)->nullable()->after('customer_ktp')->comment('Nomor Pokok Wajib Pajak (NPWP)');
            }
            if (!Schema::hasColumn('customers', 'alamat_npwp')) {
                $table->text('alamat_npwp')->nullable()->after('npwp')->comment('Alamat yang tertera pada NPWP');
            }
            if (!Schema::hasColumn('customers', 'is_digipos')) {
                $table->boolean('is_digipos')->default(true)->after('type')->comment('Status apakah outlet menggunakan DigiPOS Telkomsel');
            }
            if (!Schema::hasColumn('customers', 'is_consignment')) {
                $table->boolean('is_consignment')->default(false)->after('is_digipos')->comment('Status apakah outlet bertipe konsinyasi (titip jual)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $cols = ['npwp', 'alamat_npwp', 'is_digipos', 'is_consignment'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
